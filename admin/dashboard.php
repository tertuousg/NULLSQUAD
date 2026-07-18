<?php
require_once __DIR__ . '/../includes/init.php';

require_admin();

$pageTitle = 'Dashboard';

$stats = [
    'products' => (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'customers' => (int) db()->query('SELECT COUNT(*) FROM customers')->fetchColumn(),
    'orders' => (int) db()->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'low_stock' => (int) db()->query('SELECT COUNT(*) FROM inventory WHERE quantity <= low_stock_threshold')->fetchColumn(),
];

$recentOrders = db()->query(
    'SELECT o.*, u.full_name
     FROM orders o
     JOIN customers c ON c.id = o.customer_id
     JOIN users u ON u.id = c.user_id
     ORDER BY o.created_at DESC
     LIMIT 5'
)->fetchAll();

$lowStockItems = db()->query(
    'SELECT p.name, i.quantity, i.low_stock_threshold
     FROM inventory i
     JOIN products p ON p.id = i.product_id
     WHERE i.quantity <= i.low_stock_threshold
     ORDER BY i.quantity ASC
     LIMIT 5'
)->fetchAll();

include __DIR__ . '/../templates/admin_header.php';
?>
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card dashboard-stat"><div class="card-body"><div class="text-muted">Products</div><div class="display-6 fw-bold"><?= $stats['products'] ?></div></div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card dashboard-stat"><div class="card-body"><div class="text-muted">Customers</div><div class="display-6 fw-bold"><?= $stats['customers'] ?></div></div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card dashboard-stat"><div class="card-body"><div class="text-muted">Orders</div><div class="display-6 fw-bold"><?= $stats['orders'] ?></div></div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card dashboard-stat"><div class="card-body"><div class="text-muted">Low Stock</div><div class="display-6 fw-bold"><?= $stats['low_stock'] ?></div></div></div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3">Recent Orders</h2>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Reference</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><?= e($order['reference_number']) ?></td>
                                <td><?= e($order['full_name']) ?></td>
                                <td><?= e(money($order['total_amount'])) ?></td>
                                <td><span class="badge text-bg-<?= badge_class($order['order_status']) ?>"><?= e(ucfirst($order['order_status'])) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentOrders)): ?><tr><td colspan="4" class="text-muted">No orders yet.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3">Low Stock Alerts</h2>
                <?php foreach ($lowStockItems as $item): ?>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span><?= e($item['name']) ?></span>
                        <strong><?= (int) $item['quantity'] ?> left</strong>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($lowStockItems)): ?><p class="text-muted mb-0">No low stock items.</p><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../templates/admin_footer.php'; ?>

