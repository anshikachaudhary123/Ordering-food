# 🍽 FoodieExpress – Food Ordering System
### Full-Stack PHP + MySQL Web Application

---

## 📁 Project Structure

```
food-ordering/
├── index.php              ← Homepage (restaurant listing)
├── menu.php               ← Restaurant menu + dish listing
├── cart.php               ← Shopping cart
├── place_order.php        ← Order placement handler
├── orders.php             ← Customer order history
├── login.php              ← Login page
├── register.php           ← Registration page
├── logout.php             ← Logout handler
├── connect.php            ← Database connection + helpers
├── database.sql           ← Full DB schema + seed data
├── css/
│   └── style.css          ← Complete stylesheet (dark theme)
├── js/
│   └── main.js            ← Frontend JS (cart, toasts, filters)
├── includes/
│   └── php/
│       └── cart.php       ← Cart AJAX handler
└── admin/
    ├── index.php          ← Admin dashboard
    ├── restaurants.php    ← Manage restaurants
    ├── dishes.php         ← Manage dishes
    ├── categories.php     ← Manage categories
    ├── orders.php         ← Manage & update orders
    └── users.php          ← View/manage customers
```

---

## ⚡ Setup Instructions

### Prerequisites
- XAMPP or WAMP installed
- PHP 8.0+, MySQL 5.7+, Web browser

---

### Step 1 – Copy Project Files
Copy the entire `food-ordering/` folder into:
- **XAMPP:** `C:\xampp\htdocs\food-ordering\`
- **WAMP:** `C:\wamp64\www\food-ordering\`

---

### Step 2 – Import the Database
1. Open your browser → go to `http://localhost/phpmyadmin`
2. Click **New** (left sidebar) → Create database named `food_ordering_db`
3. Click the new database → go to **Import** tab
4. Click **Choose File** → select `database.sql` → click **Go**

---

### Step 3 – Configure Database Connection
Open `connect.php` and update if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');     // Your MySQL username
define('DB_PASS', '');         // Your MySQL password (blank for XAMPP default)
define('DB_NAME', 'food_ordering_db');
```

---

### Step 4 – Start the App
1. Start **Apache** and **MySQL** in XAMPP/WAMP
2. Open browser → `http://localhost/food-ordering/`

---

## 🔑 Demo Login Credentials

| Role     | Email                   | Password     |
|----------|-------------------------|--------------|
| Admin    | admin@foodorder.com     | admin123     |
| Customer | john@example.com        | customer123  |

---

## 🌟 Features

### Customer Side
- ✅ Register & login with secure password hashing
- ✅ Browse restaurants with live menu
- ✅ Filter dishes by category
- ✅ Add to cart (AJAX, no page reload)
- ✅ Adjust quantity / remove items
- ✅ Place order with delivery address
- ✅ View full order history with status

### Admin Panel (`/admin/`)
- ✅ Dashboard with stats (revenue, users, orders, restaurants)
- ✅ Add / Edit / Delete restaurants
- ✅ Add / Edit / Delete dishes
- ✅ Manage food categories per restaurant
- ✅ View all orders, filter by status
- ✅ Update order status (Pending → Preparing → Delivered)
- ✅ View all registered users

---

## 🛠 Tech Stack

| Layer    | Technology                         |
|----------|------------------------------------|
| Frontend | HTML5, CSS3, JavaScript (Vanilla)  |
| Backend  | PHP 8 (PDO, Sessions)              |
| Database | MySQL (Relational, Normalized)     |
| Server   | XAMPP / WAMP (Apache)              |
| Fonts    | Google Fonts (Playfair Display, DM Sans) |
| Icons    | Font Awesome 6                     |

---

## 🗄 Database Tables

| Table          | Purpose                        |
|----------------|--------------------------------|
| users          | Customer & admin accounts      |
| restaurants    | Restaurant listings            |
| categories     | Food categories per restaurant |
| dishes         | Menu items                     |
| orders         | Customer orders                |
| order_details  | Items within each order        |
| website_settings | Site name, email, phone      |

---

## 🔒 Security Features
- Passwords hashed with `password_hash()` (bcrypt)
- PDO prepared statements (SQL injection prevention)
- Session-based authentication
- Input sanitization via `htmlspecialchars()`
- Role-based access control (admin vs customer)

---

## 📱 Responsive Design
The UI is fully responsive and works on:
- Desktop (1200px+)
- Tablet (768px–1199px)
- Mobile (< 768px)
