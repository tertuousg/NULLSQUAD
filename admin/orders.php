<?php
require_once __DIR__ . '/../includes/init.php';

require_admin();

$pageTitle = 'Order Management';
$allowedStatuses = ['pending', 'processing', 'approved', 'delivered', 'cancelled'];
$allowedPaymentStatuses = ['unpaid', 'paid'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('danger', 'Invalid form token. Please try again.');
        redirect('admin/orders.php');
    }

    $orderId = (int) ($_POST['order_id'] ?? 0);
    $orderStatus = (string) ($_POST['order_status'] ?? 'pending');
    $paymentStatus = (string) ($_POST['payment_status'] ?? 'unpaid');

    if (in_array($orderStatus, $allowedStatuses, true) && in_array($paymentStatus, $allowedPaymentStatuses, true)) {
        $stmt = db()->prepare('UPDATE orders SET order_status = :order_status, payment_status = :payment_status WHERE id = :id');
        $stmt->execute([
            'order_status' => $orderStatus,
            'payment_status' => $paymentStatus,
            'id' => $orderId,
        ]);

        if ($paymentStatus === 'paid') {
            $paymentStmt = db()->prepare('UPDATE payments SET status = "paid" WHERE order_id = :order_id');
            $paymentStmt->execute(['order_id' => $orderId]);
        }

        $action = $orderStatus === 'approved' ? 'Checkout approval for order ID ' . $orderId : 'Update order ID ' . $orderId;
        log_activity((int) current_user()['id'], current_user()['email'], $action);
        set_flash('success', 'Order updated.');
    }

    redirect('admin/orders.php');
}

$orders = db()->query(
    'SELECT o.*, u.full_name, u.email
     FROM orders o
     JOIN customers c ON c.id = o.customer_id
     JOIN users u ON u.id = c.user_id
     ORDER BY o.created_at DESC'
)->fetchAll();

include __DIR__ . '/../templates/admin_header.php';
?>
<div class="card">
    <div class="card-body">
        <h2 class="h5 mb-3">Orders</h2>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Reference</th><th>Customer</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th><th>Update</th></tr></thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= e($order['reference_number']) ?></div>
                            <div class="small text-muted"><?= e($order['email']) ?></div>
                        </td>
                        <td><?= e($order['full_name']) ?></td>
                        <td><?= e(date('M d, Y h:i A', strtotime($order['created_at']))) ?></td>
                        <td><?= e(money($order['total_amount'])) ?></td>
                        <td><?= e($order['payment_method']) ?></td>
                        <td><span class="badge text-bg-<?= badge_class($order['order_status']) ?>"><?= e(ucfirst($order['order_status'])) ?></span></td>
                        <td>
                            <form method="post" class="d-flex gap-2">
                                <?= csrf_field() ?>
                                <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                <select class="form-select form-select-sm" name="order_status" aria-label="Order status">
                                    <?php foreach ($allowedStatuses as $status): ?>
                                        <option value="<?= e($status) ?>" <?= selected($order['order_status'], $status) ?>><?= e(ucfirst($status)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select class="form-select form-select-sm" name="payment_status" aria-label="Payment status">
                                    <?php foreach ($allowedPaymentStatuses as $status): ?>
                                        <option value="<?= e($status) ?>" <?= selected($order['payment_status'], $status) ?>><?= e(ucfirst($status)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?><tr><td colspan="7" class="text-muted">No orders yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../templates/admin_footer.php'; ?>

