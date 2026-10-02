USE cafe_app;
ALTER TABLE users MODIFY role ENUM('customer','staff','admin') NOT NULL DEFAULT 'customer';
UPDATE products SET image=REPLACE(image,'.png','.jpg') WHERE image LIKE 'assets/images/%.png';
-- Audit columns (run once). If a column already exists, skip that line.
ALTER TABLE products ADD COLUMN created_by INT NULL, ADD COLUMN updated_by INT NULL;
ALTER TABLE orders ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN updated_by INT NULL;

-- Give each seeded drink its own photo (only if it still has the old shared default photo)
UPDATE products SET image='assets/images/menu/latte-art.jpg' WHERE name='House Latte' AND image IN ('assets/images/coffee.jpg','assets/images/coffee3.jpg','assets/images/coffee.png','assets/images/coffee3.png');
UPDATE products SET image='assets/images/menu/cup.jpg' WHERE name='Iced Latte' AND image IN ('assets/images/coffee.jpg','assets/images/coffee3.jpg','assets/images/coffee.png','assets/images/coffee3.png');
UPDATE products SET image='assets/images/menu/saucer.jpg' WHERE name='Long Black' AND image IN ('assets/images/coffee.jpg','assets/images/coffee3.jpg','assets/images/coffee.png','assets/images/coffee3.png');
UPDATE products SET image='assets/images/menu/top.jpg' WHERE name='Cappuccino' AND image IN ('assets/images/coffee.jpg','assets/images/coffee3.jpg','assets/images/coffee.png','assets/images/coffee3.png');
UPDATE products SET image='assets/images/menu/latte-flip.jpg' WHERE name='Mocha' AND image IN ('assets/images/coffee.jpg','assets/images/coffee3.jpg','assets/images/coffee.png','assets/images/coffee3.png');
UPDATE products SET image='assets/images/menu/cup-flip.jpg' WHERE name='Iced Mocha' AND image IN ('assets/images/coffee.jpg','assets/images/coffee3.jpg','assets/images/coffee.png','assets/images/coffee3.png');
