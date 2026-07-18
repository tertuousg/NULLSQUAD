<?php
$pageTitle = $pageTitle ?? APP_NAME;
$bodyClass = $bodyClass ?? '';
$customerIdForNav = is_customer() ? current_customer_id() : 0;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <link rel="icon" href="<?= asset('images/logo.svg') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= asset('css/styles.css') ?>" rel="stylesheet">
</head>
<body class="<?= e($bodyClass) ?>">
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= url('index.php') ?>">
            <img src="<?= asset('images/logo.svg') ?>" alt="<?= e(APP_NAME) ?> logo" width="42" height="42">
            <span class="fw-bold"><?= e(APP_SHORT_NAME) ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link <?= active_link('index.php') ?>" href="<?= url('index.php') ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link <?= active_link('store.php') ?>" href="<?= url('store.php') ?>">Store</a></li>
                <li class="nav-item"><a class="nav-link <?= active_link('about.php') ?>" href="<?= url('about.php') ?>">About</a></li>
                <?php if (is_customer()): ?>
                    <li class="nav-item"><a class="nav-link <?= active_link('order_history.php') ?>" href="<?= url('order_history.php') ?>">Orders</a></li>
                    <li class="nav-item">
                        <a class="btn btn-outline-primary position-relative ms-lg-2" href="<?= url('cart.php') ?>">
                            Cart
                            <?php if ($customerIdForNav > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= cart_item_count($customerIdForNav) ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item"><a class="btn btn-primary ms-lg-2" href="<?= url('logout.php') ?>">Logout</a></li>
                <?php elseif (is_admin()): ?>
                    <li class="nav-item"><a class="btn btn-primary ms-lg-2" href="<?= url('admin/dashboard.php') ?>">Admin</a></li>
                <?php else: ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= active_link('login.php') ?>" href="#" id="loginMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Login
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="loginMenu">
                            <li>
                                <a class="dropdown-item py-2" href="<?= url('login.php') ?>">
                                    <span class="fw-semibold d-block">Customer Login</span>
                                    <small class="text-muted">Shop, manage your cart, and view orders</small>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2" href="<?= url('admin/login.php') ?>">
                                    <span class="fw-semibold d-block">Admin Login</span>
                                    <small class="text-muted">Manage products, inventory, and orders</small>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="btn btn-primary ms-lg-2" href="<?= url('register.php') ?>">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main>
