<?php
// Start the session
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sheila_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission (Login and Register)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    if ($action == 'login') {
        $email = $_POST['email']; 
        $password = $_POST['password'];

        // Check if student exists
        $sql = "SELECT * FROM students WHERE email = ? AND password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $password); 
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $role = $user['role'];

            // Store student ID in session if student
            $_SESSION['student_id'] = $user['id'];

            if ($role == 'Student') {
                header('Location: student_page.php');
                exit();
            }
        }

        // Check if admin exists
        $sql = "SELECT * FROM admin WHERE email = ? AND password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $password); 
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $role = $user['role'];

            // Store admin ID in session if admin
            $_SESSION['admin_id'] = $user['id'];

            if ($role == 'Admin') {
                header('Location: admin_dashboard.php');
                exit();
            }
        }

        echo "Invalid username or password.";
        $stmt->close();
    }

    if ($action == 'register') {
        $firstName = $_POST['first-name'];
        $lastName = $_POST['last-name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = isset($_POST['role']) ? $_POST['role'] : 'Student'; 
        $year = $_POST['year'];
        $section = $_POST['section'];
        $gender = $_POST['gender']; // Get the gender value from the form
        
        $profilePicture = null;

        if (!empty($_FILES['profile-picture']['name'])) {
            $targetDir = "uploads/";

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $targetFile = $targetDir . basename($_FILES['profile-picture']['name']);
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            $allowedFileTypes = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($imageFileType, $allowedFileTypes)) {
                if (move_uploaded_file($_FILES['profile-picture']['tmp_name'], $targetFile)) {
                    $profilePicture = $targetFile;
                } else {
                    echo "Error uploading the profile picture.";
                    $profilePicture = null;
                }
            } else {
                echo "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
                $profilePicture = null;
            }
        }

        if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($role) || empty($year) || empty($section) || empty($gender)) {
            echo "All fields are required.";
        } else {
            $sql = "SELECT * FROM students WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
    
            if ($result->num_rows > 0) {
                echo "Email is already registered.";
            } else {
                $sql = "INSERT INTO students (firstname, lastname, email, password, role, year, section, profile_picture, gender) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssss", $firstName, $lastName, $email, $password, $role, $year, $section, $profilePicture, $gender);
    
                if ($stmt->execute()) {
                    echo "<script>
                        alert('Registration successful! Please login to continue.');
                        document.addEventListener('DOMContentLoaded', function() {
                            toggleForm();
                        });
                    </script>";
                } else {
                    echo "Error: " . $stmt->error;
                }
            }
    
            $stmt->close();
        }
    }
}

$conn->close();
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BSIT Profiling System</title>
    <link rel="stylesheet" href="folders/css/index.css">
</head>
<body>
    <div class="card">
        <!-- Login Form -->
        <div class="form-section" id="login-section">
            <div class="title">BSIT Profiling System</div>
            <h2>Login</h2>
            <form id="login-form" action="index.php" method="POST">
                <input type="hidden" name="action" value="login">
                <div class="input-container">
                    <label for="email">Email:</label>
                    <input type="text" id="email" name="email" required>
                </div>
                <div class="input-container">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Login</button>
            </form>
            <p>Don't have an account? <a href="#" id="register-link" onclick="toggleForm()">Register</a></p>
        </div>

<!-- Register Form -->
<div class="form-section" id="register-section" style="display: none;">
    <div class="title">BSIT Profiling System</div>
    <h2>Register</h2>
    <form id="register-form" action="index.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="register">
        <div class="input-container">
            <label for="first-name">First Name:</label>
            <input type="text" id="first-name" name="first-name" required>
        </div>
        <div class="input-container">
            <label for="last-name">Last Name:</label>
            <input type="text" id="last-name" name="last-name" required>
        </div>
        <div class="input-container">
            <label for="gender">Gender:</label>
            <select id="gender" name="gender" required>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div class="input-container">
            <label for="email">Email:</label>
            <input type="text" id="email" name="email" required>
        </div>
        <div class="input-container">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="input-container">
            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <option value="Student">Student</option>
                <option value="Admin">Admin</option>
            </select>
        </div>
        <div class="input-container">
            <label for="year">Year:</label>
            <select id="year" name="year" required>
                <option value="1st-Year">1st-Year</option>
                <option value="2nd-Year">2nd-Year</option>
                <option value="3rd-Year">3rd-Year</option>
                <option value="4th-Year">4th-Year</option>
            </select>
        </div>
        <div class="input-container">
            <label for="section">Section:</label>
            <select id="section" name="section" required>
                <option value="Section A">Section A</option>
                <option value="Section B">Section B</option>
                <option value="Section C">Section C</option>
                <option value="Section D">Section D</option>
                <option value="Section E">Section E</option>
            </select>
        </div>
        <div class="input-container">
            <label for="profile-picture">Profile Picture:</label>
            <input type="file" id="profile-picture" name="profile-picture" accept="image/*">
        </div>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="#" id="login-link" onclick="toggleForm()">Login</a></p>
</div>


        <!-- Logo Section -->
        <div class="logo-section">
            <img src="folders/image/BSIT LOGO.jpg" alt="Logo">
        </div>
    </div>
    <script src="folders/js/index.js"></script>
</body>
</html>
