<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Home';
$featuredProducts = [];
$categories = [];
$databaseReady = true;

try {
    $featuredStatement = db()->query(
        'SELECT p.*, c.name AS category_name, i.quantity AS stock
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id
         INNER JOIN inventory i ON i.product_id = p.id
         WHERE p.status = "active"
         ORDER BY p.created_at DESC
         LIMIT 6'
    );
    $featuredProducts = $featuredStatement->fetchAll();

    $categoryStatement = db()->query(
        'SELECT c.*, COUNT(p.id) AS product_count
         FROM categories c
         LEFT JOIN products p
            ON p.category_id = c.id
            AND p.status = "active"
         WHERE c.status = "active"
         GROUP BY c.id
         ORDER BY c.name'
    );
    $categories = $categoryStatement->fetchAll();
} catch (Throwable $exception) {
    $databaseReady = false;
}

include __DIR__ . '/templates/header.php';
?>
<section class="hero">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8 col-xl-7">
                <p class="text-uppercase fw-semibold text-accent mb-2">
                    <?= e(APP_TAGLINE) ?>
                </p>

                <h1 class="display-4 fw-bold mb-4">
                    Better equipment for better workdays.
                </h1>

                <p class="lead mb-4">
                    Browse dependable chairs, desks, storage, tables, and office
                    accessories for schools, businesses, and growing teams.
                </p>

                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-primary btn-lg" href="<?= url('store.php') ?>">
                        Shop Products
                    </a>
                    <a class="btn btn-light btn-lg" href="<?= url('about.php') ?>">
                        About Our Team
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <?php display_flash(); ?>

        <?php if (!$databaseReady): ?>
            <div class="alert alert-warning setup-notice" role="alert">
                <h2 class="h5 alert-heading">Database setup needed</h2>
                <p class="mb-1">
                    The website is running, but product and account data cannot load yet.
                </p>
                <p class="mb-0 small">
                    Import <strong>database/ecommerce_db.sql</strong>, then check the
                    credentials inside <strong>config/config.php</strong>.
                </p>
            </div>
        <?php endif; ?>

        <div class="row g-4 mt-1">
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <div class="feature-number">01</div>
                    <h2 class="h5 fw-bold">Practical selection</h2>
                    <p class="text-muted mb-0">
                        Products are grouped by real office needs, making the store easy
                        to browse and manage.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card h-100">
                    <div class="feature-number">02</div>
                    <h2 class="h5 fw-bold">Simple ordering</h2>
                    <p class="text-muted mb-0">
                        Customers can register, add items to a cart, check out, and follow
                        their order history.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card h-100">
                    <div class="feature-number">03</div>
                    <h2 class="h5 fw-bold">Clear administration</h2>
                    <p class="text-muted mb-0">
                        The admin panel keeps products, stock, users, orders, and reports
                        in one organized workspace.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($databaseReady): ?>
    <section class="pb-5">
        <div class="container">
            <div class="row align-items-end mb-4">
                <div class="col-lg-8">
                    <p class="section-kicker mb-2">Recently added</p>
                    <h2 class="h3 fw-bold mb-1">Featured office equipment</h2>
                    <p class="text-muted mb-0">
                        A few useful pieces to help build a cleaner, more comfortable workspace.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a class="btn btn-outline-primary" href="<?= url('store.php') ?>">
                        Browse the Store
                    </a>
                </div>
            </div>

            <?php if ($featuredProducts): ?>
                <div class="row g-4">
                    <?php foreach ($featuredProducts as $product): ?>
                        <div class="col-sm-6 col-lg-4">
                            <?php include __DIR__ . '/templates/product_card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3 class="h5">No featured products yet</h3>
                    <p class="text-muted mb-0">
                        Add an active product through the admin panel and it will appear here.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<section class="section-band py-5">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-5">
                <p class="section-kicker mb-2">Shop by purpose</p>
                <h2 class="h3 fw-bold">Everything a workspace needs</h2>
                <p class="text-muted mb-0">
                    Find seating, work surfaces, storage, and accessories without digging
                    through a crowded catalog.
                </p>
            </div>

            <div class="col-lg-7">
                <?php if ($databaseReady && $categories): ?>
                    <div class="row g-3">
                        <?php foreach ($categories as $category): ?>
                            <div class="col-sm-6">
                                <a
                                    class="category-card"
                                    href="<?= url('store.php?category=' . (int) $category['id']) ?>"
                                >
                                    <span class="fw-bold"><?= e($category['name']) ?></span>
                                    <span class="small text-muted">
                                        <?= (int) $category['product_count'] ?> products
                                    </span>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php
                        $previewCategories = [
                            ['name' => 'Office Chairs', 'description' => 'Comfort for long work sessions'],
                            ['name' => 'Desks and Tables', 'description' => 'Reliable surfaces for every task'],
                            ['name' => 'Storage', 'description' => 'Cabinets and shelves that reduce clutter'],
                            ['name' => 'Accessories', 'description' => 'Small details that improve daily work'],
                        ];
                        ?>

                        <?php foreach ($previewCategories as $category): ?>
                            <div class="col-sm-6">
                                <div class="category-card category-card-static">
                                    <span class="fw-bold"><?= e($category['name']) ?></span>
                                    <span class="small text-muted"><?= e($category['description']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-7">
                <p class="section-kicker mb-2">About the project</p>
                <h2 class="h3 fw-bold">A complete student-built e-commerce system</h2>
                <p class="mb-0">
                    <?= e(APP_NAME) ?> demonstrates customer registration, email verification,
                    secure login, product browsing, cart management, checkout, simulated payment,
                    order tracking, inventory control, and administrative reporting.
                </p>
            </div>

            <div class="col-lg-5">
                <div class="card dashboard-stat border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold">Designed for a clear presentation</h3>
                        <p class="text-muted mb-0">
                            The project uses reusable components and organized folders so each
                            part of the system is easier to explain, maintain, and improve.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/templates/footer.php'; ?>
