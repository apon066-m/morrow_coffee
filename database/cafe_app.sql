CREATE DATABASE IF NOT EXISTS cafe_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cafe_app;

CREATE TABLE IF NOT EXISTS users (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 email VARCHAR(120) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 role ENUM('customer','staff','admin') NOT NULL DEFAULT 'customer',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 category VARCHAR(60) NOT NULL,
 description TEXT NOT NULL,
 price DECIMAL(10,2) NOT NULL,
 image VARCHAR(255) DEFAULT 'assets/images/coffee.jpg',
 available TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 created_by INT NULL,
 updated_by INT NULL,
 FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
 FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS orders (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NULL,
 customer_name VARCHAR(100) NOT NULL,
 total DECIMAL(10,2) NOT NULL DEFAULT 0,
 status ENUM('pending','preparing','ready','completed','cancelled') NOT NULL DEFAULT 'pending',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 updated_by INT NULL,
 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
 FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS order_items (
 id INT AUTO_INCREMENT PRIMARY KEY,
 order_id INT NOT NULL,
 product_id INT NULL,
 product_name VARCHAR(120) NOT NULL,
 quantity INT NOT NULL DEFAULT 1,
 price DECIMAL(10,2) NOT NULL,
 FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
 FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS activity_logs (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NULL,
 action VARCHAR(180) NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO products (name,category,description,price,image)
SELECT * FROM (SELECT 'House Latte','Coffee','Smooth espresso with steamed milk and a soft layer of foam.',5.50,'assets/images/menu/latte-art.jpg') AS x
WHERE NOT EXISTS (SELECT 1 FROM products LIMIT 1);
INSERT INTO products (name,category,description,price,image)
SELECT 'Iced Latte','Iced','Espresso poured over cold milk and ice.',6.00,'assets/images/menu/cup.jpg' WHERE NOT EXISTS (SELECT 1 FROM products WHERE name='Iced Latte');
INSERT INTO products (name,category,description,price,image)
SELECT 'Long Black','Coffee','Double espresso finished with hot water.',4.50,'assets/images/menu/saucer.jpg' WHERE NOT EXISTS (SELECT 1 FROM products WHERE name='Long Black');
INSERT INTO products (name,category,description,price,image)
SELECT 'Cappuccino','Coffee','Espresso, steamed milk and velvety foam.',5.50,'assets/images/menu/top.jpg' WHERE NOT EXISTS (SELECT 1 FROM products WHERE name='Cappuccino');
INSERT INTO products (name,category,description,price,image)
SELECT 'Mocha','Coffee','Espresso, chocolate and steamed milk.',6.00,'assets/images/menu/latte-flip.jpg' WHERE NOT EXISTS (SELECT 1 FROM products WHERE name='Mocha');
INSERT INTO products (name,category,description,price,image)
SELECT 'Iced Mocha','Iced','Chocolate espresso, cold milk and ice.',6.50,'assets/images/menu/cup-flip.jpg' WHERE NOT EXISTS (SELECT 1 FROM products WHERE name='Iced Mocha');
