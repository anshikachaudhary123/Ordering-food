<?php
require_once '../connect.php';
if (!isAdmin()) redirect('../login.php');

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name          = sanitize($_POST['name'] ?? '');
        $restaurant_id = intval($_POST['restaurant_id'] ?? 0);
        if ($name && $restaurant_id) {
            $stmt = $pdo->prepare("INSERT INTO categories (category_name, restaurant_id) VALUES (?, ?)");
            $stmt->execute([$name, $restaurant_id]);
            $msg = 'success:Category added!';
        }
    }

    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $pdo->prepare("DELETE FROM categories WHERE category_id = ?")->execute([$id]);
        $msg = 'success:Category deleted.';
    }

    redirect('categories.php?msg=' . urlencode($msg));
}

$msg         = sanitize($_GET['msg'] ?? '');
$categories  = $pdo->query("SELECT ca.*, r.restaurant_name FROM categories ca LEFT JOIN restaurants r ON ca.restaurant_id = r.restaurant_id ORDER BY r.restaurant_name, ca.category_name")->fetchAll();
$restaurants = $pdo->query("SELECT * FROM restaurants WHERE status=1 ORDER BY restaurant_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories – Admin</title>
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
        <a href="categories.php" class="admin-nav-item active"><i class="fas fa-tags"></i> Categories</a>
        <a href="orders.php" class="admin-nav-item"><i class="fas fa-receipt"></i> Orders</a>
        <a href="users.php" class="admin-nav-item"><i class="fas fa-users"></i> Users</a>
        <a href="../index.php" class="admin-nav-item" style="margin-top:20px;"><i class="fas fa-globe"></i> View Site</a>
        <a href="../logout.php" class="admin-nav-item" style="color:#ff8a8a;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
</div>

<div class="admin-main">
    <div class="admin-page-title">Categories 🏷️</div>

    <?php if($msg): ?>
        <?php [$type, $text] = explode(':', $msg, 2); ?>
        <div class="alert alert-<?= $type ?>" style="margin-bottom:20px;"><?= htmlspecialchars($text) ?></div>
    <?php endif; ?>

    <div class="data-table">
        <div class="table-header">
            <h3>All Categories (<?= count($categories) ?>)</h3>
            <button class="btn-primary" onclick="openModal('addModal')"><i class="fas fa-plus"></i> Add Category</button>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr><th>ID</th><th>Category Name</th><th>Restaurant</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($categories as $c): ?>
                    <tr>
                        <td>#<?= $c['category_id'] ?></td>
                        <td><strong><?= htmlspecialchars($c['category_name']) ?></strong></td>
                        <td><?= htmlspecialchars($c['restaurant_name'] ?? '–') ?></td>
                        <td>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this category?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $c['category_id'] ?>">
                                <button type="submit" class="btn-del">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($categories)): ?>
                    <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:40px;">No categories yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="addModal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('addModal')"><i class="fas fa-times"></i></button>
        <h3>Add Category</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Restaurant *</label>
                <select name="restaurant_id" required>
                    <option value="">Select Restaurant</option>
                    <?php foreach($restaurants as $r): ?>
                    <option value="<?= $r['restaurant_id'] ?>"><?= htmlspecialchars($r['restaurant_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Category Name *</label>
                <input type="text" name="name" required placeholder="e.g. Starters, Main Course">
            </div>
            <button type="submit" class="form-submit"><i class="fas fa-plus"></i> Add Category</button>
        </form>
    </div>
</div>

<script src="../js/main.js"></script>
</body>
</html>
