<?php
require_once 'connect.php';

$cart       = $_SESSION['cart'] ?? [];
$cart_count = array_sum(array_column($cart, 'quantity'));
$subtotal   = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$delivery = $subtotal > 0 ? 40 : 0;
$total    = $subtotal + $delivery;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart – FoodieExpress</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar scrolled" id="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo"><span class="logo-icon">🍽</span><span class="logo-text">FoodieExpress</span></a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="cart.php" class="cart-btn">
                <i class="fas fa-shopping-bag"></i>
                <?php if($cart_count > 0): ?><span class="cart-badge"><?= $cart_count ?></span><?php endif; ?>
            </a>
            <?php if(isLoggedIn()): ?>
                <a href="orders.php" class="nav-cta">My Orders</a>
                <a href="logout.php" class="nav-logout"><i class="fas fa-sign-out-alt"></i></a>
            <?php else: ?>
                <a href="login.php" class="nav-cta">Sign In</a>
            <?php endif; ?>
        </div>
        <button class="nav-toggle" onclick="toggleMenu()"><i class="fas fa-bars"></i></button>
    </div>
    <div class="mobile-menu" id="mobileMenu">
        <a href="index.php">Home</a>
        <?php if(isLoggedIn()): ?>
            <a href="orders.php">My Orders</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Sign In</a>
        <?php endif; ?>
    </div>
</nav>

<section class="cart-section">
    <div class="container">
        <div class="page-header" style="padding-top:0; text-align:left; margin-bottom:32px">
            <span class="section-tag">Review</span>
            <h1>Your Cart</h1>
            <?php if($cart_count > 0): ?>
                <p><?= $cart_count ?> item<?= $cart_count > 1 ? 's' : '' ?> in your cart</p>
            <?php endif; ?>
        </div>

        <?php if(empty($cart)): ?>
            <div class="empty-state">
                <div class="empty-emoji">🛒</div>
                <h3>Your cart is empty</h3>
                <p>Looks like you haven't added anything yet.</p>
                <a href="index.php" class="btn-primary">Browse Restaurants</a>
            </div>
        <?php else: ?>
        <div class="cart-layout">

            <!-- CART ITEMS -->
            <div class="cart-items">
                <?php foreach($cart as $dish_id => $item): ?>
                <div class="cart-item">
                    <div class="cart-item-emoji">🍽️</div>
                    <div class="cart-item-info">
                        <h3><?= htmlspecialchars($item['dish_name']) ?></h3>
                        <p>₹<?= number_format($item['price'], 2) ?> each</p>
                    </div>
                    <div class="qty-control">
                        <button class="qty-btn" onclick="updateQty(<?= $dish_id ?>, -1)">−</button>
                        <span class="qty-num"><?= $item['quantity'] ?></span>
                        <button class="qty-btn" onclick="updateQty(<?= $dish_id ?>, 1)">+</button>
                    </div>
                    <div class="cart-item-price">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                    <button class="btn-danger" onclick="removeFromCart(<?= $dish_id ?>)"><i class="fas fa-trash"></i></button>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- ORDER SUMMARY -->
            <div class="order-summary">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>₹<?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Delivery Fee</span>
                    <span>₹<?= number_format($delivery, 2) ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span class="total-amount">₹<?= number_format($total, 2) ?></span>
                </div>

                <?php if(!isLoggedIn()): ?>
                    <a href="login.php" class="checkout-btn" style="display:block; text-align:center;">
                        <i class="fas fa-sign-in-alt"></i> Sign In to Checkout
                    </a>
                <?php else: ?>
                    <button class="checkout-btn" onclick="openModal('checkoutModal')">
                        <i class="fas fa-lock"></i> Proceed to Checkout
                    </button>
                <?php endif; ?>

                <a href="index.php" style="display:block; text-align:center; margin-top:14px; color:var(--text-muted); font-size:0.85rem;">
                    ← Continue Shopping
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- CHECKOUT MODAL -->
<?php if(isLoggedIn() && !empty($cart)): ?>
<div class="modal-overlay" id="checkoutModal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('checkoutModal')"><i class="fas fa-times"></i></button>
        <h3>Confirm Your Order</h3>

        <form action="place_order.php" method="POST">
            <div class="form-group">
                <label>Delivery Address</label>
                <textarea name="delivery_address" placeholder="Enter your full delivery address..." required rows="3"></textarea>
            </div>

            <div style="background:var(--bg-elevated); border-radius:10px; padding:16px; margin-bottom:20px;">
                <div class="summary-row" style="margin-bottom:8px;">
                    <span style="font-size:0.85rem; color:var(--text-muted)">Items (<?= $cart_count ?>)</span>
                    <span style="font-size:0.85rem;">₹<?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="summary-row" style="margin-bottom:8px;">
                    <span style="font-size:0.85rem; color:var(--text-muted)">Delivery</span>
                    <span style="font-size:0.85rem;">₹<?= number_format($delivery, 2) ?></span>
                </div>
                <div class="summary-row total" style="border-top:1px solid var(--border); padding-top:12px; margin-top:4px;">
                    <span>Total</span>
                    <span class="total-amount">₹<?= number_format($total, 2) ?></span>
                </div>
            </div>

            <input type="hidden" name="total" value="<?= $total ?>">
            <button type="submit" class="checkout-btn">
                <i class="fas fa-check-circle"></i> Place Order
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<script src="js/main.js"></script>
</body>
</html>
