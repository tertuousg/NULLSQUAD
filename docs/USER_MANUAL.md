# User Manual

## Buyer Website

### Register

1. Open `register.php`.
2. Enter complete name, email, password, address, and contact number.
3. Submit the form.
4. Open the verification link shown after registration or sent by email if PHPMailer is configured.
5. Log in after verification.

### Login

1. Open `login.php`.
2. Enter customer email and password.
3. Click Login.

Sample customer account:

```text
customer@primedesk.test
password
```

### Shop Products

1. Open `store.php`.
2. Use search, category filter, or price filter.
3. Click View Details to inspect a product.
4. Enter quantity and click Add to Cart.

### Cart and Checkout

1. Open Cart.
2. Update quantities or remove items.
3. Click Proceed to Checkout.
4. Confirm shipping address.
5. Continue to Payment.
6. Choose Cash on Delivery, Bank Transfer, or GCash.
7. Click Place Order.
8. Save the generated reference number.

### Order History

1. Open Orders.
2. View previous orders and current order status.
3. Click Details to view order items and payment summary.

## Admin Panel

### Login

Open:

```text
admin/login.php
```

Sample admin account:

```text
admin@primedesk.test
password
```

### Manage Admin Users

1. Open Admin Users.
2. Add, edit, delete, activate, or deactivate admin accounts.
3. Use Reset Password to assign a new password.

### Manage Categories

1. Open Categories.
2. Add, edit, or delete categories.
3. Categories assigned to products cannot be deleted until products are reassigned or removed.

### Manage Products

1. Open Products.
2. Add product name, description, category, price, quantity, image, and status.
3. Use Search to find products quickly.
4. Edit products to change price, stock, image, category, or status.

### Manage Inventory

1. Open Inventory.
2. Review remaining stocks.
3. Watch low-stock and out-of-stock alerts.
4. Update quantity and low-stock threshold.

### Manage Orders

1. Open Orders.
2. Review customer orders.
3. Update order status to pending, processing, approved, delivered, or cancelled.
4. Update payment status to unpaid or paid.

### View Reports

1. Open Reports.
2. Review Inventory Report.
3. Review Audit Log Report with username, date, time, IP address, and action performed.

