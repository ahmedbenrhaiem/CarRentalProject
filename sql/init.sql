-- SQL script to initialize the database
CREATE DATABASE CarRental;
USE CarRental;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(50) NOT NULL,
    role ENUM('admin', 'user') NOT NULL
);

INSERT INTO users (username, password, role) VALUES
('admin', 'admin', 'admin'),
('user1', 'password1', 'user'),
('user2', 'password2', 'user'),
('user3', 'password3', 'user'),
('user4', 'password4', 'user'),
('user5', 'password5', 'user'),
('user6', 'password6', 'user'),
('user7', 'password7', 'user'),
('user8', 'password8', 'user'),
('user9', 'password9', 'user'),
('user10', 'password10', 'user');

-- Cars table
CREATE TABLE cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    manufacturer VARCHAR(50),
    brand VARCHAR(50),
    model VARCHAR(50),
    plate VARCHAR(20),
    type VARCHAR(20),
    fuel VARCHAR(20),
    transmission VARCHAR(20),
    mileage INT,
    photo VARCHAR(255),
    notes TEXT,
    is_rented BOOLEAN DEFAULT FALSE
);

INSERT INTO cars (manufacturer, brand, model, plate, type, fuel, transmission, mileage, photo, notes, is_rented) VALUES
('Stellantis', 'Citroen', 'C5X', 'DW12345', 'sedan', 'gasoline', 'automatic', 53400, 'images/cars/citroen_c5x.jpg', 'A comfortable sedan.', FALSE),
('Toyota', 'Corolla', '2020', 'DW54321', 'hatchback', 'hybrid', 'manual', 30000, 'images/cars/toyota_corolla.jpg', 'Reliable and efficient.', FALSE),
('Ford', 'Focus', '2019', 'DW67890', 'sedan', 'diesel', 'manual', 45000, 'images/cars/ford_focus.jpg', 'Compact and practical.', FALSE),
('Volkswagen', 'Golf', '2021', 'DW98765', 'hatchback', 'gasoline', 'automatic', 25000, 'images/cars/vw_golf.jpg', 'Sporty and stylish.', FALSE),
('Tesla', 'Model 3', '2022', 'DW54322', 'sedan', 'electric', 'automatic', 15000, 'images/cars/tesla_model3.jpg', 'Innovative and eco-friendly.', FALSE),
('BMW', 'X5', '2020', 'DW12346', 'SUV', 'diesel', 'automatic', 40000, 'images/cars/bmw_x5.jpg', 'Luxury and performance.', FALSE),
('Audi', 'A4', '2018', 'DW65432', 'sedan', 'gasoline', 'manual', 60000, 'images/cars/audi_a4.jpg', 'Premium comfort.', FALSE),
('Honda', 'Civic', '2019', 'DW76543', 'hatchback', 'hybrid', 'manual', 35000, 'images/cars/honda_civic.jpg', 'Reliable and efficient.', FALSE),
('Hyundai', 'Tucson', '2021', 'DW87654', 'SUV', 'gasoline', 'automatic', 20000, 'images/cars/hyundai_tucson.jpg', 'Spacious and versatile.', FALSE),
('Chevrolet', 'Malibu', '2020', 'DW43210', 'sedan', 'gasoline', 'automatic', 30000, 'images/cars/chevrolet_malibu.jpg', 'Smooth and stylish.', FALSE);

-- Rentals table
CREATE TABLE rentals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    rented_at DATETIME NOT NULL,
    released_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (car_id) REFERENCES cars(id)
);