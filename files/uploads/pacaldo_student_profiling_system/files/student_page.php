<?php
// Start session to store user data
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

// Check if the user is logged in, redirect to login page if not
if (!isset($_SESSION['student_id'])) {
    header('Location: index.php');
    exit();
}

// Get student data from the database
$student_id = $_SESSION['student_id'];
$sql = "SELECT * FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
} else {
    echo "Student data not found.";
    exit();
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #4caf50;
            --secondary-color: #ffffff;
            --background-color: #f4f4f9;
            --dark-mode-bg: #1f1f1f;
            --dark-mode-text: #dcdcdc;
            --dark-mode-header-bg: #333;
            --light-mode-text: #000;
            --transition-speed: 0.3s;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--background-color);
            color: var(--light-mode-text);
            transition: background-color var(--transition-speed), color var(--transition-speed);
        }

        body.dark-mode {
            background-color: var(--dark-mode-bg);
            color: var(--dark-mode-text);
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background: rgb(121, 239, 180);
            color: var(--light-mode-text);
            position: fixed;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            z-index: 10;
            transition: background-color var(--transition-speed);
        }

        .sidebar.dark-mode {
            background: var(--dark-mode-header-bg);
            color: var(--dark-mode-text);
        }

        .sidebar .brand {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            justify-content: center; /* Center the text horizontally */
        }

        .sidebar .nav-link {
            color: inherit;
            padding: 15px;
            text-decoration: none;
            display: block;
            align-items: center;
            transition: background-color 0.3s;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
        }

        .sidebar .nav-link:hover {
            background-color: transparent;
            color: #fff;
        }

        .sidebar .dark-mode-toggle {
            text-align: center;
            padding: 20px;
        }

        .header {
            background: rgb(121, 239, 180);
            color: var(--light-mode-text);
            padding: 10px 20px;
            margin-left: 250px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color var(--transition-speed);
            font-family: 'Verdana', sans-serif;
            font-size: 1.2rem;
        }

        .header.dark-mode {
            background: var(--dark-mode-header-bg);
            color: var(--dark-mode-text);
        }

        .content {
            margin-left: 250px;
            padding: 20px;
            animation: fadeIn 1s;
        }

        .card {
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            background-color: var(--secondary-color);
        }

        .card.dark-mode {
            background-color: #444;
            color: var(--dark-mode-text);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgb(76, 21, 239);
            transition: transform 0.3s;
        }

        .profile-img:hover {
            transform: scale(1.1);
        }

        .info-table {
            text-align: left;
            margin: 20px auto;
            max-width: 600px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 10px 20px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 20px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 20px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: rgb(57, 24, 246);
        }

        input:checked + .slider:before {
            transform: translateX(40px);
        }

        .logout-btn,
        .edit-btn {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            width: 120px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-decoration: none; /* Remove underline */
        }

        /* Edit button hover effect (Red color) */
        .edit-btn:hover {
            background-color: red;
            color: white;
            transform: scale(1.05);
            animation: fadeInRed 0.3s ease-out;
        }

        /* Logout button hover effect */
        .logout-btn:hover {
            transform: scale(1.1) rotate(5deg);
            background-color: red;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            opacity: 0.8;
        }

        /* Focus effect for accessibility */
        .logout-btn:focus {
            outline: none;
            box-shadow: 0 0 0 4px red; /* Focus ring */
        }

        /* Animation for Edit Profile hover */
        @keyframes fadeInRed {
            0% {
                transform: scale(1);
            }
            100% {
                transform: scale(1.05);
            }
        }

        .fadeIn {
            animation: fadeIn 1s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .welcome-text {
            font-size: 1.5rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div>
            <!-- Remove the icon from the brand -->
            <div class="brand">
                Student Dashboard
            </div>
            <a href="#" class="nav-link"><i class="fas fa-eye"></i> View Student Information</a> <!-- Eye icon -->
            <a href="edit_profile.php" class="nav-link"><i class="fas fa-edit"></i> Edit Student Information</a>
        </div>
        <div class="dark-mode-toggle">
            <label for="darkModeToggle" id="darkModeLabel">Dark Mode</label>
            <label class="switch">
                <input type="checkbox" id="darkModeToggle">
                <span class="slider"></span>
            </label>
        </div>
    </div>

    <!-- Header -->
    <div class="header" id="header">
        <div class="welcome-text">Welcome, <?php echo htmlspecialchars($student['firstname']); ?>!</div>
        <a href="logout.php" class="logout-btn">Logout</a> <!-- Logout button without underline -->
    </div>

    <!-- Content -->
    <div class="content" id="content">
        <div class="card" id="card">
            <div class="card-body text-center">
                <h3>Student Information</h3>
                <?php if ($student['profile_picture']): ?>
                    <img src="<?php echo htmlspecialchars($student['profile_picture']); ?>" alt="Profile Picture" class="profile-img">
                <?php else: ?>
                    <img src="default-avatar.png" alt="Default Avatar" class="profile-img">
                <?php endif; ?>
                <table class="info-table">
                    <tr><td><strong>ID:</strong></td><td><?php echo htmlspecialchars($student['id']); ?></td></tr>
                    <tr><td><strong>Email:</strong></td><td><?php echo htmlspecialchars($student['email']); ?></td></tr>
                    <tr><td><strong>First Name:</strong></td><td><?php echo htmlspecialchars($student['firstname']); ?></td></tr>
                    <tr><td><strong>Last Name:</strong></td><td><?php echo htmlspecialchars($student['lastname']); ?></td></tr>
                    <tr><td><strong>Gender:</strong></td><td><?php echo htmlspecialchars($student['gender']); ?></td></tr>
                    <tr><td><strong>Role:</strong></td><td><?php echo htmlspecialchars($student['role']); ?></td></tr>
                    <tr><td><strong>Year:</strong></td><td><?php echo htmlspecialchars($student['year']); ?></td></tr>
                    <tr><td><strong>Section:</strong></td><td><?php echo htmlspecialchars($student['section']); ?></td></tr>
                </table>
                <a href="edit_profile.php" class="btn edit-btn">Edit Profile</a>
            </div>
        </div>
    </div>

    <script>
        const darkModeToggle = document.getElementById('darkModeToggle');
        const body = document.body;
        const sidebar = document.getElementById('sidebar');
        const header = document.getElementById('header');
        const content = document.getElementById('content');
        const card = document.getElementById('card');
        const darkModeLabel = document.getElementById('darkModeLabel');

        // Check for saved dark mode preference in localStorage
        if (localStorage.getItem('darkMode') === 'enabled') {
            body.classList.add('dark-mode');
            sidebar.classList.add('dark-mode');
            header.classList.add('dark-mode');
            content.classList.add('dark-mode');
            card.classList.add('dark-mode'); 
            darkModeLabel.textContent = 'Light Mode'; 
        }

        // Toggle dark mode
        darkModeToggle.addEventListener('change', () => {
            if (darkModeToggle.checked) {
                body.classList.add('dark-mode');
                sidebar.classList.add('dark-mode');
                header.classList.add('dark-mode');
                content.classList.add('dark-mode');
                card.classList.add('dark-mode');
                localStorage.setItem('darkMode', 'enabled');
                darkModeLabel.textContent = 'Light Mode';
            } else {
                body.classList.remove('dark-mode');
                sidebar.classList.remove('dark-mode');
                header.classList.remove('dark-mode');
                content.classList.remove('dark-mode');
                card.classList.remove('dark-mode');
                localStorage.setItem('darkMode', 'disabled');
                darkModeLabel.textContent = 'Dark Mode';
            }
        });
    </script>
</body>
</html>
