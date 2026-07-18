<?php
require_once __DIR__ . '/includes/init.php';

require_customer();

$customerId = current_customer_id();
$items = get_cart_items($customerId);

if (empty($items)) {
    set_flash('warning', 'Your cart is empty.');
    redirect('store.php');
}

$customerStmt = db()->prepare(
    'SELECT u.full_name, u.email, c.address, c.contact_number
     FROM customers c
     JOIN users u ON u.id = c.user_id
     WHERE c.id = :customer_id'
);
$customerStmt->execute(['customer_id' => $customerId]);
$customer = $customerStmt->fetch();
$total = cart_total($items);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('danger', 'Invalid form token. Please try again.');
        redirect('checkout.php');
    }

    $shippingAddress = trim((string) ($_POST['shipping_address'] ?? ''));
    if ($shippingAddress === '') {
        set_flash('danger', 'Shipping address is required.');
    } else {
        $_SESSION['checkout_shipping_address'] = $shippingAddress;
        redirect('payment.php');
    }
}

$pageTitle = 'Checkout';
include __DIR__ . '/templates/header.php';
?>
<section class="section-band py-4">
    <div class="container">
        <h1 class="h2 fw-bold mb-1">Checkout</h1>
        <p class="text-muted mb-0">Confirm customer information and shipping address.</p>
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
                        <h2 class="h5 mb-3">Customer Information</h2>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Complete Name</label>
                                <input class="form-control" value="<?= e($customer['full_name']) ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input class="form-control" value="<?= e($customer['email']) ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Number</label>
                                <input class="form-control" value="<?= e($customer['contact_number']) ?>" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="shipping_address">Shipping Address</label>
                            <textarea class="form-control" id="shipping_address" name="shipping_address" rows="4" required><?= e($_SESSION['checkout_shipping_address'] ?? $customer['address']) ?></textarea>
                        </div>
                        <button class="btn btn-primary" type="submit">Continue to Payment</button>
                    </div>
                </form>
            </div>
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Order Summary</h2>
                        <?php foreach ($items as $item): ?>
                            <div class="d-flex justify-content-between gap-3 border-bottom py-2">
                                <span><?= e($item['name']) ?> x <?= (int) $item['quantity'] ?></span>
                                <strong><?= e(money((float) $item['price'] * (int) $item['quantity'])) ?></strong>
                            </div>
                        <?php endforeach; ?>
                        <div class="d-flex justify-content-between mt-3 h5">
                            <span>Total Payment</span>
                            <strong><?= e(money($total)) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>

