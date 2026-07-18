<?php
require_once __DIR__ . '/includes/init.php';

require_customer();

$customerId = current_customer_id();
$stmt = db()->prepare('SELECT * FROM orders WHERE customer_id = :customer_id ORDER BY created_at DESC');
$stmt->execute(['customer_id' => $customerId]);
$orders = $stmt->fetchAll();
$pageTitle = 'Order History';

include __DIR__ . '/templates/header.php';
?>
<section class="section-band py-4">
    <div class="container">
        <h1 class="h2 fw-bold mb-1">Order History</h1>
        <p class="text-muted mb-0">View previous orders, current status, and details.</p>
    </div>
</section>
<section class="py-4">
    <div class="container">
        <?php display_flash(); ?>
        <?php if (empty($orders)): ?>
            <div class="alert alert-info">You do not have orders yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= e($order['reference_number']) ?></td>
                            <td><?= e(date('M d, Y h:i A', strtotime($order['created_at']))) ?></td>
                            <td><?= e(money($order['total_amount'])) ?></td>
                            <td><span class="badge text-bg-<?= badge_class($order['order_status']) ?>"><?= e(ucfirst($order['order_status'])) ?></span></td>
                            <td><?= e($order['payment_method']) ?></td>
                            <td><a class="btn btn-sm btn-outline-primary" href="<?= url('order_details.php?id=' . (int) $order['id']) ?>">Details</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>

