<?php
require_once 'connect.php';

if (!isLoggedIn()) redirect('login.php');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('cart.php');
if (empty($_SESSION['cart'])) redirect('cart.php');

$user_id          = $_SESSION['user_id'];
$total            = floatval($_POST['total'] ?? 0);
$delivery_address = sanitize($_POST['delivery_address'] ?? '');

if ($total <= 0 || empty($delivery_address)) {
    redirect('cart.php');
}

try {
    $pdo->beginTransaction();

    // Insert order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, delivery_address, status) VALUES (?, ?, ?, 'Pending')");
    $stmt->execute([$user_id, $total, $delivery_address]);
    $order_id = $pdo->lastInsertId();

    // Insert order details
    $stmt2 = $pdo->prepare("INSERT INTO order_details (order_id, dish_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($_SESSION['cart'] as $dish_id => $item) {
        $stmt2->execute([$order_id, $dish_id, $item['quantity'], $item['price']]);
    }

    $pdo->commit();

    // Clear cart
    $_SESSION['cart'] = [];
    $_SESSION['order_success'] = $order_id;

    redirect('orders.php?success=1&order_id=' . $order_id);

} catch (Exception $e) {
    $pdo->rollBack();
    redirect('cart.php?error=1');
}
