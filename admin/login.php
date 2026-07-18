<?php
require_once __DIR__ . '/../includes/init.php';

if (is_admin()) {
    redirect('admin/dashboard.php');
}

$pageTitle = 'Admin Login';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('danger', 'Invalid form token. Please try again.');
        redirect('admin/login.php');
    }

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } elseif (attempt_login($email, $password, 'admin')) {
        redirect('admin/dashboard.php');
    } else {
        $error = 'Invalid administrator credentials.';
    }

    remember_old(['email' => $email]);
}

include __DIR__ . '/../templates/header.php';
?>
<section class="section-band py-5">
    <div class="container">
        <div class="auth-panel card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <h1 class="h3 fw-bold mb-1">Admin Login</h1>
                <p class="text-muted">Authorized sellers can manage products, inventory, orders, and reports.</p>
                <?php display_flash(); ?>
                <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                <form method="post" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= e(old('email')) ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Login</button>
                </form>
                <p class="small text-muted mt-3 mb-0">Sample admin: admin@primedesk.test / password</p>
            </div>
        </div>
    </div>
</section>
<?php clear_old(); include __DIR__ . '/../templates/footer.php'; ?>

