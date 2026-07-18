<?php
/** @var array $product */
$stock = (int) ($product['stock'] ?? $product['quantity'] ?? 0);
?>
<div class="card product-card">
    <img src="<?= e(upload_url((string) ($product['image'] ?? ''))) ?>" class="product-image" alt="<?= e($product['name']) ?>">
    <div class="card-body d-flex flex-column">
        <div class="d-flex justify-content-between gap-2 align-items-start mb-2">
            <h3 class="h6 mb-0"><?= e($product['name']) ?></h3>
            <span class="fw-bold text-accent"><?= e(money($product['price'])) ?></span>
        </div>
        <p class="text-muted small flex-grow-1"><?= e(excerpt((string) $product['description'])) ?></p>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="badge text-bg-<?= $stock > 0 ? 'success' : 'danger' ?>"><?= $stock > 0 ? $stock . ' in stock' : 'Out of stock' ?></span>
            <a href="<?= url('product.php?id=' . (int) $product['id']) ?>" class="small">View details</a>
        </div>
        <?php if ($stock > 0): ?>
            <?php if (is_customer()): ?>
                <form method="post" action="<?= url('cart.php') ?>" class="d-flex gap-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <input type="number" name="quantity" class="form-control form-control-sm" value="1" min="1" max="<?= $stock ?>" aria-label="Quantity">
                    <button class="btn btn-sm btn-primary" type="submit">Add to Cart</button>
                </form>
            <?php else: ?>
                <a class="btn btn-sm btn-primary" href="<?= url('login.php') ?>">Login to Buy</a>
            <?php endif; ?>
        <?php else: ?>
            <button class="btn btn-sm btn-secondary" disabled>Unavailable</button>
        <?php endif; ?>
    </div>
</div>
