<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}
require 'db/connection.php';

$user_id = $_SESSION['user_id'];

// Fetch available cars
$stmt = $pdo->prepare("SELECT * FROM cars WHERE is_rented = 0");
$stmt->execute();
$available_cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch currently rented cars by this user
$stmt = $pdo->prepare("
    SELECT c.*, r.rented_at 
    FROM cars c 
    JOIN rentals r ON c.id = r.car_id 
    WHERE r.user_id = ? AND r.released_at IS NULL
");
$stmt->execute([$user_id]);
$current_rentals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch rental history
$stmt = $pdo->prepare("
    SELECT c.*, r.rented_at, r.released_at 
    FROM cars c 
    JOIN rentals r ON c.id = r.car_id 
    WHERE r.user_id = ? AND r.released_at IS NOT NULL
    ORDER BY r.released_at DESC
");
$stmt->execute([$user_id]);
$rental_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get username
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Car Rental</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-car"></i>
                <span>CarRental Pro</span>
            </div>
            <div class="nav-user">
                <span class="welcome-text">Welcome, <?php echo htmlspecialchars($user['username']); ?></span>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Available Cars Section -->
        <section class="car-section">
            <div class="section-header">
                <h2><i class="fas fa-car-side"></i> Available Cars for Rent</h2>
                <span class="car-count"><?php echo count($available_cars); ?> cars available</span>
            </div>
            
            <div class="cars-grid">
                <?php foreach ($available_cars as $car): ?>
                <div class="car-card" data-car-id="<?php echo $car['id']; ?>">
                    <div class="car-card-header" onclick="toggleCarDetails(<?php echo $car['id']; ?>)">
                        <div class="car-image">
                            <?php if (!empty($car['photo'])): ?>
                                <img src="<?php echo htmlspecialchars($car['photo']); ?>" alt="Car Image">
                            <?php else: ?>
                                <div class="car-placeholder">
                                    <i class="fas fa-car"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="car-basic-info">
                            <h3><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h3>
                            <div class="car-specs">
                                <span class="spec"><i class="fas fa-cog"></i> <?php echo htmlspecialchars($car['transmission']); ?></span>
                                <span class="spec"><i class="fas fa-gas-pump"></i> <?php echo htmlspecialchars($car['fuel']); ?></span>
                                <span class="spec"><i class="fas fa-tachometer-alt"></i> <?php echo number_format($car['mileage']); ?> km</span>
                            </div>
                        </div>
                        <div class="expand-icon">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    
                    <div class="car-details" id="details-<?php echo $car['id']; ?>">
                        <div class="details-grid">
                            <div class="detail-item">
                                <label>Manufacturer:</label>
                                <span><?php echo htmlspecialchars($car['manufacturer']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Registration:</label>
                                <span><?php echo htmlspecialchars($car['plate']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Type:</label>
                                <span><?php echo htmlspecialchars($car['type']); ?></span>
                            </div>
                            <div class="detail-item full-width">
                                <label>Additional Info:</label>
                                <p><?php echo htmlspecialchars($car['notes'] ?? 'No additional information'); ?></p>
                            </div>
                        </div>
                        <form method="POST" action="rent_car.php" class="rent-form">
                            <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                            <button type="submit" class="btn btn-rent">
                                <i class="fas fa-key"></i> Rent This Car
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Currently Rented Cars Section -->
        <section class="car-section">
            <div class="section-header">
                <h2><i class="fas fa-car-alt"></i> Currently Rented Cars</h2>
                <span class="car-count"><?php echo count($current_rentals); ?> cars rented</span>
            </div>
            
            <div class="cars-grid">
                <?php if (empty($current_rentals)): ?>
                    <div class="empty-state">
                        <i class="fas fa-car-side"></i>
                        <p>You don't have any rented cars at the moment</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($current_rentals as $car): ?>
                    <div class="car-card rented" data-car-id="<?php echo $car['id']; ?>">
                        <div class="car-card-header" onclick="toggleCarDetails(<?php echo $car['id']; ?>)">
                        <div class="car-image">
                            <?php if (!empty($car['photo'])): ?>
                                <img src="<?php echo htmlspecialchars($car['photo']); ?>" alt="Car Image">
                            <?php else: ?>
                                <div class="car-placeholder">
                                    <i class="fas fa-car"></i>
                                </div>
                            <?php endif; ?>
                            </div>
                            <div class="car-basic-info">
                                <h3><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h3>
                                <div class="rental-info">
                                    <span class="rental-date">Rented: <?php echo date('M j, Y', strtotime($car['rented_at'])); ?></span>
                                </div>
                                <div class="car-specs">
                                    <span class="spec"><i class="fas fa-cog"></i> <?php echo htmlspecialchars($car['transmission']); ?></span>
                                    <span class="spec"><i class="fas fa-gas-pump"></i> <?php echo htmlspecialchars($car['fuel']); ?></span>
                                </div>
                            </div>
                            <div class="expand-icon">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        
                        <div class="car-details" id="details-<?php echo $car['id']; ?>">
                            <div class="details-grid">
                                <div class="detail-item">
                                    <label>Manufacturer:</label>
                                    <span><?php echo htmlspecialchars($car['manufacturer']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Registration:</label>
                                    <span><?php echo htmlspecialchars($car['plate']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Type:</label>
                                    <span><?php echo htmlspecialchars($car['type']); ?></span>
                                </div>
                                <div class="detail-item full-width">
                                    <label>Additional Info:</label>
                                    <p><?php echo htmlspecialchars($car['notes'] ?? 'No additional information'); ?></p>
                                </div>
                            </div>
                            <form method="POST" action="release_car.php" class="release-form">
                                <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                                <button type="submit" class="btn btn-release">
                                    <i class="fas fa-undo"></i> Release Car
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Rental History Section -->
        <section class="car-section">
            <div class="section-header">
                <h2><i class="fas fa-history"></i> Rental History</h2>
                <span class="car-count"><?php echo count($rental_history); ?> past rentals</span>
            </div>
            
            <div class="cars-grid">
                <?php if (empty($rental_history)): ?>
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <p>No rental history yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($rental_history as $car): ?>
                    <div class="car-card history" data-car-id="<?php echo $car['id']; ?>">
                        <div class="car-card-header" onclick="toggleCarDetails(<?php echo $car['id']; ?>)">
                        <div class="car-image">
                            <?php if (!empty($car['photo'])): ?>
                                <img src="<?php echo htmlspecialchars($car['photo']); ?>" alt="Car Image">
                            <?php else: ?>
                                <div class="car-placeholder">
                                    <i class="fas fa-car"></i>
                                </div>
                            <?php endif; ?>
                            </div>
                            <div class="car-basic-info">
                                <h3><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h3>
                                <div class="rental-info">
                                    <span class="rental-date">Rented: <?php echo date('M j, Y', strtotime($car['rented_at'])); ?></span>
                                    <span class="return-date">Returned: <?php echo date('M j, Y', strtotime($car['released_at'])); ?></span>
                                </div>
                                <div class="car-specs">
                                    <span class="spec"><i class="fas fa-cog"></i> <?php echo htmlspecialchars($car['transmission']); ?></span>
                                    <span class="spec"><i class="fas fa-gas-pump"></i> <?php echo htmlspecialchars($car['fuel']); ?></span>
                                </div>
                            </div>
                            <div class="expand-icon">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        
                        <div class="car-details" id="details-<?php echo $car['id']; ?>">
                            <div class="details-grid">
                                <div class="detail-item">
                                    <label>Manufacturer:</label>
                                    <span><?php echo htmlspecialchars($car['manufacturer']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Registration:</label>
                                    <span><?php echo htmlspecialchars($car['plate']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Type:</label>
                                    <span><?php echo htmlspecialchars($car['type']); ?></span>
                                </div>
                                <div class="detail-item full-width">
                                    <label>Additional Info:</label>
                                    <p><?php echo htmlspecialchars($car['notes'] ?? 'No additional information'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <script>
        function toggleCarDetails(carId) {
            const details = document.getElementById(`details-${carId}`);
            const card = document.querySelector(`[data-car-id="${carId}"]`);
            const icon = card.querySelector('.expand-icon i');
            
            if (details.style.maxHeight) {
                // Collapse
                details.style.maxHeight = null;
                details.classList.remove('expanded');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            } else {
                // Expand
                details.style.maxHeight = details.scrollHeight + "px";
                details.classList.add('expanded');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        }

        // Add smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.car-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('fade-in');
            });
        });
    </script>
    <script>
        function toggleCarDetails(carId) {
            const details = document.getElementById(`details-${carId}`);
            const card = document.querySelector(`[data-car-id="${carId}"]`);
            const icon = card.querySelector('.expand-icon i');

            if (details.classList.contains('expanded')) {
                details.classList.remove('expanded');
                details.style.maxHeight = null;
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            } else {
                details.classList.add('expanded');
                details.style.maxHeight = details.scrollHeight + "px";
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        }
    </script>
</body>
</html>