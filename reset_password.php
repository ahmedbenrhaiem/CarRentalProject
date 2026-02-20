<?php
require 'db/connection.php';

$error = '';
$valid_token = false;
$token = $_GET['token'] ?? $_POST['token'] ?? '';

if (!empty($token)) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if ($user) $valid_token = true;
    else $error = "Invalid or expired reset link.";
} else {
    $error = "No reset token provided.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $p1 = $_POST['new_password'] ?? '';
    $p2 = $_POST['confirm_password'] ?? '';
    if (strlen($p1) < 6) $error = "Password must be at least 6 characters.";
    elseif ($p1 !== $p2) $error = "Passwords do not match.";
    else {
        $pdo->prepare("UPDATE users SET password=?, reset_token=NULL, reset_token_expires=NULL WHERE id=?")->execute([password_hash($p1, PASSWORD_DEFAULT), $user['id']]);
        header("Location: login.php?password_reset=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Car Rental</title>
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
                            <i class="fas fa-lock text-primary fs-1 d-block mb-2"></i>
                            <h1 class="h3 fw-bold">Reset Password</h1>
                        </header>

                        <?php if ($error): ?>
                            <div class="alert alert-danger small"><i class="fas fa-exclamation-circle me-1"></i><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <?php if ($valid_token): ?>
                            <form method="POST">
                                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">New Password</label>
                                    <input type="password" class="form-control" name="new_password" required minlength="6">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Confirm New Password</label>
                                    <input type="password" class="form-control" name="confirm_password" required minlength="6">
                                </div>
                                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Reset Password</button>
                            </form>
                        <?php endif; ?>

                        <nav class="text-center mt-3">
                            <a href="login.php" class="text-decoration-none"><i class="fas fa-sign-in-alt me-1"></i>Back to Login</a>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
