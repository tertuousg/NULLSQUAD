<?php
require_once __DIR__ . '/../includes/init.php';

require_admin();

$pageTitle = 'Admin User Management';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('danger', 'Invalid form token. Please try again.');
        redirect('admin/users.php');
    }

    $action = (string) ($_POST['action'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    try {
        if ($action === 'create') {
            $fullName = trim((string) ($_POST['full_name'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $status = (string) ($_POST['status'] ?? 'active');

            if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
                throw new RuntimeException('Complete name, valid email, and password are required.');
            }

            $stmt = db()->prepare(
                'INSERT INTO users (full_name, email, password_hash, role, status, email_verified_at, created_at)
                 VALUES (:full_name, :email, :password_hash, "admin", :status, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)'
            );
            $stmt->execute([
                'full_name' => $fullName,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'status' => $status,
            ]);
            log_activity((int) current_user()['id'], current_user()['email'], 'Add admin user: ' . $email);
            set_flash('success', 'Admin user added.');
        } elseif ($action === 'update') {
            $stmt = db()->prepare('UPDATE users SET full_name = :full_name, email = :email, status = :status WHERE id = :id AND role = "admin"');
            $stmt->execute([
                'full_name' => trim((string) ($_POST['full_name'] ?? '')),
                'email' => trim((string) ($_POST['email'] ?? '')),
                'status' => (string) ($_POST['status'] ?? 'active'),
                'id' => $id,
            ]);
            log_activity((int) current_user()['id'], current_user()['email'], 'Update admin user ID ' . $id);
            set_flash('success', 'Admin user updated.');
        } elseif ($action === 'reset_password') {
            $password = (string) ($_POST['new_password'] ?? '');
            if ($password === '') {
                throw new RuntimeException('New password is required.');
            }
            $stmt = db()->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id AND role = "admin"');
            $stmt->execute([
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'id' => $id,
            ]);
            log_activity((int) current_user()['id'], current_user()['email'], 'Reset admin password ID ' . $id);
            set_flash('success', 'Password reset successfully.');
        } elseif ($action === 'delete') {
            if ($id === (int) current_user()['id']) {
                throw new RuntimeException('You cannot delete your own account.');
            }
            $stmt = db()->prepare('DELETE FROM users WHERE id = :id AND role = "admin"');
            $stmt->execute(['id' => $id]);
            log_activity((int) current_user()['id'], current_user()['email'], 'Delete admin user ID ' . $id);
            set_flash('success', 'Admin user deleted.');
        }
    } catch (Throwable $exception) {
        set_flash('danger', $exception->getMessage());
    }

    redirect('admin/users.php');
}

$editUser = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM users WHERE id = :id AND role = "admin" LIMIT 1');
    $stmt->execute(['id' => (int) $_GET['edit']]);
    $editUser = $stmt->fetch();
}

$users = db()->query('SELECT * FROM users WHERE role = "admin" ORDER BY created_at DESC')->fetchAll();

include __DIR__ . '/../templates/admin_header.php';
?>
<div class="row g-4">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3"><?= $editUser ? 'Edit Admin User' : 'Add Admin User' ?></h2>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="<?= $editUser ? 'update' : 'create' ?>">
                    <?php if ($editUser): ?><input type="hidden" name="id" value="<?= (int) $editUser['id'] ?>"><?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label" for="full_name">Complete Name</label>
                        <input class="form-control" id="full_name" name="full_name" value="<?= e($editUser['full_name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= e($editUser['email'] ?? '') ?>" required>
                    </div>
                    <?php if (!$editUser): ?>
                        <div class="mb-3">
                            <label class="form-label" for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active" <?= selected($editUser['status'] ?? 'active', 'active') ?>>Active</option>
                            <option value="inactive" <?= selected($editUser['status'] ?? '', 'inactive') ?>>Inactive</option>
                        </select>
                    </div>
                    <button class="btn btn-primary" type="submit"><?= $editUser ? 'Save Changes' : 'Add User' ?></button>
                    <?php if ($editUser): ?><a class="btn btn-outline-secondary" href="<?= url('admin/users.php') ?>">Cancel</a><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3">Admin Accounts</h2>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Reset Password</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= e($user['full_name']) ?></td>
                                <td><?= e($user['email']) ?></td>
                                <td><span class="badge text-bg-<?= badge_class($user['status']) ?>"><?= e(ucfirst($user['status'])) ?></span></td>
                                <td>
                                    <form method="post" class="d-flex gap-2">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="reset_password">
                                        <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                        <input type="password" class="form-control form-control-sm" name="new_password" placeholder="New password" required>
                                        <button class="btn btn-sm btn-outline-primary" type="submit">Reset</button>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= url('admin/users.php?edit=' . (int) $user['id']) ?>">Edit</a>
                                    <form method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Delete this admin user?">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../templates/admin_footer.php'; ?>

