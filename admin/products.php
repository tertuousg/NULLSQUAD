<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/file_upload.php';

require_admin();

$pageTitle = 'Product Management';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('danger', 'Invalid form token. Please try again.');
        redirect('admin/products.php');
    }

    $action = (string) ($_POST['action'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    try {
        if ($action === 'create' || $action === 'update') {
            $data = [
                'name' => trim((string) ($_POST['name'] ?? '')),
                'description' => trim((string) ($_POST['description'] ?? '')),
                'category_id' => (string) ($_POST['category_id'] ?? ''),
                'price' => (string) ($_POST['price'] ?? ''),
                'quantity' => (string) ($_POST['quantity'] ?? ''),
                'status' => (string) ($_POST['status'] ?? 'active'),
            ];

            $errors = validate_product($data);
            if (!empty($errors)) {
                throw new RuntimeException('Please complete all required product fields.');
            }

            $image = handle_product_image_upload($_FILES['image'] ?? []);

            if ($action === 'create') {
                $stmt = db()->prepare(
                    'INSERT INTO products (category_id, name, description, price, image, status, created_at)
                     VALUES (:category_id, :name, :description, :price, :image, :status, CURRENT_TIMESTAMP)'
                );
                $stmt->execute([
                    'category_id' => $data['category_id'],
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'image' => $image ?: 'assets/images/product-placeholder.svg',
                    'status' => $data['status'],
                ]);

                $productId = (int) db()->lastInsertId();
                $inventory = db()->prepare('INSERT INTO inventory (product_id, quantity, low_stock_threshold) VALUES (:product_id, :quantity, :threshold)');
                $inventory->execute([
                    'product_id' => $productId,
                    'quantity' => (int) $data['quantity'],
                    'threshold' => LOW_STOCK_DEFAULT,
                ]);

                log_activity((int) current_user()['id'], current_user()['email'], 'Add product: ' . $data['name']);
                set_flash('success', 'Product added.');
            } else {
                $sql = 'UPDATE products
                        SET category_id = :category_id, name = :name, description = :description,
                            price = :price, status = :status';
                $params = [
                    'category_id' => $data['category_id'],
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'status' => $data['status'],
                    'id' => $id,
                ];

                if ($image) {
                    $sql .= ', image = :image';
                    $params['image'] = $image;
                }

                $sql .= ' WHERE id = :id';
                $stmt = db()->prepare($sql);
                $stmt->execute($params);

                $inventory = db()->prepare('UPDATE inventory SET quantity = :quantity WHERE product_id = :product_id');
                $inventory->execute([
                    'quantity' => (int) $data['quantity'],
                    'product_id' => $id,
                ]);

                log_activity((int) current_user()['id'], current_user()['email'], 'Update product ID ' . $id);
                set_flash('success', 'Product updated.');
            }
        } elseif ($action === 'delete') {
            $stmt = db()->prepare('DELETE FROM products WHERE id = :id');
            $stmt->execute(['id' => $id]);
            log_activity((int) current_user()['id'], current_user()['email'], 'Delete product ID ' . $id);
            set_flash('success', 'Product deleted.');
        }
    } catch (Throwable $exception) {
        set_flash('danger', $exception->getMessage());
    }

    redirect('admin/products.php');
}

$editProduct = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare(
        'SELECT p.*, i.quantity
         FROM products p
         JOIN inventory i ON i.product_id = p.id
         WHERE p.id = :id
         LIMIT 1'
    );
    $stmt->execute(['id' => (int) $_GET['edit']]);
    $editProduct = $stmt->fetch();
}

$categories = db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$search = trim((string) ($_GET['search'] ?? ''));
$params = [];
$where = '';

if ($search !== '') {
    $where = 'WHERE p.name LIKE :search OR c.name LIKE :search';
    $params['search'] = '%' . $search . '%';
}

$stmt = db()->prepare(
    'SELECT p.*, c.name AS category_name, i.quantity
     FROM products p
     JOIN categories c ON c.id = p.category_id
     JOIN inventory i ON i.product_id = p.id
     ' . $where . '
     ORDER BY p.created_at DESC'
);
$stmt->execute($params);
$products = $stmt->fetchAll();

include __DIR__ . '/../templates/admin_header.php';
?>
<div class="row g-4">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3"><?= $editProduct ? 'Edit Product' : 'Add Product' ?></h2>
                <form method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="<?= $editProduct ? 'update' : 'create' ?>">
                    <?php if ($editProduct): ?><input type="hidden" name="id" value="<?= (int) $editProduct['id'] ?>"><?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label" for="name">Product Name</label>
                        <input class="form-control" id="name" name="name" value="<?= e($editProduct['name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required><?= e($editProduct['description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="category_id">Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>" <?= selected($editProduct['category_id'] ?? '', $category['id']) ?>><?= e($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="price">Price</label>
                            <input type="number" min="0" step="0.01" class="form-control" id="price" name="price" value="<?= e($editProduct['price'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="quantity">Quantity</label>
                            <input type="number" min="0" class="form-control" id="quantity" name="quantity" value="<?= e($editProduct['quantity'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active" <?= selected($editProduct['status'] ?? 'active', 'active') ?>>Active</option>
                            <option value="inactive" <?= selected($editProduct['status'] ?? '', 'inactive') ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="image">Product Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" data-image-preview-input>
                    </div>
                    <img class="img-fluid rounded border mb-3" src="<?= e(upload_url((string) ($editProduct['image'] ?? ''))) ?>" alt="Product preview" data-image-preview>
                    <button class="btn btn-primary" type="submit"><?= $editProduct ? 'Save Changes' : 'Add Product' ?></button>
                    <?php if ($editProduct): ?><a class="btn btn-outline-secondary" href="<?= url('admin/products.php') ?>">Cancel</a><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h2 class="h5 mb-0">Products</h2>
                    <form method="get" class="d-flex gap-2">
                        <input class="form-control form-control-sm" name="search" value="<?= e($search) ?>" placeholder="Search products">
                        <button class="btn btn-sm btn-outline-primary" type="submit">Search</button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stocks</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= e(upload_url((string) $product['image'])) ?>" alt="<?= e($product['name']) ?>" width="56" height="42" class="rounded border">
                                        <span><?= e($product['name']) ?></span>
                                    </div>
                                </td>
                                <td><?= e($product['category_name']) ?></td>
                                <td><?= e(money($product['price'])) ?></td>
                                <td><?= (int) $product['quantity'] ?></td>
                                <td><span class="badge text-bg-<?= badge_class($product['status']) ?>"><?= e(ucfirst($product['status'])) ?></span></td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= url('admin/products.php?edit=' . (int) $product['id']) ?>">Edit</a>
                                    <form method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Delete this product?">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($products)): ?><tr><td colspan="6" class="text-muted">No products found.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../templates/admin_footer.php'; ?>

