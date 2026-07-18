<?php
require_once __DIR__ . '/includes/init.php';

$token = trim((string) ($_GET['token'] ?? ''));

if ($token === '') {
    set_flash('danger', 'Verification token is missing.');
    redirect('login.php');
}

$stmt = db()->prepare(
    'SELECT ev.*, u.email
     FROM email_verification ev
     JOIN users u ON u.id = ev.user_id
     WHERE ev.token = :token AND ev.expires_at >= CURRENT_TIMESTAMP
     LIMIT 1'
);
$stmt->execute(['token' => $token]);
$verification = $stmt->fetch();

if (!$verification) {
    set_flash('danger', 'Verification link is invalid or expired.');
    redirect('login.php');
}

db()->beginTransaction();
try {
    $update = db()->prepare('UPDATE users SET status = "active", email_verified_at = CURRENT_TIMESTAMP WHERE id = :id');
    $update->execute(['id' => $verification['user_id']]);

    $delete = db()->prepare('DELETE FROM email_verification WHERE user_id = :user_id');
    $delete->execute(['user_id' => $verification['user_id']]);

    db()->commit();
    log_activity((int) $verification['user_id'], $verification['email'], 'Customer email verified');
    set_flash('success', 'Your email has been verified. You may now log in.');
} catch (Throwable $exception) {
    db()->rollBack();
    error_log($exception->getMessage());
    set_flash('danger', 'Email verification failed.');
}

redirect('login.php');

