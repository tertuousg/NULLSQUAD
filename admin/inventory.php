<?php
require_once __DIR__ . '/../includes/init.php';

require_admin();

$pageTitle = 'Inventory Management';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('danger', 'Invalid form token. Please try again.');
        redirect('admin/inventory.php');
    }

    $stmt = db()->prepare('UPDATE inventory SET quantity = :quantity, low_stock_threshold = :threshold WHERE product_id = :product_id');
    $stmt->execute([
        'quantity' => max(0, (int) ($_POST['quantity'] ?? 0)),
        'threshold' => max(0, (int) ($_POST['low_stock_threshold'] ?? LOW_STOCK_DEFAULT)),
        'product_id' => (int) ($_POST['product_id'] ?? 0),
    ]);

    log_activity((int) current_user()['id'], current_user()['email'], 'Update stocks for product ID ' . (int) ($_POST['product_id'] ?? 0));
    set_flash('success', 'Inventory updated.');
    redirect('admin/inventory.php');
}

$items = db()->query(
    'SELECT p.id, p.name, p.price, p.status, c.name AS category_name, i.quantity, i.low_stock_threshold
     FROM inventory i
     JOIN products p ON p.id = i.product_id
     JOIN categories c ON c.id = p.category_id
     ORDER BY i.quantity ASC, p.name'
)->fetchAll();

include __DIR__ . '/../templates/admin_header.php';
?>
<div class="card">
    <div class="card-body">
        <h2 class="h5 mb-3">Stock Levels</h2>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Remaining Stocks</th><th>Alert</th><th>Update</th></tr></thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <?php
                    $quantity = (int) $item['quantity'];
                    $threshold = (int) $item['low_stock_threshold'];
                    $alert = $quantity === 0 ? 'Out of Stock' : ($quantity <= $threshold ? 'Low Stock' : 'Normal');
                    $alertClass = $quantity === 0 ? 'danger' : ($quantity <= $threshold ? 'warning' : 'success');
                    ?>
                    <tr>
                        <td><?= e($item['name']) ?></td>
                        <td><?= e($item['category_name']) ?></td>
                        <td><?= e(money($item['price'])) ?></td>
                        <td><?= $quantity ?></td>
                        <td><span class="badge text-bg-<?= $alertClass ?>"><?= e($alert) ?></span></td>
                        <td>
                            <form method="post" class="row g-2 align-items-center">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>">
                                <div class="col-5"><input type="number" min="0" class="form-control form-control-sm" name="quantity" value="<?= $quantity ?>" aria-label="Quantity"></div>
                                <div class="col-4"><input type="number" min="0" class="form-control form-control-sm" name="low_stock_threshold" value="<?= $threshold ?>" aria-label="Low stock threshold"></div>
                                <div class="col-3"><button class="btn btn-sm btn-outline-primary" type="submit">Save</button></div>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../templates/admin_footer.php'; ?>

