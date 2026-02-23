-- ===================================
-- CHATRAMUE TEA SHOP - DATABASE DESIGN
-- ===================================

CREATE DATABASE IF NOT EXISTS chatramue_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE chatramue_db;

-- -----------------------------------
-- TABLE: users (สมาชิก)
-- -----------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('admin','member') DEFAULT 'member',
    avatar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- -----------------------------------
-- TABLE: categories (หมวดหมู่สินค้า)
-- -----------------------------------
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    name_en VARCHAR(100),
    description TEXT,
    image VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------
-- TABLE: products (สินค้า/เมนูชา)
-- -----------------------------------
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    name_en VARCHAR(150),
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    is_popular TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    stock INT DEFAULT 999,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- -----------------------------------
-- TABLE: toppings (ท็อปปิ้ง)
-- -----------------------------------
CREATE TABLE toppings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------
-- TABLE: promotions (โปรโมชั่น / คูปอง)
-- -----------------------------------
CREATE TABLE promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255),
    discount_type ENUM('percent','fixed') DEFAULT 'percent',
    discount_value DECIMAL(10,2) NOT NULL,
    min_order DECIMAL(10,2) DEFAULT 0,
    max_uses INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    start_date DATE,
    end_date DATE,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------
-- TABLE: orders (ออเดอร์)
-- -----------------------------------
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    final_amount DECIMAL(10,2) NOT NULL,
    promotion_id INT DEFAULT NULL,
    shipping_name VARCHAR(100),
    shipping_phone VARCHAR(20),
    shipping_address TEXT,
    payment_method ENUM('cash','transfer','promptpay') DEFAULT 'cash',
    status ENUM('pending','processing','completed','cancelled') DEFAULT 'pending',
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (promotion_id) REFERENCES promotions(id) ON DELETE SET NULL
);

-- -----------------------------------
-- TABLE: order_items (รายการในออเดอร์)
-- -----------------------------------
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(150),
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    sweetness ENUM('0%','25%','50%','75%','100%') DEFAULT '100%',
    ice_level ENUM('no_ice','less_ice','normal_ice','full_ice') DEFAULT 'normal_ice',
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- -----------------------------------
-- TABLE: order_item_toppings (ท็อปปิ้งในออเดอร์)
-- -----------------------------------
CREATE TABLE order_item_toppings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_item_id INT NOT NULL,
    topping_id INT NOT NULL,
    topping_name VARCHAR(100),
    price DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE,
    FOREIGN KEY (topping_id) REFERENCES toppings(id)
);

-- -----------------------------------
-- TABLE: cart (ตะกร้าสินค้า - session-based)
-- -----------------------------------
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    sweetness ENUM('0%','25%','50%','75%','100%') DEFAULT '100%',
    ice_level ENUM('no_ice','less_ice','normal_ice','full_ice') DEFAULT 'normal_ice',
    toppings_json TEXT DEFAULT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ===================================
-- SAMPLE DATA
-- ===================================

INSERT INTO users (username, email, password, full_name, phone, role) VALUES
('admin', 'admin@chatramue.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ผู้ดูแลระบบ', '0812345678', 'admin'),
('member1', 'member@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'สมชาย ใจดี', '0898765432', 'member');
-- password for both: 'password'

INSERT INTO categories (name, name_en, sort_order) VALUES
('ชาไทย', 'Thai Tea', 1),
('ชาเขียว', 'Green Tea', 2),
('ชามะนาว', 'Lemon Tea', 3),
('ชาดำ', 'Black Tea', 4),
('นมชา', 'Milk Tea', 5);

INSERT INTO products (category_id, name, description, price, is_popular) VALUES
(1, 'ชาไทยนมสด', 'ชาไทยสูตรต้นตำรับ ผสมนมสดแท้ หอมกลมกล่อม', 55.00, 1),
(1, 'ชาไทยใส่นม', 'ชาไทยสีส้มสวย ผสมนมข้นหวาน', 45.00, 1),
(1, 'ชาไทยเย็น', 'ชาไทยเข้มข้น เย็นชื่นใจ', 40.00, 0),
(2, 'ชาเขียวมัทฉะ', 'มัทฉะแท้จากญี่ปุ่น นุ่มหอม', 60.00, 1),
(2, 'ชาเขียวนมสด', 'ชาเขียวผสมนมสดเย็น สดชื่น', 55.00, 0),
(3, 'ชามะนาวโซดา', 'ชามะนาวซ่า สดชื่นมาก', 45.00, 1),
(3, 'ชามะนาวเย็น', 'ชามะนาวเย็น ชาแท้ หอมมะนาว', 40.00, 0),
(4, 'ชาดำร้อน', 'ชาดำต้นตำรับ หอมชาแท้', 35.00, 0),
(5, 'นมชาไข่มุก', 'นมชาหอม ไข่มุกนุ่มหวาน', 65.00, 1),
(5, 'นมชามัทฉะ', 'นมชามัทฉะ เข้มข้น', 65.00, 0);

INSERT INTO toppings (name, price) VALUES
('ไข่มุก', 10.00),
('วุ้นมะพร้าว', 10.00),
('ไข่มุกคริสตัล', 10.00),
('เยลลี่', 10.00),
('ซากุระเยลลี่', 15.00),
('ครีมชีส', 20.00),
('ไม่เพิ่มท็อปปิ้ง', 0.00);

INSERT INTO promotions (code, description, discount_type, discount_value, min_order, end_date) VALUES
('WELCOME10', 'ส่วนลด 10% สำหรับสมาชิกใหม่', 'percent', 10.00, 100.00, '2025-12-31'),
('TEA50', 'ลด 50 บาท เมื่อสั่งครบ 200 บาท', 'fixed', 50.00, 200.00, '2025-12-31'),
('SUMMER20', 'ฤดูร้อนลด 20%', 'percent', 20.00, 150.00, '2025-08-31');
