<?php
declare(strict_types=1);

function set_flash(string $type, string $message): void
{
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message,
    ];
}

function display_flash(): void
{
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);

    foreach ($messages as $message) {
        $type = e($message['type']);
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
        echo e($message['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}

function remember_old(array $data): void
{
    $_SESSION['old_input'] = $data;
}

function old(string $key, string $default = ''): string
{
    return (string) ($_SESSION['old_input'][$key] ?? $default);
}

function clear_old(): void
{
    unset($_SESSION['old_input']);
}

