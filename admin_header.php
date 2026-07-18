<?php
$pageTitle = $pageTitle ?? 'Admin';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?> Admin</title>
    <link rel="icon" href="<?= asset('images/logo.svg') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= asset('css/styles.css') ?>" rel="stylesheet">
</head>
<body class="admin-body">
<nav class="navbar navbar-dark bg-primary sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= url('admin/dashboard.php') ?>">
            <img src="<?= asset('images/logo.svg') ?>" alt="<?= e(APP_NAME) ?> logo" width="38" height="38">
            <span><?= e(APP_SHORT_NAME) ?> Admin</span>
        </a>
        <div class="d-flex align-items-center gap-3 text-white">
            <span class="small d-none d-md-inline"><?= e(current_user()['full_name'] ?? 'Administrator') ?></span>
            <a class="btn btn-sm btn-light" href="<?= url('admin/logout.php') ?>">Logout</a>
        </div>
    </div>
</nav>
<div class="container-fluid">
    <div class="row">
        <aside class="col-lg-2 admin-sidebar p-3">
            <div class="list-group list-group-flush">
                <a class="list-group-item list-group-item-action <?= active_link('dashboard.php') ?>" href="<?= url('admin/dashboard.php') ?>">Dashboard</a>
                <a class="list-group-item list-group-item-action <?= active_link('users.php') ?>" href="<?= url('admin/users.php') ?>">Admin Users</a>
                <a class="list-group-item list-group-item-action <?= active_link('products.php') ?>" href="<?= url('admin/products.php') ?>">Products</a>
                <a class="list-group-item list-group-item-action <?= active_link('categories.php') ?>" href="<?= url('admin/categories.php') ?>">Categories</a>
                <a class="list-group-item list-group-item-action <?= active_link('inventory.php') ?>" href="<?= url('admin/inventory.php') ?>">Inventory</a>
                <a class="list-group-item list-group-item-action <?= active_link('orders.php') ?>" href="<?= url('admin/orders.php') ?>">Orders</a>
                <a class="list-group-item list-group-item-action <?= active_link('reports.php') ?>" href="<?= url('admin/reports.php') ?>">Reports</a>
                <a class="list-group-item list-group-item-action" href="<?= url('index.php') ?>">View Store</a>
            </div>
        </aside>
        <main class="col-lg-10 admin-content p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h1 class="h3 mb-0"><?= e($pageTitle) ?></h1>
            </div>
            <?php display_flash(); ?>

