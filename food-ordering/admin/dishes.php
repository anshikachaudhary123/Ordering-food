<?php
require_once '../connect.php';
if (!isAdmin()) redirect('../login.php');

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name        = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $price       = floatval($_POST['price'] ?? 0);
        $category_id = intval($_POST['category_id'] ?? 0);
        $restaurant_id = intval($_POST['restaurant_id'] ?? 0);
        if ($name && $price > 0 && $restaurant_id) {
            $stmt = $pdo->prepare("INSERT INTO dishes (dish_name, description, price, category_id, restaurant_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $category_id ?: null, $restaurant_id]);
            $msg = 'success:Dish added successfully!';
        } else {
            $msg = 'error:Please fill in all required fields.';
        }
    }

    if ($action === 'edit') {
        $id          = intval($_POST['id']);
        $name        = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $price       = floatval($_POST['price'] ?? 0);
        $category_id = intval($_POST['category_id'] ?? 0);
        $restaurant_id = intval($_POST['restaurant_id'] ?? 0);
        $status      = intval($_POST['status'] ?? 1);
        $stmt = $pdo->prepare("UPDATE dishes SET dish_name=?, description=?, price=?, category_id=?, restaurant_id=?, status=? WHERE dish_id=?");
        $stmt->execute([$name, $description, $price, $category_id ?: null, $restaurant_id, $status, $id]);
        $msg = 'success:Dish updated successfully!';
    }

    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM dishes WHERE dish_id = ?");
        $stmt->execute([$id]);
        $msg = 'success:Dish deleted.';
    }

    redirect('dishes.php?msg=' . urlencode($msg));
}

$msg = sanitize($_GET['msg'] ?? '');

$dishes = $pdo->query("
    SELECT d.*, c.category_name, r.restaurant_name
    FROM dishes d
    LEFT JOIN categories c ON d.category_id = c.category_id
    LEFT JOIN restaurants r ON d.restaurant_id = r.restaurant_id
    ORDER BY d.restaurant_id, d.dish_id DESC
")->fetchAll();

$restaurants = $pdo->query("SELECT * FROM restaurants WHERE status=1 ORDER BY restaurant_name")->fetchAll();
$categories  = $pdo->query("SELECT ca.*, r.restaurant_name FROM categories ca JOIN restaurants r ON ca.restaurant_id = r.restaurant_id ORDER BY r.restaurant_name, ca.category_name")->fetchAll();

$edit_id   = intval($_GET['edit'] ?? 0);
$edit_dish = null;
if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM dishes WHERE dish_id = ?");
    $stmt->execute([$edit_id]);
    $edit_dish = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dishes – Admin</title>
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
        <a href="dishes.php" class="admin-nav-item active"><i class="fas fa-utensils"></i> Dishes</a>
        <a href="categories.php" class="admin-nav-item"><i class="fas fa-tags"></i> Categories</a>
        <a href="orders.php" class="admin-nav-item"><i class="fas fa-receipt"></i> Orders</a>
        <a href="users.php" class="admin-nav-item"><i class="fas fa-users"></i> Users</a>
        <a href="../index.php" class="admin-nav-item" style="margin-top:20px;"><i class="fas fa-globe"></i> View Site</a>
        <a href="../logout.php" class="admin-nav-item" style="color:#ff8a8a;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
</div>

<div class="admin-main">
    <div class="admin-page-title">Dishes 🍜</div>

    <?php if($msg): ?>
        <?php [$type, $text] = explode(':', $msg, 2); ?>
        <div class="alert alert-<?= $type ?>" style="margin-bottom:20px;"><?= htmlspecialchars($text) ?></div>
    <?php endif; ?>

    <div class="data-table">
        <div class="table-header">
            <h3>All Dishes (<?= count($dishes) ?>)</h3>
            <button class="btn-primary" onclick="openModal('addModal')"><i class="fas fa-plus"></i> Add Dish</button>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Dish Name</th>
                        <th>Restaurant</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($dishes as $d): ?>
                    <tr>
                        <td>#<?= $d['dish_id'] ?></td>
                        <td><strong><?= htmlspecialchars($d['dish_name']) ?></strong></td>
                        <td><?= htmlspecialchars($d['restaurant_name'] ?? '–') ?></td>
                        <td><?= htmlspecialchars($d['category_name'] ?? '–') ?></td>
                        <td style="color:var(--accent); font-weight:600;">₹<?= number_format($d['price'], 2) ?></td>
                        <td>
                            <span class="status-badge <?= $d['status'] ? 'status-delivered' : 'status-cancelled' ?>">
                                <?= $d['status'] ? 'Active' : 'Hidden' ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="?edit=<?= $d['dish_id'] ?>" class="btn-edit">Edit</a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this dish?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $d['dish_id'] ?>">
                                    <button type="submit" class="btn-del">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($dishes)): ?>
                    <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:40px;">No dishes yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ADD MODAL -->
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('addModal')"><i class="fas fa-times"></i></button>
        <h3>Add New Dish</h3>
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
                <label>Category</label>
                <select name="category_id">
                    <option value="">No Category</option>
                    <?php foreach($categories as $c): ?>
                    <option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['restaurant_name'] . ' › ' . $c['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Dish Name *</label><input type="text" name="name" required placeholder="e.g. Butter Chicken"></div>
            <div class="form-group"><label>Description</label><textarea name="description" placeholder="Short description..."></textarea></div>
            <div class="form-group"><label>Price (₹) *</label><input type="number" name="price" required min="1" step="0.01" placeholder="0.00"></div>
            <button type="submit" class="form-submit"><i class="fas fa-plus"></i> Add Dish</button>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<?php if($edit_dish): ?>
<div class="modal-overlay open" id="editModal">
    <div class="modal">
        <button class="modal-close" onclick="window.location='dishes.php'"><i class="fas fa-times"></i></button>
        <h3>Edit Dish</h3>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $edit_dish['dish_id'] ?>">
            <div class="form-group">
                <label>Restaurant *</label>
                <select name="restaurant_id" required>
                    <?php foreach($restaurants as $r): ?>
                    <option value="<?= $r['restaurant_id'] ?>" <?= $r['restaurant_id'] == $edit_dish['restaurant_id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['restaurant_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id">
                    <option value="">No Category</option>
                    <?php foreach($categories as $c): ?>
                    <option value="<?= $c['category_id'] ?>" <?= $c['category_id'] == $edit_dish['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['restaurant_name'] . ' › ' . $c['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Dish Name *</label><input type="text" name="name" required value="<?= htmlspecialchars($edit_dish['dish_name']) ?>"></div>
            <div class="form-group"><label>Description</label><textarea name="description"><?= htmlspecialchars($edit_dish['description']) ?></textarea></div>
            <div class="form-group"><label>Price (₹) *</label><input type="number" name="price" required min="1" step="0.01" value="<?= $edit_dish['price'] ?>"></div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="1" <?= $edit_dish['status'] ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= !$edit_dish['status'] ? 'selected' : '' ?>>Hidden</option>
                </select>
            </div>
            <button type="submit" class="form-submit"><i class="fas fa-save"></i> Save Changes</button>
        </form>
    </div>
</div>
<?php endif; ?>

<script src="../js/main.js"></script>
</body>
</html>
