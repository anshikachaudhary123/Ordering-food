-- ============================================
-- FOOD ORDERING SYSTEM - DATABASE SCHEMA
-- Run this file in phpMyAdmin or MySQL CLI
-- ============================================

CREATE DATABASE IF NOT EXISTS food_ordering_db;
USE food_ordering_db;

-- USERS TABLE
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- RESTAURANTS TABLE
CREATE TABLE IF NOT EXISTS restaurants (
    restaurant_id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_name VARCHAR(150) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    image VARCHAR(255) DEFAULT 'default.jpg',
    status TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- CATEGORIES TABLE
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    restaurant_id INT,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(restaurant_id) ON DELETE CASCADE
);

-- DISHES TABLE
CREATE TABLE IF NOT EXISTS dishes (
    dish_id INT AUTO_INCREMENT PRIMARY KEY,
    dish_name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) DEFAULT 'default.jpg',
    category_id INT,
    restaurant_id INT,
    status TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(restaurant_id) ON DELETE CASCADE
);

-- ORDERS TABLE
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    delivery_address TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ORDER DETAILS TABLE
CREATE TABLE IF NOT EXISTS order_details (
    order_detail_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    dish_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dishes(dish_id) ON DELETE CASCADE
);

-- WEBSITE SETTINGS TABLE
CREATE TABLE IF NOT EXISTS website_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    option_name VARCHAR(100) NOT NULL UNIQUE,
    option_value TEXT
);

-- ============================================
-- SEED DATA
-- ============================================

-- Default admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@foodorder.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Sample customer (password: customer123)
INSERT INTO users (name, email, password, phone, address) VALUES
('John Doe', 'john@example.com', '$2y$10$TKh8H1.PpuAqtBVHiMmzOehK7aBj8.t4kMB4UDmAQf.vT/KUhv7am', '9876543210', '123 Main Street, City');

-- Restaurants
INSERT INTO restaurants (restaurant_name, address, phone, image) VALUES
('Spice Garden', '45 MG Road, Meerut, UP', '0121-2345678', 'spice_garden.jpg'),
('The Burger Hub', '12 Civil Lines, Meerut, UP', '0121-3456789', 'burger_hub.jpg'),
('Pizza Palace', '78 Shastri Nagar, Meerut, UP', '0121-4567890', 'pizza_palace.jpg');

-- Categories
INSERT INTO categories (category_name, restaurant_id) VALUES
('Starters', 1), ('Main Course', 1), ('Desserts', 1),
('Burgers', 2), ('Sides', 2), ('Drinks', 2),
('Pizzas', 3), ('Pasta', 3), ('Garlic Bread', 3);

-- Dishes for Spice Garden
INSERT INTO dishes (dish_name, description, price, category_id, restaurant_id) VALUES
('Veg Samosa', 'Crispy pastry filled with spiced potatoes and peas', 40.00, 1, 1),
('Paneer Tikka', 'Marinated cottage cheese grilled to perfection', 180.00, 1, 1),
('Dal Makhani', 'Slow-cooked black lentils with cream and butter', 160.00, 2, 1),
('Butter Chicken', 'Tender chicken in rich tomato-butter gravy', 220.00, 2, 1),
('Biryani', 'Fragrant basmati rice with aromatic spices', 250.00, 2, 1),
('Gulab Jamun', 'Soft milk-solid balls soaked in sugar syrup', 80.00, 3, 1),

-- Dishes for The Burger Hub
('Classic Burger', 'Juicy beef patty with lettuce, tomato and special sauce', 120.00, 4, 2),
('Chicken Zinger', 'Crispy spicy chicken with coleslaw', 150.00, 4, 2),
('Veggie Burger', 'Wholesome veggie patty with fresh veggies', 100.00, 4, 2),
('French Fries', 'Golden crispy fries with seasoning', 60.00, 5, 2),
('Onion Rings', 'Beer-battered crispy onion rings', 80.00, 5, 2),
('Cola', 'Chilled carbonated cola drink', 40.00, 6, 2),

-- Dishes for Pizza Palace
('Margherita Pizza', 'Classic tomato sauce with mozzarella and basil', 200.00, 7, 3),
('Pepperoni Pizza', 'Loaded with pepperoni and cheese', 280.00, 7, 3),
('BBQ Chicken Pizza', 'Smoky BBQ sauce with grilled chicken', 300.00, 7, 3),
('Penne Arrabbiata', 'Penne pasta in spicy tomato sauce', 160.00, 8, 3),
('Spaghetti Bolognese', 'Classic spaghetti with meat sauce', 200.00, 8, 3),
('Cheese Garlic Bread', 'Toasted garlic bread loaded with cheese', 90.00, 9, 3);

-- Website settings
INSERT INTO website_settings (option_name, option_value) VALUES
('restaurant_name', 'FoodieExpress'),
('restaurant_email', 'info@foodieexpress.com'),
('restaurant_phonenumber', '+91 98765 43210'),
('restaurant_address', 'Meerut, Uttar Pradesh, India');
