<?php
require_once 'connect.php';

// Fetch restaurants
$stmt = $pdo->query("SELECT * FROM restaurants WHERE status = 1");
$restaurants = $stmt->fetchAll();

// Fetch website settings
$stmt2 = $pdo->query("SELECT option_name, option_value FROM website_settings");
$settings = [];
foreach ($stmt2->fetchAll() as $row) {
    $settings[$row['option_name']] = $row['option_value'];
}

$cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['restaurant_name'] ?? 'FoodieExpress') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo">
            <span class="logo-icon">🍽</span>
            <span class="logo-text"><?= htmlspecialchars($settings['restaurant_name'] ?? 'FoodieExpress') ?></span>
        </a>
        <div class="nav-links">
            <a href="#restaurants">Restaurants</a>
            <a href="#how-it-works">How It Works</a>
            <a href="cart.php" class="cart-btn">
                <i class="fas fa-shopping-bag"></i>
                <?php if($cart_count > 0): ?>
                    <span class="cart-badge"><?= $cart_count ?></span>
                <?php endif; ?>
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
        <a href="#restaurants">Restaurants</a>
        <a href="cart.php">Cart (<?= $cart_count ?>)</a>
        <?php if(isLoggedIn()): ?>
            <a href="orders.php">My Orders</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Sign In</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>

<!-- HERO SECTION -->
<section class="hero">
    <div class="hero-bg">
        <div class="hero-blob blob-1"></div>
        <div class="hero-blob blob-2"></div>
        <div class="hero-grid"></div>
    </div>
    <div class="hero-content">
        <div class="hero-badge">🔥 Fast Delivery · Fresh Food · Great Taste</div>
        <h1 class="hero-title">
            Crave It.<br>
            <span class="hero-accent">Order It.</span><br>
            Love It.
        </h1>
        <p class="hero-subtitle">Discover the best restaurants in Meerut and get your favourite meals delivered right to your door.</p>
        <div class="hero-actions">
            <a href="#restaurants" class="btn-primary">
                <i class="fas fa-utensils"></i> Browse Restaurants
            </a>
            <?php if(!isLoggedIn()): ?>
            <a href="register.php" class="btn-ghost">Create Free Account →</a>
            <?php endif; ?>
        </div>
        <div class="hero-stats">
            <div class="stat"><span class="stat-num">50+</span><span class="stat-label">Dishes</span></div>
            <div class="stat-divider"></div>
            <div class="stat"><span class="stat-num">3</span><span class="stat-label">Restaurants</span></div>
            <div class="stat-divider"></div>
            <div class="stat"><span class="stat-num">30min</span><span class="stat-label">Avg Delivery</span></div>
        </div>
    </div>
    <div class="hero-visual">
        <div class="food-card-float card-1">
            <div class="food-emoji">🍕</div>
            <div class="food-info"><strong>Margherita Pizza</strong><span>₹200</span></div>
        </div>
        <div class="food-card-float card-2">
            <div class="food-emoji">🍔</div>
            <div class="food-info"><strong>Classic Burger</strong><span>₹120</span></div>
        </div>
        <div class="food-card-float card-3">
            <div class="food-emoji">🍛</div>
            <div class="food-info"><strong>Butter Chicken</strong><span>₹220</span></div>
        </div>
        <div class="hero-plate">🍽️</div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="how-it-works" id="how-it-works">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Simple Steps</span>
            <h2>How It <span class="accent">Works</span></h2>
        </div>
        <div class="steps-grid">
            <div class="step">
                <div class="step-icon">🏪</div>
                <div class="step-num">01</div>
                <h3>Choose Restaurant</h3>
                <p>Browse our curated list of top restaurants in your area</p>
            </div>
            <div class="step-arrow">→</div>
            <div class="step">
                <div class="step-icon">🛒</div>
                <div class="step-num">02</div>
                <h3>Select Your Food</h3>
                <p>Pick your favourite dishes and add them to your cart</p>
            </div>
            <div class="step-arrow">→</div>
            <div class="step">
                <div class="step-icon">🚀</div>
                <div class="step-num">03</div>
                <h3>Fast Delivery</h3>
                <p>Sit back while we bring hot food straight to your door</p>
            </div>
        </div>
    </div>
</section>

<!-- RESTAURANTS SECTION -->
<section class="restaurants-section" id="restaurants">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Eat Local</span>
            <h2>Top <span class="accent">Restaurants</span></h2>
            <p>Hand-picked restaurants serving the best food in Meerut</p>
        </div>
        <div class="restaurants-grid">
            <?php foreach($restaurants as $r): ?>
            <a href="menu.php?id=<?= $r['restaurant_id'] ?>" class="restaurant-card">
                <div class="restaurant-img">
                    <div class="restaurant-emoji">
                        <?php
                        $emojis = ['🍲','🍔','🍕'];
                        $idx = ($r['restaurant_id'] - 1) % 3;
                        echo $emojis[$idx];
                        ?>
                    </div>
                    <div class="restaurant-overlay">
                        <span>View Menu →</span>
                    </div>
                </div>
                <div class="restaurant-info">
                    <h3><?= htmlspecialchars($r['restaurant_name']) ?></h3>
                    <p class="restaurant-address"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($r['address']) ?></p>
                    <p class="restaurant-phone"><i class="fas fa-phone"></i> <?= htmlspecialchars($r['phone']) ?></p>
                    <div class="restaurant-tags">
                        <span class="tag">Free Delivery</span>
                        <span class="tag">30 mins</span>
                        <span class="tag">⭐ 4.5</span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="footer-logo">🍽 <?= htmlspecialchars($settings['restaurant_name'] ?? 'FoodieExpress') ?></div>
                <p>Your favourite food, delivered fresh and fast.</p>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <a href="index.php">Home</a>
                <a href="#restaurants">Restaurants</a>
                <a href="cart.php">Cart</a>
                <a href="orders.php">My Orders</a>
            </div>
            <div class="footer-col">
                <h4>Account</h4>
                <a href="login.php">Sign In</a>
                <a href="register.php">Register</a>
            </div>
            <div class="footer-col">
                <h4>Contact</h4>
                <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($settings['restaurant_email'] ?? '') ?></p>
                <p><i class="fas fa-phone"></i> <?= htmlspecialchars($settings['restaurant_phonenumber'] ?? '') ?></p>
                <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($settings['restaurant_address'] ?? '') ?></p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2025 <?= htmlspecialchars($settings['restaurant_name'] ?? 'FoodieExpress') ?>. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="js/main.js"></script>
</body>
</html>
