<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Store';
$search = trim((string) ($_GET['search'] ?? ''));
$categoryId = (int) ($_GET['category'] ?? 0);
$minPrice = trim((string) ($_GET['min_price'] ?? ''));
$maxPrice = trim((string) ($_GET['max_price'] ?? ''));

$categories = db()->query('SELECT * FROM categories WHERE status = "active" ORDER BY name')->fetchAll();

$where = ['p.status = "active"', 'c.status = "active"'];
$params = [];

if ($search !== '') {
    $where[] = '(p.name LIKE :search OR p.description LIKE :search)';
    $params['search'] = '%' . $search . '%';
}

if ($categoryId > 0) {
    $where[] = 'p.category_id = :category_id';
    $params['category_id'] = $categoryId;
}

if ($minPrice !== '' && is_numeric($minPrice)) {
    $where[] = 'p.price >= :min_price';
    $params['min_price'] = $minPrice;
}

if ($maxPrice !== '' && is_numeric($maxPrice)) {
    $where[] = 'p.price <= :max_price';
    $params['max_price'] = $maxPrice;
}

$sql = 'SELECT p.*, c.name AS category_name, i.quantity AS stock
        FROM products p
        JOIN categories c ON c.id = p.category_id
        JOIN inventory i ON i.product_id = p.id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY p.created_at DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

include __DIR__ . '/templates/header.php';
?>
<section class="section-band py-4">
    <div class="container">
        <h1 class="h2 fw-bold mb-1">Store</h1>
        <p class="text-muted mb-0">Search office equipment by category, stock, and price range.</p>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <?php display_flash(); ?>
        <form class="store-toolbar mb-4" method="get">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label" for="search">Search</label>
                    <input class="form-control" id="search" name="search" value="<?= e($search) ?>" placeholder="Chair, desk, cabinet">
                </div>
                <div class="col-lg-3">
                    <label class="form-label" for="category">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="0">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>" <?= selected($categoryId, $category['id']) ?>><?= e($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label" for="min_price">Min Price</label>
                    <input type="number" min="0" step="0.01" class="form-control" id="min_price" name="min_price" value="<?= e($minPrice) ?>">
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label" for="max_price">Max Price</label>
                    <input type="number" min="0" step="0.01" class="form-control" id="max_price" name="max_price" value="<?= e($maxPrice) ?>">
                </div>
                <div class="col-lg-1 d-grid">
                    <button class="btn btn-primary" type="submit">Filter</button>
                </div>
            </div>
        </form>

        <div class="row g-4">
            <?php foreach ($products as $product): ?>
                <div class="col-sm-6 col-lg-4">
                    <?php include __DIR__ . '/templates/product_card.php'; ?>
                </div>
            <?php endforeach; ?>
            <?php if (empty($products)): ?>
                <div class="col-12">
                    <div class="alert alert-info">No products matched your filter.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>

