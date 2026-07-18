# System Flow Explanation

## Customer Flow

1. Customer visits the home page.
2. Customer registers with complete name, email, password, address, and contact number.
3. The system validates the form, checks duplicate email, hashes the password, creates an inactive account, and stores an email verification token.
4. Customer verifies the email address.
5. Customer logs in.
6. Customer browses the store using search, category filter, and price filter.
7. Customer adds products to the cart.
8. Customer updates or removes cart items.
9. Customer confirms shipping information on checkout.
10. Customer chooses Cash on Delivery, Bank Transfer, or GCash simulation on the payment page.
11. The system creates an order reference number, saves the order, saves order items, saves payment information, subtracts inventory stock, clears the cart, and records an audit log.
12. Customer views order history and order details.

## Admin Flow

1. Administrator logs in through the admin login page.
2. The system validates credentials, checks account status, starts a session, and records an audit log.
3. Administrator views dashboard summaries.
4. Administrator manages admin users.
5. Administrator manages categories.
6. Administrator manages products and product images.
7. Administrator updates inventory quantities and low-stock thresholds.
8. Administrator reviews orders and updates order or payment status.
9. If an order is approved, the system records checkout approval in the audit log.
10. Administrator views inventory and audit reports.
11. Administrator logs out and the session is destroyed.

## Checkout Transaction Flow

The payment page uses a database transaction to protect the order process:

1. Lock inventory rows for cart products.
2. Check if stock is still enough.
3. Insert order.
4. Insert order items.
5. Decrease inventory quantity.
6. Insert payment record.
7. Clear customer cart.
8. Commit the transaction.

If any step fails, the transaction is rolled back.

