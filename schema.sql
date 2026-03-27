DROP DATABASE IF EXISTS shop;
CREATE DATABASE shop CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE shop;

CREATE TABLE users (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    login    VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE tokens (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    token      VARCHAR(128) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    revoked    TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    description TEXT         NOT NULL,
    price       DECIMAL(10,2) NOT NULL,
    img         VARCHAR(255) NOT NULL,
    type        VARCHAR(100) NOT NULL,
    quantity    INT NOT NULL DEFAULT 0
);

CREATE TABLE orders (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NULL,
    first_name  VARCHAR(100) NOT NULL,
    last_name   VARCHAR(100) NOT NULL,
    address     VARCHAR(255) NOT NULL,
    city        VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20)  NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE order_items (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    order_id     INT NOT NULL,
    product_id   INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    price        DECIMAL(10,2) NOT NULL,
    quantity     INT NOT NULL,
    CONSTRAINT fk_oi_order   FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_oi_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT ON UPDATE CASCADE
);

INSERT INTO products (name, description, price, img, type, quantity) VALUES
('Jaguar Wheel',  'Sample product for testing the frontend and API connection.', 199.99, 'sample-1.png', 'Parts',       8),
('Jaguar Jacket', 'Example listing used to verify search and cart actions.',      299.00, 'sample-2.png', 'Clothes',     5),
('Jaguar Mug',    'Simple demo item for quick checkout tests.',                    29.50, 'sample-3.png', 'Accessories', 20);
