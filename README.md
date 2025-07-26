# Car Rental Project

A simple Car Rental Management System built for the "Speciality Classes" subject (Summer 2025).  
This project demonstrates user and admin functionalities for managing car rentals.

## Features

### **For All Users**
- Login system (predefined users in the database, no registration)
- View available cars
- Rent a car (moves from Available → Currently Rented)
- View currently rented cars
- View rental history
- Expand/collapse car details (Manufacturer, Brand, Model, Plate, Type, Fuel, Transmission, Mileage, Photo, Notes)

### **For Admin**
- Manage cars
- Add a new car with image upload
- Remove cars (only from available cars list)
- View all rented cars with username of the renter
- Expand/collapse car details

---

## Technologies Used
- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Server:** XAMPP (Apache + MySQL)

---

## Database Setup
1. Open **phpMyAdmin** in XAMPP.
2. Create a database named: CarRental

## Setup Instructions

1. Install XAMPP on your machine.
2. Clone the repository from GitHub, BitBucket, or GitLab.
3. Place the project folder in the `htdocs` directory of XAMPP.
4. Start XAMPP and enable Apache and MySQL.
5. Open phpMyAdmin and import the `sql/init.sql` file to initialize the database.
6. Access the project in your browser at http://localhost/CarRentalProject/login.php
