<?php
declare(strict_types=1);

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

function is_customer(): bool
{
    return (current_user()['role'] ?? '') === 'customer';
}

function attempt_login(string $email, string $password, ?string $requiredRole = null): bool
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    if ($requiredRole !== null && $user['role'] !== $requiredRole) {
        return false;
    }

    if ($user['status'] !== 'active') {
        set_flash('warning', 'Your account is inactive. Please verify your email or contact the administrator.');
        return false;
    }

    if ($user['role'] === 'customer' && empty($user['email_verified_at'])) {
        set_flash('warning', 'Please verify your email address before logging in.');
        return false;
    }

    regenerate_session();

    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];

    if (function_exists('log_activity')) {
        log_activity((int) $user['id'], $user['email'], ucfirst($user['role']) . ' login');
    }

    return true;
}

function require_admin(): void
{
    if (!is_logged_in() || !is_admin()) {
        set_flash('warning', 'Please log in as an administrator.');
        redirect('admin/login.php');
    }
}

function require_customer(): void
{
    if (!is_logged_in() || !is_customer()) {
        set_flash('warning', 'Please log in to continue.');
        redirect('login.php');
    }
}

function logout_user(): void
{
    if (function_exists('log_activity') && is_logged_in()) {
        $user = current_user();
        log_activity((int) $user['id'], $user['email'], ucfirst($user['role']) . ' logout');
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
    session_write_close();
    session_id('');
    session_start();
    regenerate_session();
}
