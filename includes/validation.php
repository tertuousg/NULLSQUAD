<?php
declare(strict_types=1);

function required_value(array $data, string $field): bool
{
    return isset($data[$field]) && trim((string) $data[$field]) !== '';
}

function validate_password_strength(string $password): bool
{
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/[0-9]/', $password);
}

function validate_registration(array $data): array
{
    $errors = [];

    foreach (['full_name', 'email', 'password', 'confirm_password', 'address', 'contact_number'] as $field) {
        if (!required_value($data, $field)) {
            $errors[$field] = 'This field is required.';
        }
    }

    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (!empty($data['password']) && !validate_password_strength((string) $data['password'])) {
        $errors['password'] = 'Password must have at least 8 characters, one uppercase letter, one lowercase letter, and one number.';
    }

    if (($data['password'] ?? '') !== ($data['confirm_password'] ?? '')) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    return $errors;
}

function validate_product(array $data): array
{
    $errors = [];

    foreach (['name', 'description', 'category_id', 'price', 'quantity', 'status'] as $field) {
        if (!required_value($data, $field)) {
            $errors[$field] = 'This field is required.';
        }
    }

    if (isset($data['price']) && (float) $data['price'] < 0) {
        $errors['price'] = 'Price must not be negative.';
    }

    if (isset($data['quantity']) && (int) $data['quantity'] < 0) {
        $errors['quantity'] = 'Quantity must not be negative.';
    }

    return $errors;
}

