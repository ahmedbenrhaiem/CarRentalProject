<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? 'admin.php' : 'user_dashboard.php'));
    exit;
}
require 'db/connection.php';

$error = '';
$success = '';
if (isset($_GET['registered'])) $success = "Registration successful! Use the activation link to activate your account.";
if (isset($_GET['activated'])) $success = "Account activated! You can now log in.";
if (isset($_GET['password_reset'])) $success = "Password reset successfully! Log in with your new password.";
if (isset($_GET['account_deleted'])) $success = "Your account has been deleted.";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');
    if (empty($u) || empty($p)) {
        $error = "Both fields are required.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$u]);
        $user = $stmt->fetch();
        if ($user) {
            if (isset($user['is_active']) && $user['is_active'] == 0) {
                $error = "Account not activated. Use the activation link.";
            } else {
                $ok = password_verify($p, $user['password']);
                if (!$ok && $p === $user['password']) {
                    $ok = true;
                    $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($p, PASSWORD_DEFAULT), $user['id']]);
                }
                if ($ok) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    header("Location: " . ($user['role'] === 'admin' ? 'admin.php' : 'user_dashboard.php'));
                    exit;
                } else { $error = "Invalid username or password."; }
            }
        } else { $error = "Invalid username or password."; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/custom.css">
</head>
<body class="page-auth d-flex align-items-center">
    <main class="container">
        <div class="row justify-content-center">
            <div class="col-sm-8 col-md-6 col-lg-4">
                <div class="card shadow-lg border-0 rounded-4 fade-in-up">
                    <div class="card-body p-4 p-md-5">
                        <header class="text-center mb-4">
                            <i class="fas fa-car text-primary fs-1 d-block mb-2"></i>
                            <h1 class="h3 fw-bold">Car Rental System</h1>
                            <p class="text-muted small">Please log in to continue</p>
                        </header>

                        <?php if ($success): ?>
                            <div class="alert alert-success small"><i class="fas fa-check-circle me-1"></i><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger small"><i class="fas fa-exclamation-circle me-1"></i><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold"><i class="fas fa-user text-primary me-1"></i>Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold"><i class="fas fa-lock text-primary me-1"></i>Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3"><i class="fas fa-sign-in-alt me-1"></i>Login</button>
                        </form>

                        <nav class="text-center">
                            <a href="register.php" class="d-block mb-2 text-decoration-none"><i class="fas fa-user-plus me-1"></i>Create New Account</a>
                            <a href="forgot_password.php" class="text-muted small text-decoration-none"><i class="fas fa-key me-1"></i>Forgot Password?</a>
                        </nav>

                        <hr>
                        <div class="bg-light rounded-3 p-2 small text-muted text-center">
                            <strong>Test:</strong> admin/admin &nbsp;|&nbsp; user1/password1
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
