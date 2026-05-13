<?php
require_once '../connect.php';
if (!isAdmin()) redirect('../login.php');

$msg = '';

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id  = intval($_POST['order_id']);
    $status    = sanitize($_POST['status'] ?? '');
    $allowed   = ['Pending', 'Preparing', 'Delivered', 'Cancelled'];
    if (in_array($status, $allowed)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->execute([$status, $order_id]);
        $msg = 'success:Order #' . $order_id . ' updated to ' . $status;
    }
    redirect('orders.php?msg=' . urlencode($msg));
}

$msg = sanitize($_GET['msg'] ?? '');

// Filter
$filter = sanitize($_GET['status'] ?? 'all');
$where  = $filter !== 'all' ? "WHERE o.status = '$filter'" : '';

$orders = $pdo->query("
    SELECT o.*, u.name as customer_name, u.email as customer_email
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    $where
    ORDER BY o.order_date DESC
")->fetchAll();

// Get order details for each
$order_details = [];
if (!empty($orders)) {
    $ids = array_column($orders, 'order_id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT od.*, d.dish_name FROM order_details od JOIN dishes d ON od.dish_id = d.dish_id WHERE od.order_id IN ($placeholders)");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $row) {
        $order_details[$row['order_id']][] = $row;
    }
}

// Counts
$counts = $pdo->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders – Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="admin-sidebar">
    <div class="admin-logo">🍽 Admin Panel</div>
    <nav class="admin-nav">
        <a href="index.php" class="admin-nav-item"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="restaurants.php" class="admin-nav-item"><i class="fas fa-store"></i> Restaurants</a>
        <a href="dishes.php" class="admin-nav-item"><i class="fas fa-utensils"></i> Dishes</a>
        <a href="categories.php" class="admin-nav-item"><i class="fas fa-tags"></i> Categories</a>
        <a href="orders.php" class="admin-nav-item active"><i class="fas fa-receipt"></i> Orders</a>
        <a href="users.php" class="admin-nav-item"><i class="fas fa-users"></i> Users</a>
        <a href="../index.php" class="admin-nav-item" style="margin-top:20px;"><i class="fas fa-globe"></i> View Site</a>
        <a href="../logout.php" class="admin-nav-item" style="color:#ff8a8a;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
</div>

<div class="admin-main">
    <div class="admin-page-title">Orders 📦</div>

    <?php if($msg): ?>
        <?php [$type, $text] = explode(':', $msg, 2); ?>
        <div class="alert alert-<?= $type ?>" style="margin-bottom:20px;"><?= htmlspecialchars($text) ?></div>
    <?php endif; ?>

    <!-- Filter tabs -->
    <div style="display:flex; gap:8px; margin-bottom:24px; flex-wrap:wrap;">
        <?php
        $filters = ['all' => 'All', 'Pending' => 'Pending', 'Preparing' => 'Preparing', 'Delivered' => 'Delivered', 'Cancelled' => 'Cancelled'];
        foreach($filters as $val => $label):
        ?>
        <a href="?status=<?= $val ?>" class="cat-btn <?= $filter === $val ? 'active' : '' ?>" style="display:inline-block;">
            <?= $label ?>
            <?php if($val !== 'all' && isset($counts[$val])): ?>
                <span style="background:var(--bg-card); border-radius:100px; padding:1px 7px; font-size:0.75rem; margin-left:4px;"><?= $counts[$val] ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="orders-list">
        <?php foreach($orders as $order): ?>
        <div class="order-card">
            <div class="order-card-header">
                <div>
                    <div class="order-id">Order #<?= $order['order_id'] ?></div>
                    <div class="order-date">
                        👤 <?= htmlspecialchars($order['customer_name']) ?> (<?= htmlspecialchars($order['customer_email']) ?>)
                        &nbsp;·&nbsp; <?= date('d M Y, h:i A', strtotime($order['order_date'])) ?>
                    </div>
                </div>
                <span class="status-badge status-<?= strtolower($order['status']) ?>">
                    <?= htmlspecialchars($order['status']) ?>
                </span>
                <div class="order-amount">₹<?= number_format($order['total_amount'], 2) ?></div>
            </div>
            <div class="order-card-body">
                <div style="margin-bottom:12px; font-size:0.82rem; color:var(--text-muted);">
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

                <!-- Update Status -->
                <form method="POST" style="margin-top:16px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                    <select name="status" style="background:var(--bg-elevated); border:1px solid var(--border); color:var(--text-primary); padding:8px 14px; border-radius:8px; font-size:0.88rem; font-family:inherit;">
                        <?php foreach(['Pending','Preparing','Delivered','Cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-secondary" style="padding:8px 18px;">
                        <i class="fas fa-sync"></i> Update Status
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if(empty($orders)): ?>
        <div class="empty-state">
            <div class="empty-emoji">📋</div>
            <h3>No orders found</h3>
            <p>No orders match the selected filter.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="../js/main.js"></script>
</body>
</html>
