<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit; }
require 'db/connection.php';

// Most popular cars
$popular_cars = $pdo->query("
    SELECT c.id, c.brand, c.model, c.manufacturer, c.photo, c.type, c.fuel,
           COUNT(r.id) as rental_count
    FROM cars c LEFT JOIN rentals r ON c.id = r.car_id
    GROUP BY c.id, c.brand, c.model, c.manufacturer, c.photo, c.type, c.fuel
    ORDER BY rental_count DESC
")->fetchAll();

// Top users
$top_users = $pdo->query("
    SELECT u.id, u.username, u.first_name, u.last_name, u.email,
           COUNT(r.id) as rental_count,
           SUM(CASE WHEN r.released_at IS NULL THEN 1 ELSE 0 END) as active_rentals
    FROM users u LEFT JOIN rentals r ON u.id = r.user_id
    WHERE u.role = 'user'
    GROUP BY u.id, u.username, u.first_name, u.last_name, u.email
    ORDER BY rental_count DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - Car Rental Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/custom.css">
</head>
<body class="page-dashboard">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="admin.php"><i class="fas fa-car me-2"></i>CarRental Pro</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="admin.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_users.php"><i class="fas fa-users me-1"></i>Users</a></li>
                    <li class="nav-item"><a class="nav-link active" href="admin_stats.php"><i class="fas fa-chart-bar me-1"></i>Statistics</a></li>
                    <li class="nav-item"><span class="nav-link text-light"><span class="admin-badge">Admin</span></span></li>
                    <li class="nav-item"><a class="nav-link text-warning" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        <h1 class="h3 fw-bold mb-4"><i class="fas fa-chart-bar text-primary me-2"></i>Statistics</h1>

        <!-- Most Popular Cars -->
        <section class="card section-card mb-4">
            <div class="card-header bg-white">
                <h2 class="h5 mb-0"><i class="fas fa-trophy text-warning me-2"></i>Most Popular Cars</h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Car</th><th>Manufacturer</th><th>Type</th><th>Fuel</th><th>Total Rentals</th><th>Popularity</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $rank = 1;
                            $max = !empty($popular_cars) ? max($popular_cars[0]['rental_count'], 1) : 1;
                            foreach ($popular_cars as $car):
                                $pct = ($car['rental_count'] / $max) * 100;
                            ?>
                            <tr>
                                <td>
                                    <?php if ($rank <= 3): echo ['','🥇','🥈','🥉'][$rank]; else: ?>
                                        <span class="text-muted fw-bold"><?php echo $rank; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($car['photo'])): ?>
                                            <img src="<?php echo htmlspecialchars($car['photo']); ?>" class="car-thumb me-2" alt="">
                                        <?php endif; ?>
                                        <strong><?php echo htmlspecialchars($car['brand'].' '.$car['model']); ?></strong>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($car['manufacturer']); ?></td>
                                <td><?php echo htmlspecialchars($car['type']); ?></td>
                                <td><?php echo htmlspecialchars($car['fuel']); ?></td>
                                <td><strong><?php echo $car['rental_count']; ?></strong></td>
                                <td>
                                    <div class="progress progress-thin">
                                        <div class="progress-bar" style="width:<?php echo $pct; ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php $rank++; endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Top Users -->
        <section class="card section-card mb-4">
            <div class="card-header bg-white">
                <h2 class="h5 mb-0"><i class="fas fa-users text-success me-2"></i>Top Users by Rentals</h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Username</th><th>Name</th><th>Email</th><th>Active</th><th>Total Rentals</th><th>Activity</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $rank = 1;
                            $maxu = !empty($top_users) ? max($top_users[0]['rental_count'], 1) : 1;
                            foreach ($top_users as $u):
                                $pct = ($u['rental_count'] / $maxu) * 100;
                            ?>
                            <tr>
                                <td>
                                    <?php if ($rank <= 3): echo ['','🥇','🥈','🥉'][$rank]; else: ?>
                                        <span class="text-muted fw-bold"><?php echo $rank; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars(($u['first_name']??'').' '.($u['last_name']??'')); ?></td>
                                <td><?php echo htmlspecialchars($u['email']??'-'); ?></td>
                                <td><?php echo $u['active_rentals']; ?></td>
                                <td><strong><?php echo $u['rental_count']; ?></strong></td>
                                <td>
                                    <div class="progress progress-thin">
                                        <div class="progress-bar bg-success" style="width:<?php echo $pct; ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php $rank++; endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-dark text-light text-center py-3 mt-4">
        <p class="mb-0">&copy; 2025 Car Rental System</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
