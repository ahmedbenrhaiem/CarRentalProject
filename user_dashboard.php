<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') { header("Location: login.php"); exit; }
require 'db/connection.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM cars WHERE is_rented = 0");
$stmt->execute();
$available_cars = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT c.*, r.rented_at FROM cars c JOIN rentals r ON c.id = r.car_id WHERE r.user_id = ? AND r.released_at IS NULL");
$stmt->execute([$user_id]);
$current_rentals = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT c.*, r.rented_at, r.released_at FROM cars c JOIN rentals r ON c.id = r.car_id WHERE r.user_id = ? AND r.released_at IS NOT NULL ORDER BY r.released_at DESC");
$stmt->execute([$user_id]);
$rental_history = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/custom.css">
</head>
<body class="page-dashboard">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="user_dashboard.php"><i class="fas fa-car me-2"></i>CarRental Pro</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link active" href="user_dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php"><i class="fas fa-user-cog me-1"></i>Profile</a></li>
                    <li class="nav-item"><span class="nav-link text-light">Welcome, <?php echo htmlspecialchars($user['username']); ?></span></li>
                    <li class="nav-item"><a class="nav-link text-warning" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        <h1 class="h3 fw-bold mb-4"><i class="fas fa-tachometer-alt text-primary me-2"></i>My Dashboard</h1>

        <!-- Available Cars -->
        <section class="card section-card mb-4 fade-in-up">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="fas fa-car-side text-primary me-2"></i>Available Cars</h2>
                <span class="badge bg-primary rounded-pill"><?php echo count($available_cars); ?> available</span>
            </div>
            <div class="card-body">
                <?php if (empty($available_cars)): ?>
                    <p class="text-muted text-center py-4"><i class="fas fa-car-side fs-1 d-block mb-2 opacity-25"></i>No cars available right now.</p>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($available_cars as $car): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card car-card h-100">
                                <div class="card-body p-3" data-bs-toggle="collapse" data-bs-target="#details-<?php echo $car['id']; ?>">
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
                                                <i class="fas fa-tachometer-alt ms-2 me-1"></i><?php echo number_format($car['mileage']); ?> km
                                            </small>
                                        </div>
                                        <i class="fas fa-chevron-down text-muted"></i>
                                    </div>
                                </div>
                                <div class="collapse" id="details-<?php echo $car['id']; ?>">
                                    <div class="car-details-area p-3">
                                        <div class="row g-2 mb-3 small">
                                            <div class="col-6"><strong>Manufacturer:</strong><br><?php echo htmlspecialchars($car['manufacturer']); ?></div>
                                            <div class="col-6"><strong>Plate:</strong><br><?php echo htmlspecialchars($car['plate']); ?></div>
                                            <div class="col-6"><strong>Type:</strong><br><?php echo htmlspecialchars($car['type']); ?></div>
                                            <div class="col-6"><strong>Mileage:</strong><br><?php echo number_format($car['mileage']); ?> km</div>
                                            <div class="col-12"><strong>Notes:</strong><br><?php echo htmlspecialchars($car['notes'] ?? 'No additional information'); ?></div>
                                        </div>
                                        <form method="POST" action="rent_car.php">
                                            <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                                            <button type="submit" class="btn btn-success w-100"><i class="fas fa-key me-1"></i>Rent This Car</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Currently Rented -->
        <section class="card section-card mb-4 fade-in-up">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="fas fa-car-alt text-success me-2"></i>Currently Rented</h2>
                <span class="badge bg-success rounded-pill"><?php echo count($current_rentals); ?> rented</span>
            </div>
            <div class="card-body">
                <?php if (empty($current_rentals)): ?>
                    <p class="text-muted text-center py-4"><i class="fas fa-car-side fs-1 d-block mb-2 opacity-25"></i>No cars rented right now.</p>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($current_rentals as $car): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card car-card border-success h-100">
                                <div class="card-body p-3" data-bs-toggle="collapse" data-bs-target="#details-r<?php echo $car['id']; ?>">
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($car['photo'])): ?>
                                            <img src="<?php echo htmlspecialchars($car['photo']); ?>" class="car-thumb me-3" alt="Car">
                                        <?php else: ?>
                                            <div class="car-thumb-placeholder me-3"><i class="fas fa-car"></i></div>
                                        <?php endif; ?>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h6>
                                            <small class="text-muted">Rented: <?php echo date('M j, Y', strtotime($car['rented_at'])); ?></small>
                                        </div>
                                        <i class="fas fa-chevron-down text-muted"></i>
                                    </div>
                                </div>
                                <div class="collapse" id="details-r<?php echo $car['id']; ?>">
                                    <div class="car-details-area p-3">
                                        <div class="row g-2 mb-3 small">
                                            <div class="col-6"><strong>Manufacturer:</strong><br><?php echo htmlspecialchars($car['manufacturer']); ?></div>
                                            <div class="col-6"><strong>Plate:</strong><br><?php echo htmlspecialchars($car['plate']); ?></div>
                                            <div class="col-6"><strong>Type:</strong><br><?php echo htmlspecialchars($car['type']); ?></div>
                                            <div class="col-6"><strong>Mileage:</strong><br><?php echo number_format($car['mileage']); ?> km</div>
                                            <div class="col-12"><strong>Notes:</strong><br><?php echo htmlspecialchars($car['notes'] ?? 'No additional information'); ?></div>
                                        </div>
                                        <form method="POST" action="release_car.php">
                                            <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                                            <button type="submit" class="btn btn-warning w-100"><i class="fas fa-undo me-1"></i>Release Car</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Rental History -->
        <section class="card section-card mb-4 fade-in-up">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="fas fa-history text-secondary me-2"></i>Rental History</h2>
                <span class="badge bg-secondary rounded-pill"><?php echo count($rental_history); ?> past</span>
            </div>
            <div class="card-body">
                <?php if (empty($rental_history)): ?>
                    <p class="text-muted text-center py-4"><i class="fas fa-history fs-1 d-block mb-2 opacity-25"></i>No rental history yet.</p>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($rental_history as $car): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card car-card h-100 opacity-75">
                                <div class="card-body p-3" data-bs-toggle="collapse" data-bs-target="#details-h<?php echo $car['id']; ?>-<?php echo strtotime($car['rented_at']); ?>">
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($car['photo'])): ?>
                                            <img src="<?php echo htmlspecialchars($car['photo']); ?>" class="car-thumb me-3" alt="Car">
                                        <?php else: ?>
                                            <div class="car-thumb-placeholder me-3"><i class="fas fa-car"></i></div>
                                        <?php endif; ?>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h6>
                                            <small class="text-muted d-block">Rented: <?php echo date('M j, Y', strtotime($car['rented_at'])); ?></small>
                                            <small class="text-muted">Returned: <?php echo date('M j, Y', strtotime($car['released_at'])); ?></small>
                                        </div>
                                        <i class="fas fa-chevron-down text-muted"></i>
                                    </div>
                                </div>
                                <div class="collapse" id="details-h<?php echo $car['id']; ?>-<?php echo strtotime($car['rented_at']); ?>">
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
