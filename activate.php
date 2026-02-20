<?php
require 'db/connection.php';

$error = '';

// Account activation
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    $stmt = $pdo->prepare("SELECT id FROM users WHERE activation_token = ? AND is_active = 0");
    $stmt->execute([$token]);
    if ($stmt->fetch()) {
        $pdo->prepare("UPDATE users SET is_active = 1, activation_token = NULL WHERE activation_token = ?")->execute([$token]);
        header("Location: login.php?activated=1");
        exit;
    } else {
        $error = "Invalid or expired activation link.";
    }
}

// Email change activation
elseif (isset($_GET['email_token'])) {
    $token = trim($_GET['email_token']);
    $stmt = $pdo->prepare("SELECT id, pending_email FROM users WHERE email_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if ($user && !empty($user['pending_email'])) {
        $pdo->prepare("UPDATE users SET email = ?, pending_email = NULL, email_token = NULL, is_active = 1 WHERE id = ?")->execute([$user['pending_email'], $user['id']]);
        header("Location: login.php?activated=1");
        exit;
    } else {
        $error = "Invalid or expired email change link.";
    }
}

else {
    $error = "No activation token provided.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activation - Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/custom.css">
</head>
<body class="page-auth d-flex align-items-center">
    <main class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-4 text-center">
                        <i class="fas fa-envelope-open text-primary fs-1 d-block mb-3"></i>
                        <h1 class="h4 fw-bold mb-3">Account Activation</h1>
                        <?php if ($error): ?>
                            <div class="alert alert-danger small"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <a href="login.php" class="btn btn-primary"><i class="fas fa-sign-in-alt me-1"></i>Go to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
