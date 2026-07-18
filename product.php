<?php
require_once __DIR__ . '/includes/init.php';

$productId = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare(
    'SELECT p.*, c.name AS category_name, i.quantity AS stock
     FROM products p
     JOIN categories c ON c.id = p.category_id
     JOIN inventory i ON i.product_id = p.id
     WHERE p.id = :id AND p.status = "active"
     LIMIT 1'
);
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('warning', 'Product not found.');
    redirect('store.php');
}

$pageTitle = $product['name'];
include __DIR__ . '/templates/header.php';
?>
<section class="py-5">
    <div class="container">
        <?php display_flash(); ?>
        <div class="row g-5">
            <div class="col-lg-6">
                <img class="img-fluid rounded border" src="<?= e(upload_url((string) $product['image'])) ?>" alt="<?= e($product['name']) ?>">
            </div>
            <div class="col-lg-6">
                <span class="badge text-bg-light mb-3"><?= e($product['category_name']) ?></span>
                <h1 class="h2 fw-bold"><?= e($product['name']) ?></h1>
                <p class="lead text-accent fw-bold"><?= e(money($product['price'])) ?></p>
                <p><?= nl2br(e($product['description'])) ?></p>
                <p><strong>Available Stocks:</strong> <?= (int) $product['stock'] ?></p>
                <?php if ((int) $product['stock'] > 0): ?>
                    <?php if (is_customer()): ?>
                        <form method="post" action="<?= url('cart.php') ?>" class="d-flex gap-2 align-items-end">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                            <div>
                                <label class="form-label" for="quantity">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="<?= (int) $product['stock'] ?>">
                            </div>
                            <button class="btn btn-primary" type="submit">Add to Cart</button>
                        </form>
                    <?php else: ?>
                        <a class="btn btn-primary" href="<?= url('login.php') ?>">Login to Buy</a>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="btn btn-secondary" disabled>Out of Stock</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>

