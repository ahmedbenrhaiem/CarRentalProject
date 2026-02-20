<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'db/connection.php';

$error = '';
$success = '';
$reset_link = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Enter a valid email address.";
    } else {
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $pdo->prepare("UPDATE users SET reset_token=?, reset_token_expires=? WHERE id=?")->execute([$token, $expires, $user['id']]);

            $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $reset_link = "{$proto}://{$_SERVER['HTTP_HOST']}" . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token={$token}";
            $success = "Reset link generated for: " . htmlspecialchars($user['username']);
        } else {
            $error = "No account found with that email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Car Rental</title>
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
                            <i class="fas fa-key text-primary fs-1 d-block mb-2"></i>
                            <h1 class="h3 fw-bold">Forgot Password</h1>
                            <p class="text-muted small">Enter your email to get a reset link</p>
                        </header>

                        <?php if ($success): ?>
                            <div class="alert alert-success small"><i class="fas fa-check-circle me-1"></i><?php echo $success; ?></div>
                            <?php if ($reset_link): ?>
                                <div class="activation-box p-3 text-center mb-3">
                                    <p class="fw-bold mb-2"><i class="fas fa-link me-1"></i>Reset Link:</p>
                                    <a href="<?php echo htmlspecialchars($reset_link); ?>" class="d-block small"><?php echo htmlspecialchars($reset_link); ?></a>
                                    <p class="text-muted small mt-2 mb-0"><i class="fas fa-info-circle me-1"></i>In production this would be emailed. Expires in 1 hour.</p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger small"><i class="fas fa-exclamation-circle me-1"></i><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <?php if (!$success): ?>
                            <form method="POST">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold"><i class="fas fa-envelope text-primary me-1"></i>Email</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-paper-plane me-1"></i>Send Reset Link</button>
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
