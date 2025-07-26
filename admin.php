<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: user_dashboard.php");
    exit;
}

require 'db/connection.php';

// Handle car removal (admin can remove cars that are not currently rented)
if (isset($_GET['remove_car']) && isset($_GET['car_id'])) {
    $car_id = (int)$_GET['car_id'];
    
    // Check if car is currently rented
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rentals WHERE car_id = :car_id AND released_at IS NULL");
    $stmt->execute(['car_id' => $car_id]);
    $is_rented = $stmt->fetchColumn() > 0;
    
    if (!$is_rented) {
        // Car is not rented, safe to remove
        $stmt = $pdo->prepare("DELETE FROM cars WHERE id = :car_id");
        $stmt->execute(['car_id' => $car_id]);
        $success_message = "Car removed successfully!";
    } else {
        $error_message = "Cannot remove car - it is currently rented!";
    }
}

// Fetch available cars (not currently rented)
$stmt = $pdo->prepare("SELECT * FROM cars WHERE is_rented = FALSE ORDER BY brand, model");
$stmt->execute();
$available_cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch rented cars with the username of the renter
$stmt = $pdo->prepare("SELECT cars.*, rentals.user_id, users.username, rentals.rented_at 
                       FROM cars
                       JOIN rentals ON cars.id = rentals.car_id
                       JOIN users ON rentals.user_id = users.id
                       WHERE rentals.released_at IS NULL
                       ORDER BY rentals.rented_at DESC");
$stmt->execute();
$rented_cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get admin username
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Car Rental</title>
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
                <span class="welcome-text">
                    Welcome, <?php echo htmlspecialchars($admin['username']); ?>
                    <span class="admin-badge">👑 Admin</span>
                </span>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Admin Statistics -->
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($available_cars); ?></div>
                <div class="stat-label">Available Cars</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($rented_cars); ?></div>
                <div class="stat-label">Currently Rented</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($available_cars) + count($rented_cars); ?></div>
                <div class="stat-label">Total Fleet</div>
            </div>
        </div>

        <!-- Add Car Section -->
        <section class="add-car-section">
            <div class="section-header">
                <h2><i class="fas fa-plus-circle"></i> Add New Car</h2>
            </div>
            
            <button class="btn btn-add" onclick="toggleAddCarForm()">
                <i class="fas fa-car"></i> Add a New Car
            </button>
            
            <div class="add-car-form" id="add-car-form">
                <form action="add_car.php" method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="manufacturer">
                                <i class="fas fa-industry"></i>
                                Manufacturer
                            </label>
                            <input type="text" id="manufacturer" name="manufacturer" 
                                   placeholder="e.g., Stellantis" required>
                        </div>

                        <div class="form-group">
                            <label for="brand">
                                <i class="fas fa-tag"></i>
                                Brand
                            </label>
                            <input type="text" id="brand" name="brand" 
                                   placeholder="e.g., Citroen" required>
                        </div>

                        <div class="form-group">
                            <label for="model">
                                <i class="fas fa-car-side"></i>
                                Model
                            </label>
                            <input type="text" id="model" name="model" 
                                   placeholder="e.g., C5X" required>
                        </div>

                        <div class="form-group">
                            <label for="plate">
                                <i class="fas fa-id-card"></i>
                                Registration Plate
                            </label>
                            <input type="text" id="plate" name="plate" 
                                   placeholder="e.g., DW12345" required>
                        </div>

                        <div class="form-group">
                            <label for="type">
                                <i class="fas fa-car"></i>
                                Type
                            </label>
                            <select id="type" name="type" required>
                                <option value="">Select type</option>
                                <option value="sedan">Sedan</option>
                                <option value="hatchback">Hatchback</option>
                                <option value="SUV">SUV</option>
                                <option value="coupe">Coupe</option>
                                <option value="convertible">Convertible</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="fuel">
                                <i class="fas fa-gas-pump"></i>
                                Fuel Type
                            </label>
                            <select id="fuel" name="fuel" required>
                                <option value="">Select fuel type</option>
                                <option value="gasoline">Gasoline</option>
                                <option value="diesel">Diesel</option>
                                <option value="hybrid">Hybrid</option>
                                <option value="electric">Electric</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="transmission">
                                <i class="fas fa-cog"></i>
                                Transmission
                            </label>
                            <select id="transmission" name="transmission" required>
                                <option value="">Select transmission</option>
                                <option value="manual">Manual</option>
                                <option value="automatic">Automatic</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="mileage">
                                <i class="fas fa-tachometer-alt"></i>
                                Mileage (km)
                            </label>
                            <input type="number" id="mileage" name="mileage" 
                                   placeholder="e.g., 53400" min="0" required>
                        </div>

                        <div class="form-group">
                            <label for="photo">
                                <i class="fas fa-camera"></i>
                                Car Photo
                            </label>
                            <input type="file" id="photo" name="photo" 
                                   accept="image/*" required>
                        </div>

                        <div class="form-group full-width">
                            <label for="notes">
                                <i class="fas fa-sticky-note"></i>
                                Notes
                            </label>
                            <textarea id="notes" name="notes" rows="3"
                                      placeholder="Additional information about the car..."></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="toggleAddCarForm()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus"></i> Add Car
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Available Cars Section -->
        <section class="car-section">
            <div class="section-header">
                <h2><i class="fas fa-car-side"></i> Available Cars for Rental</h2>
                <span class="car-count"><?php echo count($available_cars); ?> cars available</span>
            </div>
            
            <div class="cars-grid">
                <?php if (empty($available_cars)): ?>
                    <div class="empty-state">
                        <i class="fas fa-car-side"></i>
                        <p>No cars available for rental at the moment</p>
                    </div>
                <?php else: ?>
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
                            <button class="btn btn-remove" 
                                    onclick="confirmRemove(<?php echo $car['id']; ?>, '<?php echo htmlspecialchars($car['brand'] . ' ' . $car['model'], ENT_QUOTES); ?>')">
                                <i class="fas fa-trash"></i> Remove Car
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Currently Rented Cars Section -->
        <section class="car-section">
            <div class="section-header">
                <h2><i class="fas fa-key"></i> Currently Rented Cars</h2>
                <span class="car-count"><?php echo count($rented_cars); ?> cars rented</span>
            </div>
            
            <div class="cars-grid">
                <?php if (empty($rented_cars)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No cars are currently rented</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($rented_cars as $car): ?>
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
                                    <span class="renter-info">
                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($car['username']); ?>
                                    </span>
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
                                <div class="detail-item">
                                    <label>Mileage:</label>
                                    <span><?php echo number_format($car['mileage']); ?> km</span>
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

        function toggleAddCarForm() {
            const form = document.getElementById('add-car-form');
            const button = document.querySelector('.btn-add');
            
            if (form.classList.contains('show')) {
                form.classList.remove('show');
                button.innerHTML = '<i class="fas fa-car"></i> Add a New Car';
            } else {
                form.classList.add('show');
                button.innerHTML = '<i class="fas fa-times"></i> Cancel';
            }
        }

        function confirmRemove(carId, carName) {
            if (confirm(`Are you sure you want to remove "${carName}"? This action cannot be undone.`)) {
                window.location.href = `admin.php?remove_car=1&car_id=${carId}`;
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
</body>
</html>