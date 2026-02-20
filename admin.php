<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit; }
require 'db/connection.php';

// Handle car removal
if (isset($_GET['remove_car']) && isset($_GET['car_id'])) {
    $car_id = (int)$_GET['car_id'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rentals WHERE car_id = ? AND released_at IS NULL");
    $stmt->execute([$car_id]);
    if ($stmt->fetchColumn() > 0) {
        $error_message = "Cannot remove car - it is currently rented!";
    } else {
        $pdo->prepare("DELETE FROM cars WHERE id = ?")->execute([$car_id]);
        $success_message = "Car removed successfully!";
    }
}

// Fetch data
$stmt = $pdo->prepare("SELECT * FROM cars WHERE is_rented = FALSE ORDER BY brand, model");
$stmt->execute();
$available_cars = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT cars.*, users.username, rentals.rented_at FROM cars JOIN rentals ON cars.id = rentals.car_id JOIN users ON rentals.user_id = users.id WHERE rentals.released_at IS NULL ORDER BY rentals.rented_at DESC");
$stmt->execute();
$rented_cars = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/custom.css">
</head>
<body class="page-dashboard">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="admin.php"><i class="fas fa-car me-2"></i>CarRental Pro</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link active" href="admin.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_users.php"><i class="fas fa-users me-1"></i>Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_stats.php"><i class="fas fa-chart-bar me-1"></i>Statistics</a></li>
                    <li class="nav-item"><span class="nav-link text-light"><?php echo htmlspecialchars($admin['username']); ?> <span class="admin-badge">Admin</span></span></li>
                    <li class="nav-item"><a class="nav-link text-warning" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        <h1 class="h3 fw-bold mb-4"><i class="fas fa-tachometer-alt text-primary me-2"></i>Admin Dashboard</h1>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle me-1"></i><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-1"></i><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Stats Row -->
        <section class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card stat-card shadow-sm text-center p-3">
                    <div class="stat-number"><?php echo count($available_cars); ?></div>
                    <div class="text-muted fw-semibold">Available Cars</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm text-center p-3">
                    <div class="stat-number"><?php echo count($rented_cars); ?></div>
                    <div class="text-muted fw-semibold">Currently Rented</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm text-center p-3">
                    <div class="stat-number"><?php echo count($available_cars) + count($rented_cars); ?></div>
                    <div class="text-muted fw-semibold">Total Fleet</div>
                </div>
            </div>
        </section>

        <!-- Add Car -->
        <section class="card section-card mb-4">
            <div class="card-header bg-white"><h2 class="h5 mb-0"><i class="fas fa-plus-circle text-primary me-2"></i>Add New Car</h2></div>
            <div class="card-body">
                <button class="btn btn-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#addCarForm">
                    <i class="fas fa-car me-1"></i>Add a Car
                </button>
                <div class="collapse" id="addCarForm">
                    <form action="add_car.php" method="POST" enctype="multipart/form-data" class="border rounded-3 p-4 bg-light">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Manufacturer *</label>
                                <input type="text" class="form-control" name="manufacturer" placeholder="e.g. Stellantis" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Brand *</label>
                                <input type="text" class="form-control" name="brand" placeholder="e.g. Citroen" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Model *</label>
                                <input type="text" class="form-control" name="model" placeholder="e.g. C5X" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Plate *</label>
                                <input type="text" class="form-control" name="plate" placeholder="e.g. DW12345" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Type *</label>
                                <select class="form-select" name="type" required>
                                    <option value="">Select</option>
                                    <option value="sedan">Sedan</option>
                                    <option value="hatchback">Hatchback</option>
                                    <option value="SUV">SUV</option>
                                    <option value="coupe">Coupe</option>
                                    <option value="convertible">Convertible</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Fuel *</label>
                                <select class="form-select" name="fuel" required>
                                    <option value="">Select</option>
                                    <option value="gasoline">Gasoline</option>
                                    <option value="diesel">Diesel</option>
                                    <option value="hybrid">Hybrid</option>
                                    <option value="electric">Electric</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Transmission *</label>
                                <select class="form-select" name="transmission" required>
                                    <option value="">Select</option>
                                    <option value="manual">Manual</option>
                                    <option value="automatic">Automatic</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Mileage (km) *</label>
                                <input type="number" class="form-control" name="mileage" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Photo *</label>
                                <input type="file" class="form-control" name="photo" accept="image/*" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Notes</label>
                                <textarea class="form-control" name="notes" rows="2" placeholder="Additional info..."></textarea>
                            </div>
                        </div>
                        <div class="mt-3 d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#addCarForm"><i class="fas fa-times me-1"></i>Cancel</button>
                            <button type="submit" class="btn btn-success"><i class="fas fa-plus me-1"></i>Add Car</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- Available Cars -->
        <section class="card section-card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="fas fa-car-side text-primary me-2"></i>Available Cars</h2>
                <span class="badge bg-primary rounded-pill"><?php echo count($available_cars); ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($available_cars)): ?>
                    <p class="text-muted text-center py-4">No available cars.</p>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($available_cars as $car): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card car-card h-100">
                                <div class="card-body p-3" data-bs-toggle="collapse" data-bs-target="#adm-<?php echo $car['id']; ?>">
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($car['photo'])): ?>
                                            <img src="<?php echo htmlspecialchars($car['photo']); ?>" class="car-thumb me-3" alt="Car">
                                        <?php else: ?>
                                            <div class="car-thumb-placeholder me-3"><i class="fas fa-car"></i></div>
                                        <?php endif; ?>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h6>
                                            <small class="text-muted">
                                                <i class="fas fa-cog me-1"></i><?php echo htmlspecialchars($car['transmission']); ?>
                                                <i class="fas fa-gas-pump ms-2 me-1"></i><?php echo htmlspecialchars($car['fuel']); ?>
                                            </small>
                                        </div>
                                        <i class="fas fa-chevron-down text-muted"></i>
                                    </div>
                                </div>
                                <div class="collapse" id="adm-<?php echo $car['id']; ?>">
                                    <div class="car-details-area p-3">
                                        <div class="row g-2 mb-3 small">
                                            <div class="col-6"><strong>Manufacturer:</strong><br><?php echo htmlspecialchars($car['manufacturer']); ?></div>
                                            <div class="col-6"><strong>Plate:</strong><br><?php echo htmlspecialchars($car['plate']); ?></div>
                                            <div class="col-6"><strong>Type:</strong><br><?php echo htmlspecialchars($car['type']); ?></div>
                                            <div class="col-6"><strong>Mileage:</strong><br><?php echo number_format($car['mileage']); ?> km</div>
                                            <div class="col-12"><strong>Notes:</strong><br><?php echo htmlspecialchars($car['notes'] ?? 'No additional information'); ?></div>
                                        </div>
                                        <a href="admin.php?remove_car=1&car_id=<?php echo $car['id']; ?>"
                                           class="btn btn-danger w-100"
                                           onclick="return confirm('Remove <?php echo htmlspecialchars($car['brand'] . ' ' . $car['model'], ENT_QUOTES); ?>?');">
                                            <i class="fas fa-trash me-1"></i>Remove Car
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Rented Cars -->
        <section class="card section-card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="fas fa-key text-success me-2"></i>Currently Rented</h2>
                <span class="badge bg-success rounded-pill"><?php echo count($rented_cars); ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($rented_cars)): ?>
                    <p class="text-muted text-center py-4">No cars currently rented.</p>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($rented_cars as $car): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card car-card border-success h-100">
                                <div class="card-body p-3" data-bs-toggle="collapse" data-bs-target="#admr-<?php echo $car['id']; ?>">
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($car['photo'])): ?>
                                            <img src="<?php echo htmlspecialchars($car['photo']); ?>" class="car-thumb me-3" alt="Car">
                                        <?php else: ?>
                                            <div class="car-thumb-placeholder me-3"><i class="fas fa-car"></i></div>
                                        <?php endif; ?>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h6>
                                            <small class="text-primary fw-semibold"><i class="fas fa-user me-1"></i><?php echo htmlspecialchars($car['username']); ?></small><br>
                                            <small class="text-muted">Rented: <?php echo date('M j, Y', strtotime($car['rented_at'])); ?></small>
                                        </div>
                                        <i class="fas fa-chevron-down text-muted"></i>
                                    </div>
                                </div>
                                <div class="collapse" id="admr-<?php echo $car['id']; ?>">
                                    <div class="car-details-area p-3">
                                        <div class="row g-2 small">
                                            <div class="col-6"><strong>Manufacturer:</strong><br><?php echo htmlspecialchars($car['manufacturer']); ?></div>
                                            <div class="col-6"><strong>Plate:</strong><br><?php echo htmlspecialchars($car['plate']); ?></div>
                                            <div class="col-6"><strong>Type:</strong><br><?php echo htmlspecialchars($car['type']); ?></div>
                                            <div class="col-6"><strong>Mileage:</strong><br><?php echo number_format($car['mileage']); ?> km</div>
                                            <div class="col-12"><strong>Notes:</strong><br><?php echo htmlspecialchars($car['notes'] ?? 'No additional information'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="bg-dark text-light text-center py-3">
        <p class="mb-0">&copy; 2025 Car Rental System</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
