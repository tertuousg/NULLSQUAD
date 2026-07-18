# Installation Guide

## Requirements

- XAMPP, WAMP, Laragon, or another local PHP server
- PHP 8.1 or newer
- MySQL or MariaDB
- PDO MySQL extension enabled
- A browser

## Setup Steps

1. Copy the `office_ecommerce_php` folder into your local web server folder.
   - XAMPP example: `C:\xampp\htdocs\office_ecommerce_php`

2. Start Apache and MySQL.

3. Open phpMyAdmin.

4. Import the SQL file:
   - Go to Import
   - Choose `database/ecommerce_db.sql`
   - Click Go

5. Open `config/config.php` and confirm the database settings:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'primedesk_ecommerce');
define('DB_USER', 'root');
define('DB_PASS', '');
```

6. Visit the website:

```text
http://localhost/office_ecommerce_php/index.php
```

7. Open the admin panel:

```text
http://localhost/office_ecommerce_php/admin/login.php
```

## Sample Accounts

- Admin: `admin@primedesk.test` / `password`
- Customer: `customer@primedesk.test` / `password`

## Optional PHPMailer Setup

The project is designed to run even without PHPMailer. For real email sending, install PHPMailer in the project folder:

```text
composer require phpmailer/phpmailer
```

Then configure SMTP settings inside `includes/mailer.php` if your teacher requires real email delivery.

## Upload Folder

Product images are stored in:

```text
uploads/products
```

Make sure this folder is writable by the web server.

