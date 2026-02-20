<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
require 'db/connection.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
$email_link = '';

// Fetch user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if (!$user) { header("Location: login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // === EDIT BASIC DATA ===
    if ($action === 'edit_profile') {
        $first_name  = trim($_POST['first_name'] ?? '');
        $last_name   = trim($_POST['last_name'] ?? '');
        $phone       = trim($_POST['phone'] ?? '');
        $address     = trim($_POST['address'] ?? '');
        $city        = trim($_POST['city'] ?? '');
        $postal_code = trim($_POST['postal_code'] ?? '');
        $country     = trim($_POST['country'] ?? '');

        if (empty($first_name) || empty($last_name)) {
            $error = "First and last name are required.";
        } else {
            $pdo->prepare("UPDATE users SET first_name=?, last_name=?, phone=?, address=?, city=?, postal_code=?, country=? WHERE id=?")
                ->execute([$first_name, $last_name, $phone, $address, $city, $postal_code, $country, $user_id]);
            $success = "Profile updated successfully!";
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        }
    }

    // === CHANGE PASSWORD ===
    elseif ($action === 'change_password') {
        $old1 = $_POST['old_password1'] ?? '';
        $old2 = $_POST['old_password2'] ?? '';
        $new1 = $_POST['new_password1'] ?? '';
        $new2 = $_POST['new_password2'] ?? '';

        if ($old1 !== $old2) $error = "Old password entries do not match.";
        elseif ($new1 !== $new2) $error = "New passwords do not match.";
        elseif (strlen($new1) < 6) $error = "New password must be at least 6 characters.";
        else {
            $ok = password_verify($old1, $user['password']) || ($old1 === $user['password']);
            if (!$ok) $error = "Current password is incorrect.";
            else {
                $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new1, PASSWORD_DEFAULT), $user_id]);
                $success = "Password changed successfully!";
            }
        }
    }

    // === CHANGE EMAIL ===
    elseif ($action === 'change_email') {
        $new_email = trim($_POST['new_email'] ?? '');
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) $error = "Enter a valid email.";
        elseif ($new_email === $user['email']) $error = "That is your current email.";
        else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$new_email, $user_id]);
            if ($stmt->fetch()) $error = "Email already used by another account.";
            else {
                $token = bin2hex(random_bytes(32));
                $pdo->prepare("UPDATE users SET pending_email=?, email_token=? WHERE id=?")->execute([$new_email, $token, $user_id]);
                $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $email_link = "{$proto}://{$_SERVER['HTTP_HOST']}" . dirname($_SERVER['PHP_SELF']) . "/activate.php?email_token={$token}";
                $success = "Email change confirmation link generated.";
            }
        }
    }

    // === DELETE ACCOUNT ===
    elseif ($action === 'delete_account') {
        $pwd = $_POST['confirm_delete_password'] ?? '';
        $ok = password_verify($pwd, $user['password']) || ($pwd === $user['password']);
        if (!$ok) $error = "Password incorrect. Account not deleted.";
        else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM rentals WHERE user_id = ? AND released_at IS NULL");
            $stmt->execute([$user_id]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Release all rented cars before deleting your account.";
            } else {
                $pdo->prepare("DELETE FROM rentals WHERE user_id = ?")->execute([$user_id]);
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
                session_destroy();
                header("Location: login.php?account_deleted=1");
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/custom.css">
</head>
<body class="page-dashboard">
    <!-- Nav -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="user_dashboard.php"><i class="fas fa-car me-2"></i>CarRental Pro</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="user_dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="profile.php"><i class="fas fa-user-cog me-1"></i>Profile</a></li>
                    <li class="nav-item"><span class="nav-link text-light"><?php echo htmlspecialchars($user['username']); ?></span></li>
                    <li class="nav-item"><a class="nav-link text-warning" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle me-1"></i><?php echo $success; ?></div>
            <?php if ($email_link): ?>
                <div class="activation-box p-3 text-center mb-3">
                    <p class="fw-bold mb-2"><i class="fas fa-link me-1"></i>Confirm Email Link:</p>
                    <a href="<?php echo htmlspecialchars($email_link); ?>" class="d-block small"><?php echo htmlspecialchars($email_link); ?></a>
                    <p class="text-muted small mt-2 mb-0"><i class="fas fa-info-circle me-1"></i>In production this would be emailed.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-1"></i><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- EDIT PROFILE -->
        <section class="card section-card mb-4">
            <div class="card-header bg-white"><h2 class="h5 mb-0"><i class="fas fa-user-edit text-primary me-2"></i>Edit Profile</h2></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="edit_profile">
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Username</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">First Name *</label>
                            <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Last Name *</label>
                            <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address</label>
                        <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">City</label>
                            <input type="text" class="form-control" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Postal Code</label>
                            <input type="text" class="form-control" name="postal_code" value="<?php echo htmlspecialchars($user['postal_code'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Country</label>
                            <input type="text" class="form-control" name="country" value="<?php echo htmlspecialchars($user['country'] ?? ''); ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Save Changes</button>
                </form>
            </div>
        </section>

        <!-- CHANGE PASSWORD -->
        <section class="card section-card mb-4">
            <div class="card-header bg-white"><h2 class="h5 mb-0"><i class="fas fa-lock text-primary me-2"></i>Change Password</h2></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Current Password (1st entry) *</label>
                            <input type="password" class="form-control" name="old_password1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Current Password (2nd entry) *</label>
                            <input type="password" class="form-control" name="old_password2" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">New Password *</label>
                            <input type="password" class="form-control" name="new_password1" required minlength="6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Confirm New Password *</label>
                            <input type="password" class="form-control" name="new_password2" required minlength="6">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-key me-1"></i>Change Password</button>
                </form>
            </div>
        </section>

        <!-- CHANGE EMAIL -->
        <section class="card section-card mb-4">
            <div class="card-header bg-white"><h2 class="h5 mb-0"><i class="fas fa-envelope text-primary me-2"></i>Change Email</h2></div>
            <div class="card-body">
                <p class="text-muted">Current email: <strong><?php echo htmlspecialchars($user['email'] ?? 'Not set'); ?></strong></p>
                <form method="POST">
                    <input type="hidden" name="action" value="change_email">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">New Email *</label>
                            <input type="email" class="form-control" name="new_email" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Send Confirmation Link</button>
                </form>
            </div>
        </section>

        <!-- DELETE ACCOUNT -->
        <section class="card section-card mb-4 border-danger">
            <div class="card-header bg-white"><h2 class="h5 mb-0 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Delete Account</h2></div>
            <div class="card-body">
                <p class="text-muted">This is permanent. All your data will be removed. You must release all rented cars first.</p>
                <form method="POST" onsubmit="return confirm('Are you SURE? This cannot be undone!');">
                    <input type="hidden" name="action" value="delete_account">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Enter password to confirm *</label>
                            <input type="password" class="form-control" name="confirm_delete_password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i>Delete My Account</button>
                </form>
            </div>
        </section>
    </main>

    <footer class="bg-dark text-light text-center py-3 mt-auto">
        <p class="mb-0">&copy; 2025 Car Rental System</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
