<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

require_customer();

$customerId = current_customer_id();
$orderId = (int) ($_GET['id'] ?? 0);

$orderStmt = db()->prepare(
    'SELECT * FROM orders
     WHERE id = :id AND customer_id = :customer_id
     LIMIT 1'
);
$orderStmt->execute([
    'id' => $orderId,
    'customer_id' => $customerId,
]);
$order = $orderStmt->fetch();

if (!$order) {
    set_flash('warning', 'Order not found.');
    redirect('order_history.php');
}

/* Cancel a pending and unpaid order. */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('danger', 'Invalid form token.');
        redirect('order_details.php?id=' . $orderId);
    }

    $pdo = db();

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'SELECT reference_number, order_status, payment_status
             FROM orders
             WHERE id = :id AND customer_id = :customer_id'
        );
        $stmt->execute([
            'id' => $orderId,
            'customer_id' => $customerId,
        ]);
        $currentOrder = $stmt->fetch();

        if (
            !$currentOrder ||
            $currentOrder['order_status'] !== 'pending' ||
            $currentOrder['payment_status'] !== 'unpaid'
        ) {
            throw new RuntimeException(
                'Only pending and unpaid orders can be cancelled.'
            );
        }

        /* Return all ordered quantities to inventory. */
        $itemsToReturn = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = :order_id');
        $itemsToReturn->execute(['order_id' => $orderId]);
        foreach ($itemsToReturn->fetchAll() as $itemRow) {
            $restore = $pdo->prepare('UPDATE inventory SET quantity = quantity + :quantity WHERE product_id = :product_id');
            $restore->execute([
                'quantity' => (int) $itemRow['quantity'],
                'product_id' => (int) $itemRow['product_id'],
            ]);
        }

        /* Mark the order and payment as cancelled. */
        $stmt = $pdo->prepare(
            'UPDATE orders
             SET order_status = "cancelled"
             WHERE id = :id'
        );
        $stmt->execute(['id' => $orderId]);

        $stmt = $pdo->prepare(
            'UPDATE payments
             SET status = "failed"
             WHERE order_id = :order_id AND status = "pending"'
        );
        $stmt->execute(['order_id' => $orderId]);

        log_activity(
            (int) current_user()['id'],
            current_user()['email'],
            'Cancelled order ' . $currentOrder['reference_number']
        );

        $pdo->commit();
        set_flash('success', 'Order cancelled successfully.');
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        set_flash('danger', $exception->getMessage());
    }

    redirect('order_details.php?id=' . $orderId);
}

$itemsStmt = db()->prepare(
    'SELECT * FROM order_items WHERE order_id = :order_id'
);
$itemsStmt->execute(['order_id' => $orderId]);
$items = $itemsStmt->fetchAll();

$paymentStmt = db()->prepare(
    'SELECT * FROM payments WHERE order_id = :order_id LIMIT 1'
);
$paymentStmt->execute(['order_id' => $orderId]);
$payment = $paymentStmt->fetch();

$pageTitle = 'Order Details';
include __DIR__ . '/templates/header.php';
?>

<section class="section-band py-4">
    <div class="container">
        <h1 class="h2 fw-bold mb-1">Order Details</h1>
        <p class="text-muted mb-0">
            Reference Number: <?= e($order['reference_number']) ?>
        </p>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <?php display_flash(); ?>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Items</h2>

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Qty</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td><?= e($item['product_name']) ?></td>
                                            <td><?= e(money($item['price'])) ?></td>
                                            <td><?= (int) $item['quantity'] ?></td>
                                            <td><?= e(money($item['subtotal'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Summary</h2>

                        <p>
                            <strong>Status:</strong>
                            <span class="badge text-bg-<?= badge_class($order['order_status']) ?>">
                                <?= e(ucfirst($order['order_status'])) ?>
                            </span>
                        </p>

                        <p>
                            <strong>Payment:</strong>
                            <?= e($payment['method'] ?? $order['payment_method']) ?>
                        </p>

                        <p>
                            <strong>Payment Status:</strong>
                            <?= e(ucfirst($order['payment_status'])) ?>
                        </p>

                        <p>
                            <strong>Shipping Address:</strong><br>
                            <?= nl2br(e($order['shipping_address'])) ?>
                        </p>

                        <p class="h5">
                            <strong>Total:</strong>
                            <?= e(money($order['total_amount'])) ?>
                        </p>

                        <?php if (
                            $order['order_status'] === 'pending' &&
                            $order['payment_status'] === 'unpaid'
                        ): ?>
                            <form method="post" class="mb-2">
                                <?= csrf_field() ?>

                                <button
                                    type="submit"
                                    class="btn btn-outline-danger w-100"
                                    data-confirm="Cancel this order?"
                                >
                                    Cancel Order
                                </button>
                            </form>
                        <?php endif; ?>

                        <a
                            class="btn btn-outline-primary w-100"
                            href="<?= url('order_history.php') ?>"
                        >
                            Back to Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/templates/footer.php'; ?>