<?php
/**
 * Student Registration System Database Schema
 * This file contains SQL statements to create all tables required for the Student Registration System
 */

// Database configuration
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'student_registration';

// Create a connection
$conn = mysqli_connect($dbHost, $dbUser, $dbPass);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbName";
if (mysqli_query($conn, $sql)) {
    echo "Database created successfully or already exists<br>";
} else {
    die("Error creating database: " . mysqli_error($conn));
}

// Select the database
mysqli_select_db($conn, $dbName);

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Table 'users' created successfully<br>";
} else {
    echo "Error creating table 'users': " . mysqli_error($conn) . "<br>";
}

// Create students table
$sql = "CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    address VARCHAR(200) NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql)) {
    echo "Table 'students' created successfully<br>";
} else {
    echo "Error creating table 'students': " . mysqli_error($conn) . "<br>";
}

// Create it_course table
$sql = "CREATE TABLE IF NOT EXISTS it_course (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    duration VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Table 'it_course' created successfully<br>";
} else {
    echo "Error creating table 'it_course': " . mysqli_error($conn) . "<br>";
}

// Create student_course table (association between students and courses)
$sql = "CREATE TABLE IF NOT EXISTS student_course (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES it_course(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id)
)";

if (mysqli_query($conn, $sql)) {
    echo "Table 'student_course' created successfully<br>";
} else {
    echo "Error creating table 'student_course': " . mysqli_error($conn) . "<br>";
}

// Create payments table
$sql = "CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_course_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_course_id) REFERENCES student_course(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql)) {
    echo "Table 'payments' created successfully<br>";
} else {
    echo "Error creating table 'payments': " . mysqli_error($conn) . "<br>";
}

// Create images table (for student profile pictures)
$sql = "CREATE TABLE IF NOT EXISTS images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    images VARCHAR(255) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql)) {
    echo "Table 'images' created successfully<br>";
} else {
    echo "Error creating table 'images': " . mysqli_error($conn) . "<br>";
}

// Create requests table
$sql = "CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    request_type ENUM('payment', 'academic', 'document', 'other') NOT NULL,
    subject VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    amount DECIMAL(10,2) DEFAULT 0,
    file VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql)) {
    echo "Table 'requests' created successfully<br>";
} else {
    echo "Error creating table 'requests': " . mysqli_error($conn) . "<br>";
}

// Create an admin user for first-time setup
$adminUsername = 'admin';
$adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
$adminRole = 'admin';

$checkAdmin = "SELECT * FROM users WHERE username = '$adminUsername'";
$result = mysqli_query($conn, $checkAdmin);

if (mysqli_num_rows($result) == 0) {
    $sql = "INSERT INTO users (username, password, role) VALUES ('$adminUsername', '$adminPassword', '$adminRole')";
    if (mysqli_query($conn, $sql)) {
        echo "Admin user created successfully with username: '$adminUsername' and password: 'admin123'<br>";
    } else {
        echo "Error creating admin user: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "Admin user already exists<br>";
}

// Insert sample course data
$courses = [
    ["Web Development", "3 months", 15000.00],
    ["Mobile App Development", "4 months", 18000.00],
    ["Data Science", "6 months", 25000.00],
    ["Cyber Security", "3 months", 20000.00],
    ["UI/UX Design", "2 months", 12000.00]
];

foreach ($courses as $course) {
    $title = $course[0];
    $duration = $course[1];
    $price = $course[2];
    
    $checkCourse = "SELECT * FROM it_course WHERE title = '$title'";
    $result = mysqli_query($conn, $checkCourse);
    
    if (mysqli_num_rows($result) == 0) {
        $sql = "INSERT INTO it_course (title, duration, price) VALUES ('$title', '$duration', $price)";
        if (mysqli_query($conn, $sql)) {
            echo "Course '$title' created successfully<br>";
        } else {
            echo "Error creating course '$title': " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "Course '$title' already exists<br>";
    }
}

// Close the connection
mysqli_close($conn);

echo "<h3>Database setup completed successfully!</h3>";
echo "<p>You can now use the Student Registration System.</p>";
echo "<p><a href='index.php'>Go to Homepage</a></p>";
?>