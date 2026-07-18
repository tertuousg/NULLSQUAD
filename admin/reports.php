<?php
require_once __DIR__ . '/../includes/init.php';

require_admin();

$pageTitle = 'Reports';

$inventoryReport = db()->query(
    'SELECT p.name AS product, c.name AS category, p.price, i.quantity AS current_stocks
     FROM inventory i
     JOIN products p ON p.id = i.product_id
     JOIN categories c ON c.id = p.category_id
     ORDER BY c.name, p.name'
)->fetchAll();

$auditLogs = db()->query(
    'SELECT *
     FROM audit_logs
     ORDER BY created_at DESC
     LIMIT 100'
)->fetchAll();

include __DIR__ . '/../templates/admin_header.php';
?>
<div class="card mb-4">
    <div class="card-body">
        <h2 class="h5 mb-3">Inventory Report</h2>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Product</th><th>Current Stocks</th><th>Price</th><th>Category</th></tr></thead>
                <tbody>
                <?php foreach ($inventoryReport as $row): ?>
                    <tr>
                        <td><?= e($row['product']) ?></td>
                        <td><?= (int) $row['current_stocks'] ?></td>
                        <td><?= e(money($row['price'])) ?></td>
                        <td><?= e($row['category']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h2 class="h5 mb-3">Audit Log Report</h2>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Username</th><th>Date</th><th>Time</th><th>IP Address</th><th>Action Performed</th></tr></thead>
                <tbody>
                <?php foreach ($auditLogs as $log): ?>
                    <tr>
                        <td><?= e($log['username']) ?></td>
                        <td><?= e(date('M d, Y', strtotime($log['created_at']))) ?></td>
                        <td><?= e(date('h:i A', strtotime($log['created_at']))) ?></td>
                        <td><?= e($log['ip_address'] ?? '') ?></td>
                        <td><?= e($log['action']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($auditLogs)): ?><tr><td colspan="5" class="text-muted">No audit logs yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../templates/admin_footer.php'; ?>

