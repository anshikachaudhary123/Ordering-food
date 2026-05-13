<?php
require_once '../connect.php';
if (!isAdmin()) redirect('../login.php');

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = intval($_POST['id']);
        // Don't allow self-delete
        if ($id !== (int)$_SESSION['user_id']) {
            $pdo->prepare("DELETE FROM users WHERE user_id = ?")->execute([$id]);
            $msg = 'success:User deleted.';
        } else {
            $msg = 'error:You cannot delete your own account.';
        }
    }

    redirect('users.php?msg=' . urlencode($msg));
}

$msg   = sanitize($_GET['msg'] ?? '');
$users = $pdo->query("
    SELECT u.*,
        (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.user_id) AS order_count
    FROM users u
    ORDER BY u.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users – Admin</title>
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
        <a href="orders.php" class="admin-nav-item"><i class="fas fa-receipt"></i> Orders</a>
        <a href="users.php" class="admin-nav-item active"><i class="fas fa-users"></i> Users</a>
        <a href="../index.php" class="admin-nav-item" style="margin-top:20px;"><i class="fas fa-globe"></i> View Site</a>
        <a href="../logout.php" class="admin-nav-item" style="color:#ff8a8a;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
</div>

<div class="admin-main">
    <div class="admin-page-title">Users 👥</div>

    <?php if($msg): ?>
        <?php [$type, $text] = explode(':', $msg, 2); ?>
        <div class="alert alert-<?= $type ?>" style="margin-bottom:20px;"><?= htmlspecialchars($text) ?></div>
    <?php endif; ?>

    <div class="data-table">
        <div class="table-header">
            <h3>All Users (<?= count($users) ?>)</h3>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Orders</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td>#<?= $u['user_id'] ?></td>
                        <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['phone'] ?: '–') ?></td>
                        <td>
                            <span class="status-badge <?= $u['role'] === 'admin' ? 'status-preparing' : 'status-delivered' ?>">
                                <?= ucfirst($u['role']) ?>
                            </span>
                        </td>
                        <td style="font-weight:600; color:var(--accent);"><?= $u['order_count'] ?></td>
                        <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <?php if($u['user_id'] !== (int)$_SESSION['user_id']): ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user and all their orders?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $u['user_id'] ?>">
                                <button type="submit" class="btn-del">Delete</button>
                            </form>
                            <?php else: ?>
                            <span style="color:var(--text-muted); font-size:0.8rem;">(You)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="../js/main.js"></script>
</body>
</html>
