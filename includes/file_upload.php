<?php
declare(strict_types=1);

/*
| Validates and stores product images uploaded by administrators.
*/

function handle_product_image_upload(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed. Please try again.');
    }

    if (($file['size'] ?? 0) > MAX_UPLOAD_SIZE) {
        throw new RuntimeException('Image must be 2MB or smaller.');
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions, true)) {
        throw new RuntimeException('Only JPG, PNG, GIF, and WEBP images are allowed.');
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0775, true);
    }

    $filename = 'product_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destination = UPLOAD_DIR . $filename;

    if (!move_uploaded_file((string) $file['tmp_name'], $destination)) {
        throw new RuntimeException('Unable to save uploaded image.');
    }

    return $filename;
}

