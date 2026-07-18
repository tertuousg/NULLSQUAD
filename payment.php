<?php
require_once __DIR__ . '/includes/init.php';

require_customer();

$customerId = current_customer_id();
$items = get_cart_items($customerId);
$shippingAddress = (string) ($_SESSION['checkout_shipping_address'] ?? '');

if (empty($items)) {
    set_flash('warning', 'Your cart is empty.');
    redirect('store.php');
}

if ($shippingAddress === '') {
    set_flash('warning', 'Please confirm your shipping address.');
    redirect('checkout.php');
}

$total = cart_total($items);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('danger', 'Invalid form token. Please try again.');
        redirect('payment.php');
    }

    $method = (string) ($_POST['payment_method'] ?? '');
    $allowedMethods = ['Cash on Delivery', 'Bank Transfer', 'GCash'];

    if (!in_array($method, $allowedMethods, true)) {
        set_flash('danger', 'Please select a valid payment method.');
        redirect('payment.php');
    }

    $reference = generate_order_reference();

    db()->beginTransaction();
    try {
        foreach ($items as $item) {
            $stockStmt = db()->prepare('SELECT quantity FROM inventory WHERE product_id = :product_id');
            $stockStmt->execute(['product_id' => $item['product_id']]);
            $stock = (int) $stockStmt->fetchColumn();

            if ($stock < (int) $item['quantity']) {
                throw new RuntimeException($item['name'] . ' does not have enough stock.');
            }
        }

        $orderStmt = db()->prepare(
            'INSERT INTO orders (customer_id, reference_number, shipping_address, total_amount, payment_method, order_status, payment_status, created_at)
             VALUES (:customer_id, :reference_number, :shipping_address, :total_amount, :payment_method, "pending", "unpaid", CURRENT_TIMESTAMP)'
        );
        $orderStmt->execute([
            'customer_id' => $customerId,
            'reference_number' => $reference,
            'shipping_address' => $shippingAddress,
            'total_amount' => $total,
            'payment_method' => $method,
        ]);

        $orderId = (int) db()->lastInsertId();
        $itemStmt = db()->prepare(
            'INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal)
             VALUES (:order_id, :product_id, :product_name, :price, :quantity, :subtotal)'
        );
        $inventoryStmt = db()->prepare('UPDATE inventory SET quantity = quantity - :quantity WHERE product_id = :product_id');

        foreach ($items as $item) {
            $subtotal = (float) $item['price'] * (int) $item['quantity'];
            $itemStmt->execute([
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'product_name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'subtotal' => $subtotal,
            ]);
            $inventoryStmt->execute([
                'quantity' => $item['quantity'],
                'product_id' => $item['product_id'],
            ]);
        }

        $paymentStmt = db()->prepare(
            'INSERT INTO payments (order_id, method, amount, status, proof_reference, created_at)
             VALUES (:order_id, :method, :amount, "pending", :proof_reference, CURRENT_TIMESTAMP)'
        );
        $paymentStmt->execute([
            'order_id' => $orderId,
            'method' => $method,
            'amount' => $total,
            'proof_reference' => $method === 'Cash on Delivery' ? 'Pay upon delivery' : 'Simulated payment only',
        ]);

        clear_cart($customerId);
        unset($_SESSION['checkout_shipping_address']);

        db()->commit();
        log_activity((int) current_user()['id'], current_user()['email'], 'Created order ' . $reference);
        set_flash('success', 'Order placed successfully. Reference Number: ' . $reference);
        redirect('order_details.php?id=' . $orderId);
    } catch (Throwable $exception) {
        db()->rollBack();
        set_flash('danger', $exception->getMessage());
        redirect('cart.php');
    }
}

$pageTitle = 'Payment';
include __DIR__ . '/templates/header.php';
?>
<section class="section-band py-4">
    <div class="container">
        <h1 class="h2 fw-bold mb-1">Payment</h1>
        <p class="text-muted mb-0">Choose a simulated payment method and generate an order reference number.</p>
    </div>
</section>
<section class="py-4">
    <div class="container">
        <?php display_flash(); ?>
        <div class="row g-4">
            <div class="col-lg-7">
                <form method="post" class="card">
                    <div class="card-body">
                        <?= csrf_field() ?>
                        <h2 class="h5 mb-3">Payment Method</h2>
                        <?php foreach (['Cash on Delivery', 'Bank Transfer', 'GCash'] as $method): ?>
                            <div class="form-check border rounded p-3 ps-5 mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="<?= e(str_replace(' ', '_', $method)) ?>" value="<?= e($method) ?>" required>
                                <label class="form-check-label fw-semibold" for="<?= e(str_replace(' ', '_', $method)) ?>"><?= e($method) ?></label>
                                <div class="small text-muted">Simulation only. No real payment gateway is connected.</div>
                            </div>
                        <?php endforeach; ?>
                        <button class="btn btn-primary" type="submit">Place Order</button>
                    </div>
                </form>
            </div>
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Order Summary</h2>
                        <p class="small text-muted mb-3"><?= nl2br(e($shippingAddress)) ?></p>
                        <?php foreach ($items as $item): ?>
                            <div class="d-flex justify-content-between gap-3 border-bottom py-2">
                                <span><?= e($item['name']) ?> x <?= (int) $item['quantity'] ?></span>
                                <strong><?= e(money((float) $item['price'] * (int) $item['quantity'])) ?></strong>
                            </div>
                        <?php endforeach; ?>
                        <div class="d-flex justify-content-between mt-3 h5">
                            <span>Total</span>
                            <strong><?= e(money($total)) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>

