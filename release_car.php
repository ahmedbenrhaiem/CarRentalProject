<?php
// release_car.php
session_start();
require 'db/connection.php'; // Include database connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = $_POST['car_id'];
    $user_id = $_SESSION['user_id'];

    // Check if the car is rented by the user
    $stmt = $pdo->prepare("SELECT * FROM rentals WHERE car_id = ? AND user_id = ? AND status = 'active'");
    $stmt->execute([$car_id, $user_id]);
    $rental = $stmt->fetch();

    if ($rental) {
        // Update car status to 'available'
        $stmt = $pdo->prepare("UPDATE cars SET status = 'available' WHERE id = ?");
        $stmt->execute([$car_id]);

        // Update rental status to 'completed'
        $stmt = $pdo->prepare("UPDATE rentals SET status = 'completed' WHERE car_id = ? AND user_id = ?");
        $stmt->execute([$car_id, $user_id]);

        header("Location: user_dashboard.php?success=Car released successfully");
    } else {
        header("Location: user_dashboard.php?error=You have not rented this car");
    }
}