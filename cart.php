<?php
require_once __DIR__ . '/includes/init.php';

require_customer();

$customerId = current_customer_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('danger', 'Invalid form token. Please try again.');
        redirect('cart.php');
    }

    $action = (string) ($_POST['action'] ?? '');

    try {
        if ($action === 'add') {
            add_to_cart($customerId, (int) ($_POST['product_id'] ?? 0), (int) ($_POST['quantity'] ?? 1));
            set_flash('success', 'Product added to cart.');
        } elseif ($action === 'update') {
            update_cart_quantity((int) ($_POST['cart_id'] ?? 0), $customerId, (int) ($_POST['quantity'] ?? 1));
            set_flash('success', 'Cart updated.');
        } elseif ($action === 'remove') {
            remove_cart_item((int) ($_POST['cart_id'] ?? 0), $customerId);
            set_flash('success', 'Item removed from cart.');
        }
    } catch (Throwable $exception) {
        set_flash('danger', $exception->getMessage());
    }

    redirect('cart.php');
}

$items = get_cart_items($customerId);
$total = cart_total($items);
$pageTitle = 'Shopping Cart';

include __DIR__ . '/templates/header.php';
?>
<section class="section-band py-4">
    <div class="container">
        <h1 class="h2 fw-bold mb-1">Shopping Cart</h1>
        <p class="text-muted mb-0">Review selected products and update quantities before checkout.</p>
    </div>
</section>
<section class="py-4">
    <div class="container">
        <?php display_flash(); ?>
        <?php if (empty($items)): ?>
            <div class="alert alert-info">Your cart is empty. <a href="<?= url('store.php') ?>">Continue shopping</a>.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= e(upload_url((string) $item['image'])) ?>" alt="<?= e($item['name']) ?>" width="72" height="54" class="rounded border">
                                    <div>
                                        <div class="fw-semibold"><?= e($item['name']) ?></div>
                                        <div class="small text-muted"><?= (int) $item['stock'] ?> available</div>
                                    </div>
                                </div>
                            </td>
                            <td><?= e(money($item['price'])) ?></td>
                            <td>
                                <form method="post" class="d-flex gap-2">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="cart_id" value="<?= (int) $item['cart_id'] ?>">
                                    <input type="number" name="quantity" class="form-control form-control-sm" value="<?= (int) $item['quantity'] ?>" min="1" max="<?= (int) $item['stock'] ?>">
                                    <button class="btn btn-sm btn-outline-primary" type="submit">Update</button>
                                </form>
                            </td>
                            <td><?= e(money((float) $item['price'] * (int) $item['quantity'])) ?></td>
                            <td>
                                <form method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="cart_id" value="<?= (int) $item['cart_id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Remove this item?">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <a class="btn btn-outline-primary" href="<?= url('store.php') ?>">Continue Shopping</a>
                <div class="text-end">
                    <div class="h4">Total: <?= e(money($total)) ?></div>
                    <a class="btn btn-primary" href="<?= url('checkout.php') ?>">Proceed to Checkout</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>

