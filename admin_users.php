<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit; }
require 'db/connection.php';

$error = '';
$success = '';

// DELETE user
if (isset($_GET['delete_user'])) {
    $del_id = (int)$_GET['delete_user'];
    if ($del_id === (int)$_SESSION['user_id']) {
        $error = "You cannot delete your own account.";
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM rentals WHERE user_id = ? AND released_at IS NULL");
        $stmt->execute([$del_id]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Cannot delete - user has active rentals.";
        } else {
            $pdo->prepare("DELETE FROM rentals WHERE user_id = ?")->execute([$del_id]);
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$del_id]);
            $success = "User deleted.";
        }
    }
}

// EDIT user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_user') {
    $eid = (int)$_POST['user_id'];
    $pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone=?, address=?, city=?, postal_code=?, country=?, role=?, is_active=? WHERE id=?")
        ->execute([
            trim($_POST['first_name']), trim($_POST['last_name']), trim($_POST['email']),
            trim($_POST['phone']), trim($_POST['address']), trim($_POST['city']),
            trim($_POST['postal_code']), trim($_POST['country']),
            $_POST['role'], isset($_POST['is_active']) ? 1 : 0, $eid
        ]);
    $success = "User updated.";
}

// Fetch all users
$users = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM rentals r WHERE r.user_id=u.id AND r.released_at IS NULL) as active_rentals, (SELECT COUNT(*) FROM rentals r WHERE r.user_id=u.id) as total_rentals FROM users u ORDER BY u.id")->fetchAll();

// Editing?
$editing = null;
if (isset($_GET['edit_user'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([(int)$_GET['edit_user']]);
    $editing = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Car Rental Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/custom.css">
</head>
<body class="page-dashboard">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="admin.php"><i class="fas fa-car me-2"></i>CarRental Pro</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="admin.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="admin_users.php"><i class="fas fa-users me-1"></i>Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_stats.php"><i class="fas fa-chart-bar me-1"></i>Statistics</a></li>
                    <li class="nav-item"><span class="nav-link text-light"><span class="admin-badge">Admin</span></span></li>
                    <li class="nav-item"><a class="nav-link text-warning" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        <h1 class="h3 fw-bold mb-4"><i class="fas fa-users text-primary me-2"></i>User Management</h1>

        <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle me-1"></i><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle me-1"></i><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <!-- Edit Form -->
        <?php if ($editing): ?>
        <section class="card section-card mb-4 border-primary">
            <div class="card-header bg-white"><h2 class="h5 mb-0"><i class="fas fa-user-edit text-primary me-2"></i>Edit: <?php echo htmlspecialchars($editing['username']); ?></h2></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="edit_user">
                    <input type="hidden" name="user_id" value="<?php echo $editing['id']; ?>">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label fw-semibold">Username</label><input class="form-control" value="<?php echo htmlspecialchars($editing['username']); ?>" disabled></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Email</label><input class="form-control" name="email" value="<?php echo htmlspecialchars($editing['email'] ?? ''); ?>"></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">First Name</label><input class="form-control" name="first_name" value="<?php echo htmlspecialchars($editing['first_name'] ?? ''); ?>"></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Last Name</label><input class="form-control" name="last_name" value="<?php echo htmlspecialchars($editing['last_name'] ?? ''); ?>"></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Phone</label><input class="form-control" name="phone" value="<?php echo htmlspecialchars($editing['phone'] ?? ''); ?>"></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Address</label><input class="form-control" name="address" value="<?php echo htmlspecialchars($editing['address'] ?? ''); ?>"></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">City</label><input class="form-control" name="city" value="<?php echo htmlspecialchars($editing['city'] ?? ''); ?>"></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Postal Code</label><input class="form-control" name="postal_code" value="<?php echo htmlspecialchars($editing['postal_code'] ?? ''); ?>"></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Country</label><input class="form-control" name="country" value="<?php echo htmlspecialchars($editing['country'] ?? ''); ?>"></div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Role</label>
                            <select class="form-select" name="role">
                                <option value="user" <?php echo $editing['role']==='user'?'selected':''; ?>>User</option>
                                <option value="admin" <?php echo $editing['role']==='admin'?'selected':''; ?>>Admin</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" <?php echo ($editing['is_active']??1)?'checked':''; ?>>
                                <label class="form-check-label" for="isActive">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-2 justify-content-end">
                        <a href="admin_users.php" class="btn btn-secondary"><i class="fas fa-times me-1"></i>Cancel</a>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Save</button>
                    </div>
                </form>
            </div>
        </section>
        <?php endif; ?>

        <!-- Users Table -->
        <section class="card section-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="fas fa-list text-primary me-2"></i>All Users</h2>
                <span class="badge bg-primary rounded-pill"><?php echo count($users); ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th><th>Username</th><th>Name</th><th>Email</th><th>Role</th><th>Active</th><th>Rentals</th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars(($u['first_name']??'').' '.($u['last_name']??'')); ?></td>
                                <td><?php echo htmlspecialchars($u['email']??'-'); ?></td>
                                <td>
                                    <?php if ($u['role']==='admin'): ?><span class="badge bg-warning text-dark">Admin</span>
                                    <?php else: ?><span class="badge bg-info">User</span><?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($u['is_active']??1): ?><span class="badge bg-success">Active</span>
                                    <?php else: ?><span class="badge bg-danger">Inactive</span><?php endif; ?>
                                </td>
                                <td><small><?php echo $u['active_rentals']; ?> active / <?php echo $u['total_rentals']; ?> total</small></td>
                                <td>
                                    <a href="admin_users.php?edit_user=<?php echo $u['id']; ?>" class="btn btn-sm btn-primary btn-action me-1" title="Edit"><i class="fas fa-edit"></i></a>
                                    <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
                                        <a href="admin_users.php?delete_user=<?php echo $u['id']; ?>" class="btn btn-sm btn-danger btn-action" title="Delete" onclick="return confirm('Delete <?php echo htmlspecialchars($u['username'],ENT_QUOTES); ?>?');"><i class="fas fa-trash"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-dark text-light text-center py-3 mt-4">
        <p class="mb-0">&copy; 2025 Car Rental System</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
