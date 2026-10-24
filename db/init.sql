CREATE DATABASE IF NOT EXISTS shop
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE shop;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(12, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (price >= 0),
    CHECK (stock >= 0)
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(150) NOT NULL,
    email VARCHAR(200) NOT NULL,
    address VARCHAR(500) NOT NULL,
    total_amount DECIMAL(12, 2) NOT NULL,
    status ENUM(
        'pending',
        'confirmed',
        'shipping',
        'completed',
        'cancelled'
    ) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (total_amount >= 0)
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(12, 2) NOT NULL,

    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id)
        REFERENCES orders(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id)
        REFERENCES products(id),

    CHECK (quantity > 0),
    CHECK (unit_price >= 0)
);

INSERT INTO products (name, description, price, stock)
VALUES
    ('Tai nghe Bluetooth',
     'Tai nghe không dây tiện lợi.',
     350000, 20),

    ('Chuột không dây',
     'Chuột máy tính phục vụ học tập.',
     220000, 30),

    ('Bàn phím cơ',
     'Bàn phím dùng cho làm việc và giải trí.',
     650000, 15);
