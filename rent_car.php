<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if user is not admin (only regular users can rent cars)
if ($_SESSION['role'] === 'admin') {
    header("Location: admin.php");
    exit;
}

require 'db/connection.php';

// Handle GET request (rent car via URL parameter)
if (isset($_GET['car_id'])) {
    $car_id = (int)$_GET['car_id'];
    $user_id = $_SESSION['user_id'];
    
    try {
        // Begin transaction for data consistency
        $pdo->beginTransaction();
        
        // Check if the car is available (not currently rented)
        $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = :car_id AND is_rented = FALSE");
        $stmt->execute(['car_id' => $car_id]);
        $car = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($car) {
            // Check if user already has this car rented (double-check)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM rentals WHERE car_id = :car_id AND user_id = :user_id AND released_at IS NULL");
            $stmt->execute(['car_id' => $car_id, 'user_id' => $user_id]);
            $already_rented = $stmt->fetchColumn() > 0;
            
            if ($already_rented) {
                $pdo->rollBack();
                header("Location: user_dashboard.php?error=" . urlencode("You have already rented this car!"));
                exit;
            }
            
            // Update car status to rented
            $stmt = $pdo->prepare("UPDATE cars SET is_rented = TRUE WHERE id = :car_id");
            $stmt->execute(['car_id' => $car_id]);
            
            // Add entry to rentals table
            $stmt = $pdo->prepare("INSERT INTO rentals (car_id, user_id, rented_at) VALUES (:car_id, :user_id, NOW())");
            $stmt->execute(['car_id' => $car_id, 'user_id' => $user_id]);
            
            // Commit transaction
            $pdo->commit();
            
            $car_name = $car['brand'] . ' ' . $car['model'];
            header("Location: user_dashboard.php?success=" . urlencode("🎉 Successfully rented {$car_name}!"));
            exit;
            
        } else {
            $pdo->rollBack();
            header("Location: user_dashboard.php?error=" . urlencode("❌ Car is not available for rent!"));
            exit;
        }
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Rent car error: " . $e->getMessage());
        header("Location: user_dashboard.php?error=" . urlencode("❌ Database error occurred. Please try again."));
        exit;
    }
}

// Handle POST request (if coming from a form)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['car_id'])) {
    $car_id = (int)$_POST['car_id'];
    $user_id = $_SESSION['user_id'];
    
    try {
        // Begin transaction for data consistency
        $pdo->beginTransaction();
        
        // Check if the car is available
        $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = :car_id AND is_rented = FALSE");
        $stmt->execute(['car_id' => $car_id]);
        $car = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($car) {
            // Check if user already has this car rented
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM rentals WHERE car_id = :car_id AND user_id = :user_id AND released_at IS NULL");
            $stmt->execute(['car_id' => $car_id, 'user_id' => $user_id]);
            $already_rented = $stmt->fetchColumn() > 0;
            
            if ($already_rented) {
                $pdo->rollBack();
                header("Location: user_dashboard.php?error=" . urlencode("You have already rented this car!"));
                exit;
            }
            
            // Update car status to rented
            $stmt = $pdo->prepare("UPDATE cars SET is_rented = TRUE WHERE id = :car_id");
            $stmt->execute(['car_id' => $car_id]);
            
            // Add entry to rentals table
            $stmt = $pdo->prepare("INSERT INTO rentals (car_id, user_id, rented_at) VALUES (:car_id, :user_id, NOW())");
            $stmt->execute(['car_id' => $car_id, 'user_id' => $user_id]);
            
            // Commit transaction
            $pdo->commit();
            
            $car_name = $car['brand'] . ' ' . $car['model'];
            header("Location: user_dashboard.php?success=" . urlencode("🎉 Successfully rented {$car_name}!"));
            exit;
            
        } else {
            $pdo->rollBack();
            header("Location: user_dashboard.php?error=" . urlencode("❌ Car is not available for rent!"));
            exit;
        }
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Rent car error: " . $e->getMessage());
        header("Location: user_dashboard.php?error=" . urlencode("❌ Database error occurred. Please try again."));
        exit;
    }
}

// If no valid request, redirect back to dashboard
header("Location: user_dashboard.php?error=" . urlencode("❌ Invalid request!"));
exit;
?>