<?php
require_once '../connect.php';
if (!isAdmin()) redirect('../login.php');

$msg = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name    = sanitize($_POST['name'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $phone   = sanitize($_POST['phone'] ?? '');
        if ($name) {
            $stmt = $pdo->prepare("INSERT INTO restaurants (restaurant_name, address, phone) VALUES (?, ?, ?)");
            $stmt->execute([$name, $address, $phone]);
            $msg = 'success:Restaurant added successfully!';
        }
    }

    if ($action === 'edit') {
        $id      = intval($_POST['id']);
        $name    = sanitize($_POST['name'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $phone   = sanitize($_POST['phone'] ?? '');
        $status  = intval($_POST['status'] ?? 1);
        $stmt = $pdo->prepare("UPDATE restaurants SET restaurant_name=?, address=?, phone=?, status=? WHERE restaurant_id=?");
        $stmt->execute([$name, $address, $phone, $status, $id]);
        $msg = 'success:Restaurant updated successfully!';
    }

    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM restaurants WHERE restaurant_id = ?");
        $stmt->execute([$id]);
        $msg = 'success:Restaurant deleted.';
    }

    redirect('restaurants.php?msg=' . urlencode($msg));
}

$msg = sanitize($_GET['msg'] ?? '');
$restaurants = $pdo->query("SELECT * FROM restaurants ORDER BY created_at DESC")->fetchAll();

// For edit modal
$edit_id = intval($_GET['edit'] ?? 0);
$edit_restaurant = null;
if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE restaurant_id = ?");
    $stmt->execute([$edit_id]);
    $edit_restaurant = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurants – Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="admin-sidebar">
    <div class="admin-logo">🍽 Admin Panel</div>
    <nav class="admin-nav">
        <a href="index.php" class="admin-nav-item"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="restaurants.php" class="admin-nav-item active"><i class="fas fa-store"></i> Restaurants</a>
        <a href="dishes.php" class="admin-nav-item"><i class="fas fa-utensils"></i> Dishes</a>
        <a href="categories.php" class="admin-nav-item"><i class="fas fa-tags"></i> Categories</a>
        <a href="orders.php" class="admin-nav-item"><i class="fas fa-receipt"></i> Orders</a>
        <a href="users.php" class="admin-nav-item"><i class="fas fa-users"></i> Users</a>
        <a href="../index.php" class="admin-nav-item" style="margin-top:20px;"><i class="fas fa-globe"></i> View Site</a>
        <a href="../logout.php" class="admin-nav-item" style="color:#ff8a8a;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
</div>

<div class="admin-main">
    <div class="admin-page-title">Restaurants 🏪</div>

    <?php if($msg): ?>
        <?php [$type, $text] = explode(':', $msg, 2); ?>
        <div class="alert alert-<?= $type ?>" style="margin-bottom:20px;"><?= htmlspecialchars($text) ?></div>
    <?php endif; ?>

    <div class="data-table">
        <div class="table-header">
            <h3>All Restaurants (<?= count($restaurants) ?>)</h3>
            <button class="btn-primary" onclick="openModal('addModal')"><i class="fas fa-plus"></i> Add Restaurant</button>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($restaurants as $r): ?>
                    <tr>
                        <td>#<?= $r['restaurant_id'] ?></td>
                        <td><strong><?= htmlspecialchars($r['restaurant_name']) ?></strong></td>
                        <td><?= htmlspecialchars($r['address']) ?></td>
                        <td><?= htmlspecialchars($r['phone']) ?></td>
                        <td>
                            <span class="status-badge <?= $r['status'] ? 'status-delivered' : 'status-cancelled' ?>">
                                <?= $r['status'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="?edit=<?= $r['restaurant_id'] ?>" class="btn-edit">Edit</a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this restaurant?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $r['restaurant_id'] ?>">
                                    <button type="submit" class="btn-del">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($restaurants)): ?>
                    <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:40px;">No restaurants yet</td></tr>
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
        <h3>Add Restaurant</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group"><label>Restaurant Name *</label><input type="text" name="name" required placeholder="e.g. Spice Garden"></div>
            <div class="form-group"><label>Address</label><textarea name="address" placeholder="Full address..."></textarea></div>
            <div class="form-group"><label>Phone</label><input type="text" name="phone" placeholder="0121-XXXXXXX"></div>
            <button type="submit" class="form-submit"><i class="fas fa-plus"></i> Add Restaurant</button>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<?php if($edit_restaurant): ?>
<div class="modal-overlay open" id="editModal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('editModal'); window.location='restaurants.php'"><i class="fas fa-times"></i></button>
        <h3>Edit Restaurant</h3>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $edit_restaurant['restaurant_id'] ?>">
            <div class="form-group"><label>Restaurant Name *</label><input type="text" name="name" required value="<?= htmlspecialchars($edit_restaurant['restaurant_name']) ?>"></div>
            <div class="form-group"><label>Address</label><textarea name="address"><?= htmlspecialchars($edit_restaurant['address']) ?></textarea></div>
            <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?= htmlspecialchars($edit_restaurant['phone']) ?>"></div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="1" <?= $edit_restaurant['status'] ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= !$edit_restaurant['status'] ? 'selected' : '' ?>>Inactive</option>
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
