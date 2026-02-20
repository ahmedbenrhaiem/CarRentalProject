# Car Rental System

A web-based car rental management system built with PHP, MySQL, HTML, CSS (Bootstrap 5), and JavaScript.

## Features

### User Features
- User registration with email activation link
- Login / Logout
- View available cars, currently rented cars, and rental history
- Rent and release cars
- Expandable car detail rows (manufacturer, brand, model, plate, type, fuel, transmission, mileage, photo, notes)
- Edit profile (name, address, phone, etc.)
- Change password (old password x2, new password x2 verification)
- Change email (requires new activation link)
- Delete account (prevented if active rentals exist)
- Forgot password / Reset password via token link

### Admin Features
- Admin dashboard with fleet statistics (available, rented, total)
- Add new cars with photo upload
- Remove cars (only if not currently rented)
- View rented cars with renter username
- Expandable car details (same as user)
- User management panel (view all users, edit, delete)
- Statistics: Most popular cars sorted by rental count (GROUP BY + COUNT + ORDER BY DESC)
- Statistics: Top users sorted by rental count (GROUP BY + COUNT + ORDER BY DESC)

### Technical
- Bootstrap 5 responsive UI (mobile, tablet, desktop)
- Semantic HTML5 (nav, main, section, header, footer)
- Viewport meta tag on every page
- Password hashing (bcrypt via password_hash)
- Prepared statements (SQL injection prevention)
- Token-based activation and password reset

## Technology Stack
- **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend:** PHP 8+
- **Database:** MySQL / MariaDB
- **Server:** Apache (XAMPP)
- **Icons:** Font Awesome 6

---

## Installation Guide (XAMPP)

### Step 1: Install XAMPP
1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP on your computer
3. Start **Apache** and **MySQL** from the XAMPP Control Panel

### Step 2: Clone the Project from GitHub
1. Open a terminal (Command Prompt on Windows, Terminal on macOS/Linux)
2. Navigate to the XAMPP htdocs folder:
   - **Windows:** `cd C:\xampp\htdocs`
   - **macOS:** `cd /Applications/XAMPP/xamppfiles/htdocs`
   - **Linux:** `cd /opt/lampp/htdocs`
3. Clone the repository:
   ```
   git clone https://github.com/ahmedbenrhaiem/CarRentalProject.git
   ```
4. This will create a `CarRentalProject` folder inside htdocs with all project files.

### Step 3: Create and Import the Database
1. Open phpMyAdmin: `http://localhost/phpmyadmin/`
2. Click **"New"** in the left panel
3. Enter database name: `CarRental`
4. Click **"Create"**
5. Select the `CarRental` database
6. Go to **"Import"** tab
7. Click **"Choose File"** → select `sql/init.sql` → click **"Go"**
8. After import completes, go to **"Import"** again
9. Click **"Choose File"** → select `sql/migrate_semester2.sql` → click **"Go"**

### Step 4: Verify Database Connection
Open `db/connection.php` and verify these settings match your XAMPP:
```php
$host = 'localhost';
$dbname = 'CarRental';
$username = 'root';
$password = '';
```
Note: XAMPP default has username `root` with empty password.

### Step 5: Access the Project
1. Open browser
2. Go to: `http://localhost/CarRentalProject/login.php`

### Step 6: Test Login
| Username | Password   | Role  |
|----------|------------|-------|
| admin    | admin      | Admin |
| user1    | password1  | User  |


### Step 7: Test Registration
1. Click **"Create New Account"** on login page
2. Fill in the registration form
3. Click the **activation link** shown on screen
4. Log in with your new account

---

## Project Structure
```
CarRentalProject/
├── css/custom.css             - Custom styles (on top of Bootstrap 5)
├── db/connection.php          - Database connection (PDO)
├── images/cars/               - Car photos (jpg/png)
├── sql/
│   ├── init.sql               - Initial database (tables + sample data)
│   └── migrate_semester2.sql  - Semester 2 updates (new columns + sample history)
├── login.php                  - Login page (entry point)
├── register.php               - User registration + activation link
├── activate.php               - Account and email activation endpoint
├── forgot_password.php        - Request password reset link
├── reset_password.php         - Set new password via token
├── user_dashboard.php         - User: available cars, rented, history
├── profile.php                - User: edit profile, change password/email, delete account
├── rent_car.php               - Rent a car (POST action)
├── release_car.php            - Release a car (POST action)
├── admin.php                  - Admin: dashboard, add/remove cars, view rentals
├── add_car.php                - Admin: add car (POST action)
├── admin_users.php            - Admin: view/edit/delete users
├── admin_stats.php            - Admin: popular cars + top users statistics
├── logout.php                 - Logout (POST action)
└── README.md                  - This file
```

## Notes
- Activation and password reset links are shown on-screen (in production these would be emailed)
- The system supports both legacy plaintext and bcrypt-hashed passwords
- Car photos are stored in `images/cars/`

## Author
Student Project - Specialty Classes, Winter 2025/2026
Akademia Techniczno-Informatyczna w Naukach Stosowanych, Wroclaw
