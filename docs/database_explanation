# Database Explanation

The database name is `primedesk_ecommerce`. It uses InnoDB tables so foreign keys can enforce relationships between customers, products, carts, orders, payments, and logs.

## Tables

`users`

Stores login accounts for administrators and customers. Passwords are saved with PHP `password_hash()`. The `role` field separates `admin` and `customer` accounts. The `status` and `email_verified_at` fields control account activation.

`customers`

Stores customer-only profile details such as address and contact number. Each customer belongs to one user account.

`categories`

Stores product categories such as Office Chairs, Office Tables, Filing Cabinets, Office Desks, Shelves, and Accessories.

`products`

Stores product name, description, category, price, product image, and status.

`inventory`

Stores product stock quantity and low-stock threshold. It is separated from `products` so inventory can be managed and reported clearly.

`cart`

Stores customer cart items before checkout. A unique key prevents duplicate rows for the same customer and product.

`orders`

Stores completed checkout records, reference numbers, shipping address, total amount, payment method, order status, and payment status.

`order_items`

Stores the products inside each order. Product name and price are copied at checkout so the receipt remains accurate even if the product changes later.

`payments`

Stores simulated payment records for Cash on Delivery, Bank Transfer, and GCash.

`audit_logs`

Stores important activity such as login, logout, adding products, updating stocks, deleting records, and checkout approval.

`email_verification`

Stores email verification tokens for new customer accounts.

`password_reset`

Prepared table for future forgot-password functionality.

## Main Relationships

```mermaid
erDiagram
    users ||--o| customers : "has profile"
    categories ||--o{ products : "groups"
    products ||--|| inventory : "has stock"
    customers ||--o{ cart : "adds"
    products ||--o{ cart : "selected in"
    customers ||--o{ orders : "places"
    orders ||--o{ order_items : "contains"
    products ||--o{ order_items : "sold as"
    orders ||--o{ payments : "paid by"
    users ||--o{ audit_logs : "performs"
    users ||--o{ email_verification : "verifies"
    users ||--o{ password_reset : "requests"
```

## Security Notes

- Every database operation uses PDO prepared statements.
- User input is escaped with `htmlspecialchars()` through the `e()` helper.
- Forms include CSRF tokens.
- Passwords are hashed with `password_hash()`.
- Product image uploads validate extension and file size.
- Admin pages require an active admin session.

