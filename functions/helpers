<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_base_url(): string
{
    if (defined('BASE_URL') && BASE_URL !== '') {
        return rtrim(BASE_URL, '/');
    }

    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $scriptDir = rtrim($scriptDir, '/');

    if (preg_match('#/(admin|customer)$#', $scriptDir)) {
        $scriptDir = str_replace('\\', '/', dirname($scriptDir));
    }

    return $scriptDir === '/' || $scriptDir === '.' ? '' : $scriptDir;
}

function url(string $path = ''): string
{
    $base = app_base_url();
    $path = ltrim($path, '/');

    return $path === '' ? ($base ?: '/') : $base . '/' . $path;
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function upload_url(string $filename): string
{
    if ($filename === '') {
        return asset('images/product-placeholder.svg');
    }

    if (str_starts_with($filename, 'assets/')) {
        return url($filename);
    }

    return url('uploads/products/' . ltrim($filename, '/'));
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function money(float|int|string $amount): string
{
    return 'PHP ' . number_format((float) $amount, 2);
}

function selected(mixed $actual, mixed $expected): string
{
    return (string) $actual === (string) $expected ? 'selected' : '';
}

function checked(mixed $actual, mixed $expected): string
{
    return (string) $actual === (string) $expected ? 'checked' : '';
}

function active_link(string $page): string
{
    return basename($_SERVER['SCRIPT_NAME'] ?? '') === $page ? 'active' : '';
}

function badge_class(string $status): string
{
    return match ($status) {
        'active', 'completed', 'paid', 'approved', 'delivered' => 'success',
        'inactive', 'cancelled', 'out_of_stock' => 'danger',
        'pending', 'processing' => 'warning',
        default => 'secondary',
    };
}

function generate_order_reference(): string
{
    return 'PD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

function excerpt(string $text, int $limit = 110): string
{
    $plain = trim(strip_tags($text));

    if (strlen($plain) <= $limit) {
        return $plain;
    }

    return substr($plain, 0, $limit - 3) . '...';
}
