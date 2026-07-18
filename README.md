# Null Squad Office Solutions

Null Squad Office Solutions is a complete core PHP and MySQL e-commerce website for office equipment. It uses PHP, MySQL, HTML5, CSS3, JavaScript, Bootstrap 5, sessions, prepared statements, CSRF tokens, password hashing, form validation, file upload validation, and audit logs.

## Company and Group

Company name: Null Squad Office Solutions

Group name: Null Squad

Members:

- Patrick Jhon S. Roxas
- Paul Terence S. Guadalupe
- Eirhiz Jericko Maniego
- Mason Andrei G. Cedo

## Main Features

- Customer registration with email verification token
- Customer login, shopping cart, checkout, simulated payment, and order history
- Admin login and protected admin dashboard
- Admin user management with add, edit, delete, reset password, and account status controls
- Product management with image upload, category assignment, price, status, search, and stock update
- Category management
- Inventory report with low stock and out-of-stock alerts
- Order status management and checkout approval audit logging
- Audit log report for important actions

## Sample Accounts

Import `database/ecommerce_db.sql` first.

- Admin: `admin@NullSquad.test` / `password`
- Customer: `customer@NullSquad.test` / `password`

Change these passwords after installation.

## Important Files

- `config/config.php` - database and app settings
- `database/ecommerce_db.sql` - complete database schema and sample data
- `includes/` - database, session, auth, CSRF, validation, cart, mail, and upload helpers
- `templates/` - reusable public and admin layouts
- `admin/` - seller/admin panel
- Root PHP files - buyer website pages
- `docs/` - installation guide, database explanation, system flow, and user manual

## Email Verification Note

The registration page creates a real verification token. If PHPMailer is installed through Composer, `includes/mailer.php` can send the verification email. If PHPMailer is not installed, the system still works for classroom demonstrations by showing the development verification link after registration and writing it to the PHP error log.

