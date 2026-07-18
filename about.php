<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'About';
include __DIR__ . '/templates/header.php';
?>
<section class="section-band py-5">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-7">
                <h1 class="display-6 fw-bold"><?= e(APP_NAME) ?></h1>
                <p class="lead"><?= e(APP_TAGLINE) ?></p>
                <p><?= e(APP_NAME) ?> is a student-built PHP and MySQL e-commerce website for office equipment including office chairs, tables, filing cabinets, desks, shelves, and accessories.</p>
            </div>
            <div class="col-lg-5">
                <img class="img-fluid rounded border bg-white" src="<?= asset('images/group-placeholder.svg') ?>" alt="Group picture placeholder">
            </div>
        </div>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h2 class="h4">Mission</h2>
                <p>To provide a clear, secure, and user-friendly online ordering system for office equipment while demonstrating practical web development concepts.</p>
            </div>
            <div class="col-md-4">
                <h2 class="h4">Vision</h2>
                <p>To become a model academic e-commerce project that shows how small businesses can manage products, inventory, customers, and orders digitally.</p>
            </div>
            <div class="col-md-4">
                <h2 class="h4">Objectives</h2>
                <p>Build customer registration, product browsing, cart, checkout, simulated payment, admin management, reporting, and secure database operations using core PHP.</p>
            </div>
        </div>
    </div>
</section>
<section class="section-band py-5">
    <div class="container">
        <h2 class="h3 fw-bold mb-4">Group Members</h2>
        <div class="row g-3">
            <?php foreach (['Patrick Jhon S. Roxas', 'Paul Terence S. Guadalupe', 'Eirhiz Jericko Maniego', 'Mason Andrei G. Cedo'] as $member): ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="fw-semibold"><?= e($member) ?></div>
                            <div class="small text-muted">Project Member</div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>

