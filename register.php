<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Customer Registration';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('danger', 'Invalid form token. Please try again.');
        redirect('register.php');
    }

    $data = [
        'full_name' => trim((string) ($_POST['full_name'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'password' => (string) ($_POST['password'] ?? ''),
        'confirm_password' => (string) ($_POST['confirm_password'] ?? ''),
        'address' => trim((string) ($_POST['address'] ?? '')),
        'contact_number' => trim((string) ($_POST['contact_number'] ?? '')),
    ];

    $errors = validate_registration($data);

    $stmt = db()->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $data['email']]);
    if ($stmt->fetch()) {
        $errors['email'] = 'Email address is already registered.';
    }

    if (empty($errors)) {
        db()->beginTransaction();
        try {
            $userStmt = db()->prepare(
                'INSERT INTO users (full_name, email, password_hash, role, status, email_verified_at, created_at)
                 VALUES (:full_name, :email, :password_hash, "customer", "active", CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)'
            );
            $userStmt->execute([
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            ]);

            $userId = (int) db()->lastInsertId();

            $customerStmt = db()->prepare(
                'INSERT INTO customers (user_id, address, contact_number)
                 VALUES (:user_id, :address, :contact_number)'
            );
            $customerStmt->execute([
                'user_id' => $userId,
                'address' => $data['address'],
                'contact_number' => $data['contact_number'],
            ]);

            db()->commit();

            set_flash('success', 'Registration successful. You can now log in with your email and password.');
            redirect('login.php');
        } catch (Throwable $exception) {
            db()->rollBack();
            error_log($exception->getMessage());
            set_flash('danger', 'Registration failed. Please try again.');
        }
    }

    remember_old([
        'full_name' => $data['full_name'],
        'email' => $data['email'],
        'address' => $data['address'],
        'contact_number' => $data['contact_number'],
    ]);
}

include __DIR__ . '/templates/header.php';
?>
<section class="section-band py-5">
    <div class="container">
        <div class="auth-panel card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <h1 class="h3 fw-bold mb-1">Create Customer Account</h1>
                <p class="text-muted">Create your account, then log in with your email and password.</p>
                <?php display_flash(); ?>
                <form method="post" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="full_name">Complete Name</label>
                        <input class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" id="full_name" name="full_name" value="<?= e(old('full_name')) ?>" required>
                        <div class="invalid-feedback"><?= e($errors['full_name'] ?? 'Complete name is required.') ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">Valid Email Address</label>
                        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= e(old('email')) ?>" required>
                        <div class="invalid-feedback"><?= e($errors['email'] ?? 'Valid email is required.') ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" data-password-strength required minlength="8">
                        <div class="password-meter mt-2" data-password-meter><span></span></div>
                        <div class="invalid-feedback"><?= e($errors['password'] ?? 'Strong password is required.') ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="confirm_password">Confirm Password</label>
                        <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" id="confirm_password" name="confirm_password" required minlength="8">
                        <div class="invalid-feedback"><?= e($errors['confirm_password'] ?? 'Please confirm your password.') ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="address">Complete Address</label>
                        <textarea class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" id="address" name="address" rows="3" required><?= e(old('address')) ?></textarea>
                        <div class="invalid-feedback"><?= e($errors['address'] ?? 'Address is required.') ?></div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="contact_number">Contact Number</label>
                        <input class="form-control <?= isset($errors['contact_number']) ? 'is-invalid' : '' ?>" id="contact_number" name="contact_number" value="<?= e(old('contact_number')) ?>" required>
                        <div class="invalid-feedback"><?= e($errors['contact_number'] ?? 'Contact number is required.') ?></div>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Register</button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php clear_old(); include __DIR__ . '/templates/footer.php'; ?>
