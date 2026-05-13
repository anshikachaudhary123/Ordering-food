<?php
require_once 'connect.php';

if (!isLoggedIn()) redirect('login.php');

$user_id    = $_SESSION['user_id'];
$cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
$success    = isset($_GET['success']) && isset($_GET['order_id']);
$order_id   = intval($_GET['order_id'] ?? 0);

// Get all orders for this user
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Get order details for each order
$order_details = [];
if (!empty($orders)) {
    $ids = array_column($orders, 'order_id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt2 = $pdo->prepare("SELECT od.*, d.dish_name FROM order_details od JOIN dishes d ON od.dish_id = d.dish_id WHERE od.order_id IN ($placeholders)");
    $stmt2->execute($ids);
    foreach ($stmt2->fetchAll() as $row) {
        $order_details[$row['order_id']][] = $row;
    }
}

function statusClass($status) {
    return match(strtolower($status)) {
        'pending'   => 'status-pending',
        'preparing' => 'status-preparing',
        'delivered' => 'status-delivered',
        'cancelled' => 'status-cancelled',
        default     => 'status-pending',
    };
}

function statusIcon($status) {
    return match(strtolower($status)) {
        'pending'   => '🕐',
        'preparing' => '👨‍🍳',
        'delivered' => '✅',
        'cancelled' => '❌',
        default     => '📦',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders – FoodieExpress</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar scrolled" id="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo"><span class="logo-icon">🍽</span><span class="logo-text">FoodieExpress</span></a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="cart.php" class="cart-btn">
                <i class="fas fa-shopping-bag"></i>
                <?php if($cart_count > 0): ?><span class="cart-badge"><?= $cart_count ?></span><?php endif; ?>
            </a>
            <a href="orders.php" class="nav-cta">My Orders</a>
            <a href="logout.php" class="nav-logout"><i class="fas fa-sign-out-alt"></i></a>
        </div>
        <button class="nav-toggle" onclick="toggleMenu()"><i class="fas fa-bars"></i></button>
    </div>
    <div class="mobile-menu" id="mobileMenu">
        <a href="index.php">Home</a>
        <a href="cart.php">Cart</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<section class="orders-section">
    <div class="container">
        <div class="page-header" style="padding-top:0; text-align:left; margin-bottom:32px;">
            <span class="section-tag">History</span>
            <h1>My Orders</h1>
            <p>Hello, <strong style="color:var(--accent)"><?= htmlspecialchars($_SESSION['user_name']) ?></strong> — here are all your orders</p>
        </div>

        <?php if($success && $order_id): ?>
        <div class="alert alert-success" style="margin-bottom:24px;">
            🎉 <strong>Order #<?= $order_id ?> placed successfully!</strong> We're preparing your food. Estimated delivery: 30 minutes.
        </div>
        <?php endif; ?>

        <?php if(empty($orders)): ?>
        <div class="empty-state">
            <div class="empty-emoji">📋</div>
            <h3>No orders yet</h3>
            <p>You haven't placed any orders. Start ordering now!</p>
            <a href="index.php" class="btn-primary" style="display:inline-flex; margin-top:16px;">Browse Restaurants</a>
        </div>
        <?php else: ?>
        <div class="orders-list">
            <?php foreach($orders as $order): ?>
            <div class="order-card">
                <div class="order-card-header">
                    <div>
                        <div class="order-id">Order #<?= $order['order_id'] ?></div>
                        <div class="order-date"><i class="fas fa-clock"></i> <?= date('d M Y, h:i A', strtotime($order['order_date'])) ?></div>
                    </div>
                    <span class="status-badge <?= statusClass($order['status']) ?>">
                        <?= statusIcon($order['status']) ?> <?= htmlspecialchars($order['status']) ?>
                    </span>
                    <div class="order-amount">₹<?= number_format($order['total_amount'], 2) ?></div>
                </div>
                <div class="order-card-body">
                    <div style="margin-bottom:12px; font-size:0.8rem; color:var(--text-muted);">
                        <i class="fas fa-map-marker-alt" style="color:var(--accent)"></i>
                        <?= htmlspecialchars($order['delivery_address']) ?>
                    </div>
                    <?php if(!empty($order_details[$order['order_id']])): ?>
                        <?php foreach($order_details[$order['order_id']] as $detail): ?>
                        <div class="order-item-line">
                            <span><strong><?= htmlspecialchars($detail['dish_name']) ?></strong> × <?= $detail['quantity'] ?></span>
                            <span>₹<?= number_format($detail['price'] * $detail['quantity'], 2) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<script src="js/main.js"></script>
</body>
</html>
