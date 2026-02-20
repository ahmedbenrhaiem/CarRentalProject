<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'db/connection.php';

$error = '';
$success = '';
$activation_link = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username    = trim($_POST['username'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';
    $password2   = $_POST['password2'] ?? '';
    $first_name  = trim($_POST['first_name'] ?? '');
    $last_name   = trim($_POST['last_name'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $city        = trim($_POST['city'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $country     = trim($_POST['country'] ?? '');

    $errors = [];
    if (strlen($username) < 3) $errors[] = "Username must be at least 3 characters.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Enter a valid email.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $password2) $errors[] = "Passwords do not match.";
    if (empty($first_name) || empty($last_name)) $errors[] = "First and last name required.";
    if (empty($address) || empty($city) || empty($country)) $errors[] = "Address, city, and country required.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) $errors[] = "Username already taken.";
    }
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors[] = "Email already registered.";
    }

    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    } else {
        $token = bin2hex(random_bytes(32));
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, first_name, last_name, phone, address, city, postal_code, country, is_active, activation_token) VALUES (?,?,?,'user',?,?,?,?,?,?,?,0,?)");
        $stmt->execute([$username, $email, $hash, $first_name, $last_name, $phone, $address, $city, $postal_code, $country, $token]);

        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $activation_link = "{$proto}://{$_SERVER['HTTP_HOST']}" . dirname($_SERVER['PHP_SELF']) . "/activate.php?token={$token}";
        $success = "Registration successful! Use the activation link below.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/custom.css">
</head>
<body class="page-auth d-flex align-items-center py-5">
    <main class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-lg border-0 rounded-4 fade-in-up">
                    <div class="card-body p-4 p-md-5">
                        <header class="text-center mb-4">
                            <i class="fas fa-user-plus text-primary fs-1 d-block mb-2"></i>
                            <h1 class="h3 fw-bold">Create Account</h1>
                            <p class="text-muted small">Join Car Rental System</p>
                        </header>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><i class="fas fa-check-circle me-1"></i><?php echo $success; ?></div>
                            <?php if ($activation_link): ?>
                                <div class="activation-box p-3 text-center mb-3">
                                    <p class="fw-bold mb-2"><i class="fas fa-link me-1"></i>Activation Link:</p>
                                    <a href="<?php echo htmlspecialchars($activation_link); ?>" class="d-block small"><?php echo htmlspecialchars($activation_link); ?></a>
                                    <p class="text-muted small mt-2 mb-0"><i class="fas fa-info-circle me-1"></i>In production this would be sent via email. Click to activate.</p>
                                </div>
                            <?php endif; ?>
                            <div class="text-center"><a href="login.php" class="btn btn-primary"><i class="fas fa-sign-in-alt me-1"></i>Go to Login</a></div>
                        <?php else: ?>

                            <?php if ($error): ?>
                                <div class="alert alert-danger small"><i class="fas fa-exclamation-circle me-1"></i><?php echo $error; ?></div>
                            <?php endif; ?>

                            <form method="POST">
                                <h6 class="text-muted fw-bold border-bottom pb-2 mb-3"><i class="fas fa-user text-primary me-1"></i>Account Information</h6>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label class="form-label fw-semibold">Username *</label>
                                        <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required minlength="3">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email *</label>
                                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label class="form-label fw-semibold">Password *</label>
                                        <input type="password" class="form-control" name="password" required minlength="6">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Confirm Password *</label>
                                        <input type="password" class="form-control" name="password2" required>
                                    </div>
                                </div>

                                <h6 class="text-muted fw-bold border-bottom pb-2 mb-3 mt-4"><i class="fas fa-id-card text-primary me-1"></i>Personal Information</h6>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label class="form-label fw-semibold">First Name *</label>
                                        <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Last Name *</label>
                                        <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Phone</label>
                                    <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                                </div>

                                <h6 class="text-muted fw-bold border-bottom pb-2 mb-3 mt-4"><i class="fas fa-map-marker-alt text-primary me-1"></i>Address</h6>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Street Address *</label>
                                    <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>" required>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <label class="form-label fw-semibold">City *</label>
                                        <input type="text" class="form-control" name="city" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <label class="form-label fw-semibold">Postal Code</label>
                                        <input type="text" class="form-control" name="postal_code" value="<?php echo htmlspecialchars($_POST['postal_code'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Country *</label>
                                        <input type="text" class="form-control" name="country" value="<?php echo htmlspecialchars($_POST['country'] ?? 'Poland'); ?>" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 mb-3"><i class="fas fa-user-plus me-1"></i>Create Account</button>
                            </form>
                            <div class="text-center"><a href="login.php" class="text-decoration-none"><i class="fas fa-sign-in-alt me-1"></i>Already have an account? Log in</a></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
