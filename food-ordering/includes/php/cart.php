<?php
// ============================================
// CART HANDLER (AJAX)
// ============================================
require_once '../../connect.php';
header('Content-Type: application/json');

$action   = $_POST['action'] ?? '';
$dish_id  = intval($_POST['dish_id'] ?? 0);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

switch ($action) {

    case 'add':
        $dish_name = sanitize($_POST['dish_name'] ?? '');
        $price     = floatval($_POST['price'] ?? 0);

        if ($dish_id <= 0 || $price <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid item']);
            exit;
        }

        if (isset($_SESSION['cart'][$dish_id])) {
            $_SESSION['cart'][$dish_id]['quantity']++;
        } else {
            $_SESSION['cart'][$dish_id] = [
                'dish_id'   => $dish_id,
                'dish_name' => $dish_name,
                'price'     => $price,
                'quantity'  => 1,
            ];
        }

        $cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));
        echo json_encode(['success' => true, 'cart_count' => $cart_count]);
        break;

    case 'update':
        $delta = intval($_POST['delta'] ?? 0);

        if (isset($_SESSION['cart'][$dish_id])) {
            $_SESSION['cart'][$dish_id]['quantity'] += $delta;
            if ($_SESSION['cart'][$dish_id]['quantity'] <= 0) {
                unset($_SESSION['cart'][$dish_id]);
            }
        }

        $cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));
        echo json_encode(['success' => true, 'cart_count' => $cart_count]);
        break;

    case 'remove':
        if (isset($_SESSION['cart'][$dish_id])) {
            unset($_SESSION['cart'][$dish_id]);
        }
        $cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));
        echo json_encode(['success' => true, 'cart_count' => $cart_count]);
        break;

    case 'clear':
        $_SESSION['cart'] = [];
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
