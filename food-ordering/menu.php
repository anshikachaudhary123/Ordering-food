<?php
require_once 'connect.php';

$restaurant_id = intval($_GET['id'] ?? 0);
if (!$restaurant_id) redirect('index.php');

// Get restaurant
$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE restaurant_id = ? AND status = 1");
$stmt->execute([$restaurant_id]);
$restaurant = $stmt->fetch();
if (!$restaurant) redirect('index.php');

// Get categories for this restaurant
$stmt = $pdo->prepare("SELECT * FROM categories WHERE restaurant_id = ?");
$stmt->execute([$restaurant_id]);
$categories = $stmt->fetchAll();

// Get all dishes for this restaurant
$stmt = $pdo->prepare("SELECT d.*, c.category_name FROM dishes d LEFT JOIN categories c ON d.category_id = c.category_id WHERE d.restaurant_id = ? AND d.status = 1 ORDER BY d.category_id");
$stmt->execute([$restaurant_id]);
$dishes = $stmt->fetchAll();

$cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;

// Emoji mapping by dish name keywords
function getDishEmoji($name) {
    $name = strtolower($name);
    if (str_contains($name, 'pizza')) return '🍕';
    if (str_contains($name, 'burger')) return '🍔';
    if (str_contains($name, 'chicken')) return '🍗';
    if (str_contains($name, 'biryani')) return '🍛';
    if (str_contains($name, 'dal') || str_contains($name, 'curry')) return '🫕';
    if (str_contains($name, 'pasta') || str_contains($name, 'spaghetti') || str_contains($name, 'penne')) return '🍝';
    if (str_contains($name, 'samosa')) return '🥟';
    if (str_contains($name, 'paneer') || str_contains($name, 'tikka')) return '🧆';
    if (str_contains($name, 'gulab') || str_contains($name, 'dessert') || str_contains($name, 'sweet')) return '🍮';
    if (str_contains($name, 'fries') || str_contains($name, 'chips')) return '🍟';
    if (str_contains($name, 'onion')) return '🧅';
    if (str_contains($name, 'cola') || str_contains($name, 'drink')) return '🥤';
    if (str_contains($name, 'bread') || str_contains($name, 'garlic')) return '🧄';
    if (str_contains($name, 'salad')) return '🥗';
    if (str_contains($name, 'soup')) return '🍜';
    return '🍽️';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($restaurant['restaurant_name']) ?> – FoodieExpress</title>
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
            <a href="index.php#restaurants"><i class="fas fa-arrow-left"></i> Back</a>
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
        <a href="cart.php">Cart (<?= $cart_count ?>)</a>
        <?php if(isLoggedIn()): ?>
            <a href="orders.php">My Orders</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Sign In</a>
        <?php endif; ?>
    </div>
</nav>

<!-- PAGE HEADER -->
<div class="page-header">
    <span class="section-tag">Now Ordering From</span>
    <h1><?= htmlspecialchars($restaurant['restaurant_name']) ?></h1>
    <p>
        <i class="fas fa-map-marker-alt" style="color:var(--accent)"></i>
        <?= htmlspecialchars($restaurant['address']) ?>
        &nbsp;·&nbsp;
        <i class="fas fa-phone" style="color:var(--accent)"></i>
        <?= htmlspecialchars($restaurant['phone']) ?>
    </p>
</div>

<!-- MENU LAYOUT -->
<div class="container">
    <div class="menu-layout">

        <!-- SIDEBAR: Categories -->
        <aside class="categories-sidebar">
            <h3>Categories</h3>
            <button class="cat-btn active" onclick="filterCategory('all', this)">
                🍽 All Items
            </button>
            <?php foreach($categories as $cat): ?>
            <button class="cat-btn" onclick="filterCategory(<?= $cat['category_id'] ?>, this)">
                <?= htmlspecialchars($cat['category_name']) ?>
            </button>
            <?php endforeach; ?>
        </aside>

        <!-- DISHES GRID -->
        <div class="dishes-grid" id="dishesGrid">
            <?php if(empty($dishes)): ?>
                <div class="empty-state" style="grid-column:1/-1">
                    <div class="empty-emoji">🍽️</div>
                    <h3>No dishes available</h3>
                    <p>This restaurant hasn't added any dishes yet.</p>
                </div>
            <?php else: ?>
                <?php foreach($dishes as $dish): ?>
                <div class="dish-card" data-category="<?= $dish['category_id'] ?>">
                    <div class="dish-img"><?= getDishEmoji($dish['dish_name']) ?></div>
                    <div class="dish-body">
                        <h3><?= htmlspecialchars($dish['dish_name']) ?></h3>
                        <?php if($dish['description']): ?>
                            <p class="dish-desc"><?= htmlspecialchars($dish['description']) ?></p>
                        <?php endif; ?>
                        <div class="dish-footer">
                            <span class="dish-price">₹<?= number_format($dish['price'], 2) ?></span>
                            <button class="add-btn" onclick="addToCart(<?= $dish['dish_id'] ?>, '<?= addslashes(htmlspecialchars($dish['dish_name'])) ?>', <?= $dish['price'] ?>)">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</div>

<script src="js/main.js"></script>
</body>
</html>
