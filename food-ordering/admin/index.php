<?php
require_once '../connect.php';
if (!isAdmin()) redirect('../login.php');

// Stats
$stats = [];
$stats['users']       = $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$stats['restaurants'] = $pdo->query("SELECT COUNT(*) FROM restaurants")->fetchColumn();
$stats['orders']      = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$stats['revenue']     = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status != 'Cancelled'")->fetchColumn();

// Recent orders
$recent_orders = $pdo->query("
    SELECT o.*, u.name as customer_name
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
    LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – FoodieExpress</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<!-- SIDEBAR -->
<div class="admin-sidebar">
    <div class="admin-logo">🍽 Admin Panel</div>
    <nav class="admin-nav">
        <a href="index.php" class="admin-nav-item active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="restaurants.php" class="admin-nav-item"><i class="fas fa-store"></i> Restaurants</a>
        <a href="dishes.php" class="admin-nav-item"><i class="fas fa-utensils"></i> Dishes</a>
        <a href="categories.php" class="admin-nav-item"><i class="fas fa-tags"></i> Categories</a>
        <a href="orders.php" class="admin-nav-item"><i class="fas fa-receipt"></i> Orders</a>
        <a href="users.php" class="admin-nav-item"><i class="fas fa-users"></i> Users</a>
        <div style="margin-top:auto; padding:20px 12px 0;">
            <a href="../index.php" class="admin-nav-item"><i class="fas fa-globe"></i> View Site</a>
            <a href="../logout.php" class="admin-nav-item" style="color:#ff8a8a;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>
</div>

<!-- MAIN -->
<div class="admin-main">
    <div class="admin-page-title">Dashboard 👋</div>

    <!-- STATS CARDS -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-card-icon">👥</div>
            <div class="stat-card-value"><?= number_format($stats['users']) ?></div>
            <div class="stat-card-label">Total Customers</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon">🏪</div>
            <div class="stat-card-value"><?= number_format($stats['restaurants']) ?></div>
            <div class="stat-card-label">Restaurants</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon">📦</div>
            <div class="stat-card-value"><?= number_format($stats['orders']) ?></div>
            <div class="stat-card-label">Total Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon">💰</div>
            <div class="stat-card-value">₹<?= number_format($stats['revenue']) ?></div>
            <div class="stat-card-label">Total Revenue</div>
        </div>
    </div>

    <!-- RECENT ORDERS -->
    <div class="data-table">
        <div class="table-header">
            <h3>Recent Orders</h3>
            <a href="orders.php" class="btn-secondary"><i class="fas fa-list"></i> View All</a>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recent_orders as $order): ?>
                    <tr>
                        <td><strong>#<?= $order['order_id'] ?></strong></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td style="color:var(--accent); font-weight:600;">₹<?= number_format($order['total_amount'], 2) ?></td>
                        <td>
                            <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                <?= htmlspecialchars($order['status']) ?>
                            </span>
                        </td>
                        <td><?= date('d M Y', strtotime($order['order_date'])) ?></td>
                        <td>
                            <a href="orders.php?update_id=<?= $order['order_id'] ?>" class="btn-edit">Update</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($recent_orders)): ?>
                    <tr><td colspan="6" style="text-align:center; color:var(--text-muted); padding:40px;">No orders yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="../js/main.js"></script>
</body>
</html>
