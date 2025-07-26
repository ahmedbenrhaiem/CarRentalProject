<?php
// add_car.php
session_start();
require 'db/connection.php'; // Include database connection

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required_fields = ['manufacturer', 'brand', 'model', 'plate', 'type', 'fuel', 'transmission', 'mileage'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            header("Location: admin.php?error=Missing required field: $field");
            exit;
        }
    }

    // Collect form data
    $manufacturer = $_POST['manufacturer'];
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $plate = $_POST['plate'];
    $type = $_POST['type'];
    $fuel = $_POST['fuel'];
    $transmission = $_POST['transmission'];
    $mileage = $_POST['mileage'];
    $notes = $_POST['notes'] ?? ''; // Optional field
    $status = 'available'; // Default status

    // Handle image upload
    $photo_path = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $image_tmp_name = $_FILES['photo']['tmp_name'];
        $image_name = basename($_FILES['photo']['name']);
        $photo_path = 'assets/images/cars' . $image_name;

        // Move the uploaded file to the assets/images directory
        if (!move_uploaded_file($image_tmp_name, $photo_path)) {
            header("Location: admin.php?error=Failed to upload image");
            exit;
        }
    } else {
        header("Location: admin.php?error=Image upload error");
        exit;
    }

    // Insert car details into the database
    try {
        $stmt = $pdo->prepare("INSERT INTO cars (manufacturer, brand, model, plate, type, fuel, transmission, mileage, photo_path, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$manufacturer, $brand, $model, $plate, $type, $fuel, $transmission, $mileage, $photo_path, $notes, $status]);

        header("Location: admin.php?success=Car added successfully");
    } catch (Exception $e) {
        header("Location: admin.php?error=Failed to add car: " . $e->getMessage());
    }
}
?>