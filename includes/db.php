<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (DB_DRIVER === 'mysql' && extension_loaded('pdo_mysql')) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            return $pdo;
        } catch (PDOException $exception) {
            error_log('MySQL connection failed: ' . $exception->getMessage());
        }
    }

    if (!extension_loaded('pdo_sqlite')) {
        throw new RuntimeException('No database driver is available. Enable pdo_sqlite, or set DB_DRIVER=mysql with pdo_mysql installed.');
    }

    $dbPath = DB_PATH;
    $dbDir = dirname($dbPath);
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0775, true);
    }

    $initialize = !is_file($dbPath) || filesize($dbPath) === 0;
    $pdo = new PDO('sqlite:' . $dbPath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON;');

    if ($initialize) {
        initialize_sqlite_database($pdo);
    }

    return $pdo;
}

function initialize_sqlite_database(PDO $pdo): void
{
    $schema = <<<'SQL'
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'customer' CHECK (role IN ('admin', 'customer')),
    status TEXT NOT NULL DEFAULT 'inactive' CHECK (status IN ('active', 'inactive')),
    email_verified_at TEXT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NULL
);

CREATE TABLE IF NOT EXISTS customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    address TEXT NOT NULL,
    contact_number TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    description TEXT NULL,
    status TEXT NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    description TEXT NOT NULL,
    price REAL NOT NULL DEFAULT 0,
    image TEXT NULL,
    status TEXT NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);

CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id);
CREATE INDEX IF NOT EXISTS idx_products_name ON products(name);

CREATE TABLE IF NOT EXISTS inventory (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL UNIQUE,
    quantity INTEGER NOT NULL DEFAULT 0,
    low_stock_threshold INTEGER NOT NULL DEFAULT 5,
    updated_at TEXT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS cart (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(customer_id, product_id),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    reference_number TEXT NOT NULL UNIQUE,
    shipping_address TEXT NOT NULL,
    total_amount REAL NOT NULL,
    payment_method TEXT NOT NULL CHECK (payment_method IN ('Cash on Delivery', 'Bank Transfer', 'GCash')),
    order_status TEXT NOT NULL DEFAULT 'pending' CHECK (order_status IN ('pending', 'processing', 'approved', 'delivered', 'cancelled')),
    payment_status TEXT NOT NULL DEFAULT 'unpaid' CHECK (payment_status IN ('unpaid', 'paid')),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    product_name TEXT NOT NULL,
    price REAL NOT NULL,
    quantity INTEGER NOT NULL,
    subtotal REAL NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS payments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    method TEXT NOT NULL CHECK (method IN ('Cash on Delivery', 'Bank Transfer', 'GCash')),
    amount REAL NOT NULL,
    status TEXT NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'paid', 'failed')),
    proof_reference TEXT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NULL,
    username TEXT NOT NULL,
    action TEXT NOT NULL,
    ip_address TEXT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_audit_created_at ON audit_logs(created_at);

CREATE TABLE IF NOT EXISTS email_verification (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    token TEXT NOT NULL UNIQUE,
    expires_at TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS password_reset (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    token TEXT NOT NULL UNIQUE,
    expires_at TEXT NOT NULL,
    used_at TEXT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
SQL;

    $pdo->exec($schema);

    $seedCheck = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($seedCheck > 0) {
        return;
    }

    $seedHash = '$2y$10$RqW/.NT15PUm.K.YekhUdOrOACWffZu8MhdQ/Rb/.bfrvkvT2NnJm';

    $pdo->beginTransaction();
    try {
        $pdo->exec(
            "INSERT INTO users (id, full_name, email, password_hash, role, status, email_verified_at, created_at) VALUES
            (1, 'System Administrator', 'admin@primedesk.test', '$seedHash', 'admin', 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
            (2, 'Patrick Jhon S. Roxas', 'customer@primedesk.test', '$seedHash', 'customer', 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);"
        );

        $pdo->exec(
            "INSERT INTO customers (id, user_id, address, contact_number, created_at) VALUES
            (1, 2, 'Blk 8 Lot 15, Sample Street, Manila, Philippines', '0917-555-0188', CURRENT_TIMESTAMP);"
        );

        $pdo->exec(
            "INSERT INTO categories (id, name, description, status, created_at) VALUES
            (1, 'Office Chairs', 'Ergonomic and executive chairs for daily office use.', 'active', CURRENT_TIMESTAMP),
            (2, 'Office Tables', 'Conference, meeting, and utility office tables.', 'active', CURRENT_TIMESTAMP),
            (3, 'Filing Cabinets', 'Secure filing and document storage cabinets.', 'active', CURRENT_TIMESTAMP),
            (4, 'Office Desks', 'Work desks for individual and shared workspaces.', 'active', CURRENT_TIMESTAMP),
            (5, 'Shelves', 'Open shelving units for storage and display.', 'active', CURRENT_TIMESTAMP),
            (6, 'Accessories', 'Desk organizers, cable trays, and office accessories.', 'active', CURRENT_TIMESTAMP);"
        );

        $pdo->exec(
            "INSERT INTO products (id, category_id, name, description, price, image, status, created_at) VALUES
            (1, 1, 'ErgoPro Mesh Chair', 'Breathable mesh office chair with adjustable height and lumbar support.', 5499.00, 'assets/images/products/ergonomic-chair.svg', 'active', CURRENT_TIMESTAMP),
            (2, 1, 'Executive Cushion Chair', 'Premium cushioned chair for managers and meeting rooms.', 7899.00, 'assets/images/products/ergonomic-chair.svg', 'active', CURRENT_TIMESTAMP),
            (3, 4, 'Prime Executive Desk', 'Wide office desk with modesty panel and cable management opening.', 12499.00, 'assets/images/products/executive-desk.svg', 'active', CURRENT_TIMESTAMP),
            (4, 2, 'Conference Table 6-Seater', 'Durable meeting table with laminated top and metal legs.', 16999.00, 'assets/images/products/executive-desk.svg', 'active', CURRENT_TIMESTAMP),
            (5, 3, 'Four-Drawer Filing Cabinet', 'Lockable steel cabinet for office documents and records.', 6999.00, 'assets/images/products/filing-cabinet.svg', 'active', CURRENT_TIMESTAMP),
            (6, 5, 'Modular Storage Shelf', 'Adjustable office shelf for supplies, binders, and display items.', 3899.00, 'assets/images/products/storage-shelf.svg', 'active', CURRENT_TIMESTAMP),
            (7, 6, 'Desk Organizer Set', 'Organizer tray, pen holder, and document sorter set.', 899.00, 'assets/images/products/office-accessories.svg', 'active', CURRENT_TIMESTAMP),
            (8, 6, 'Cable Management Kit', 'Cable tray, ties, and clips for cleaner workstation setup.', 699.00, 'assets/images/products/office-accessories.svg', 'active', CURRENT_TIMESTAMP);"
        );

        $pdo->exec(
            "INSERT INTO inventory (product_id, quantity, low_stock_threshold, updated_at) VALUES
            (1, 18, 5, CURRENT_TIMESTAMP),
            (2, 8, 3, CURRENT_TIMESTAMP),
            (3, 10, 3, CURRENT_TIMESTAMP),
            (4, 4, 2, CURRENT_TIMESTAMP),
            (5, 12, 4, CURRENT_TIMESTAMP),
            (6, 2, 5, CURRENT_TIMESTAMP),
            (7, 40, 10, CURRENT_TIMESTAMP),
            (8, 35, 10, CURRENT_TIMESTAMP);"
        );

        $pdo->exec(
            "INSERT INTO audit_logs (user_id, username, action, ip_address, created_at) VALUES
            (1, 'admin@primedesk.test', 'Initial database seed created', 'local', CURRENT_TIMESTAMP);"
        );

        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}
