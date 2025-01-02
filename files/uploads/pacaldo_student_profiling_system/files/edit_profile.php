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

// Update student information if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $role = $_POST['role'];
    $year = $_POST['year'];
    $section = $_POST['section'];

    // Handle Profile Picture Upload
    if ($_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES['profile_picture']['name']);
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $profile_picture = $target_file;
        } else {
            $profile_picture = $student['profile_picture']; // Keep old picture if upload fails
        }
    } else {
        $profile_picture = $student['profile_picture']; // Keep old picture if no new one is uploaded
    }

    // Update the student information in the database
    $update_sql = "UPDATE students SET firstname = ?, lastname = ?, email = ?, gender = ?, role = ?, year = ?, section = ?, profile_picture = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssssssi", $firstname, $lastname, $email, $gender, $role, $year, $section, $profile_picture, $student_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Reload the updated student data
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student Information</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        body.dark-mode {
            background-color: #121212;
            color: #fff;
        }

        .sidebar {
            background-color: #28a745;
            color: #000;
            min-height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: background-color 0.3s ease;
            padding-top: 20px;
        }

        .sidebar.dark-mode {
            background-color: #333;
            color: #fff;
        }

        .sidebar .navbar-brand {
            text-align: center;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
        }

        .sidebar .navbar-brand i {
            margin-right: 10px;
        }

        .sidebar .nav-link {
            color: #000;
            padding: 12px 20px;
            width: 100%;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
        }

        .sidebar.dark-mode .nav-link {
            color: #fff;
        }

        .sidebar .nav-link:hover {
            background-color: #218838;
            color: #fff;
        }

        .dark-mode-switch {
            margin-top: auto;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .dark-mode-switch label {
            display: flex;
            align-items: center;
            font-size: 14px;
        }

        .dark-mode-switch input {
            width: 50px;
            height: 25px;
            position: relative;
            border-radius: 25px;
            background-color: #ccc;
            -webkit-appearance: none;
            appearance: none;
            margin-left: 10px;
            cursor: pointer;
        }

        .dark-mode-switch input:checked {
            background-color: rgb(58, 92, 205);
        }

        .dark-mode-switch input:before {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: white;
            transition: transform 0.3s;
        }

        .dark-mode-switch input:checked:before {
            transform: translateX(25px);
        }

        .header {
            background-color: #28a745;
            color: white;
            margin-left: 250px;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s;
        }

        .header.dark-mode {
            background-color: #121212;
        }

        .header .btn {
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            background-color: rgb(58, 92, 205);
            transition: all 0.3s ease;
            box-shadow: none;
            border: none;
            color: #fff;
        }

        .header .btn:hover {
            background-color: rgb(30, 147, 139);
            transform: scale(1.05);
        }

        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .profile-picture {
            text-align: center;
        }

        .profile-picture img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 2px solid #28a745;
            margin-bottom: 15px;
        }

        .form-row {
            display: flex;
            justify-content: space-between;
        }

        .form-group {
            flex: 1;
            margin: 0 10px;
        }

        .form-group label {
            font-weight: bold;
        }

        .form-control {
            max-width: 100%;
        }

        body.dark-mode .form-control {
            background-color: #333;
            color: #fff;
            border: 1px solid #555;
        }

        body.dark-mode .form-control:focus {
            background-color: #444;
            border-color: rgb(78, 110, 240);
            color: #fff;
        }

        body.dark-mode .btn-submit {
            background-color: rgb(62, 112, 238);
            color: #fff;
        }

        body.dark-mode label {
            color: #ccc;
        }

        .btn-submit {
            display: block;
            margin: 20px auto;
            transition: all 0.3s ease;
            font-size: 18px;
            padding: 12px 30px;
            border-radius: 5px;
            background-color: #28a745;
        }

        .btn-submit:hover {
            transform: scale(1.1);
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        .content {
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .sidebar .nav-link i {
            transition: color 0.3s ease;
        }

        .sidebar.dark-mode .nav-link i {
            color: white;
        }

        .sidebar .nav-link i {
            color: black;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <nav>
            <a class="navbar-brand"><i class="fas fa-user"></i> Student Menu</a>
            <a class="nav-link" href="student_page.php"><i class="fas fa-eye"></i> View Student Information</a>
            <a class="nav-link" href="#"><i class="fas fa-edit"></i> Edit Student Information</a>
        </nav>

        <!-- Dark Mode Toggle -->
        <div class="dark-mode-switch">
            <label>
                <span id="modeLabel">Light Mode</span>
                <input type="checkbox" id="darkModeToggle">
            </label>
        </div>
    </div>

    <!-- Header -->
    <div class="header" id="header">
        <div class="header-title">
            Welcome, <?php echo htmlspecialchars($student['firstname']); ?>!
        </div>
        <a href="logout.php" class="btn">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h2>Edit Student Information</h2>
        <div class="profile-picture">
            <img src="<?php echo htmlspecialchars($student['profile_picture']); ?>" alt="Profile Picture">
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="firstname">First Name</label>
                    <input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo htmlspecialchars($student['firstname']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="lastname">Last Name</label>
                    <input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo htmlspecialchars($student['lastname']); ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select class="form-control" id="gender" name="gender" required>
                        <option value="Male" <?php if ($student['gender'] === 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if ($student['gender'] === 'Female') echo 'selected'; ?>>Female</option>
                        <option value="Other" <?php if ($student['gender'] === 'Other') echo 'selected'; ?>>Other</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="role">Role</label>
                    <input type="text" class="form-control" id="role" name="role" value="<?php echo htmlspecialchars($student['role']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="year">Year</label>
                    <select class="form-control" id="year" name="year" required>
                        <option value="1st year" <?php if ($student['year'] === '1st year') echo 'selected'; ?>>1st Year</option>
                        <option value="2nd year" <?php if ($student['year'] === '2nd year') echo 'selected'; ?>>2nd Year</option>
                        <option value="3rd year" <?php if ($student['year'] === '3rd year') echo 'selected'; ?>>3rd Year</option>
                        <option value="4th year" <?php if ($student['year'] === '4th year') echo 'selected'; ?>>4th Year</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="section">Section</label>
                    <select class="form-control" id="section" name="section" required>
                        <option value="A" <?php if ($student['section'] === 'A') echo 'selected'; ?>>Section A</option>
                        <option value="B" <?php if ($student['section'] === 'B') echo 'selected'; ?>>Section B</option>
                        <option value="C" <?php if ($student['section'] === 'C') echo 'selected'; ?>>Section C</option>
                        <option value="D" <?php if ($student['section'] === 'D') echo 'selected'; ?>>Section D</option>
                        <option value="E" <?php if ($student['section'] === 'E') echo 'selected'; ?>>Section E</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="profile_picture">Profile Picture</label>
                <input type="file" class="form-control" id="profile_picture" name="profile_picture">
            </div>
            <button type="submit" class="btn btn-submit">Save Changes</button>
        </form>
    </div>

    <script>
        // Dark Mode Toggle
const darkModeToggle = document.getElementById('darkModeToggle');
const body = document.body;
const header = document.getElementById('header');
const sidebar = document.querySelector('.sidebar');
const modeLabel = document.getElementById('modeLabel');
const sidebarLinks = document.querySelectorAll('.sidebar .nav-link i');
const studentMenuText = document.querySelector('.sidebar .navbar-brand');

// Check if dark mode is stored in localStorage and apply it
if (localStorage.getItem('darkMode') === 'enabled') {
    body.classList.add('dark-mode');
    header.classList.add('dark-mode');
    sidebar.classList.add('dark-mode');
    modeLabel.textContent = 'Dark Mode';
    sidebarLinks.forEach(icon => icon.style.color = 'white');
    studentMenuText.style.color = 'white';
    darkModeToggle.checked = true;
} else {
    body.classList.remove('dark-mode');
    header.classList.remove('dark-mode');
    sidebar.classList.remove('dark-mode');
    modeLabel.textContent = 'Light Mode';
    sidebarLinks.forEach(icon => icon.style.color = 'black');
    studentMenuText.style.color = 'black';
    darkModeToggle.checked = false;
}

// Apply dark mode on toggle
darkModeToggle.addEventListener('change', () => {
    if (darkModeToggle.checked) {
        body.classList.add('dark-mode');
        header.classList.add('dark-mode');
        sidebar.classList.add('dark-mode');
        modeLabel.textContent = 'Dark Mode';
        sidebarLinks.forEach(icon => icon.style.color = 'white');
        studentMenuText.style.color = 'white';
        localStorage.setItem('darkMode', 'enabled');
    } else {
        body.classList.remove('dark-mode');
        header.classList.remove('dark-mode');
        sidebar.classList.remove('dark-mode');
        modeLabel.textContent = 'Light Mode';
        sidebarLinks.forEach(icon => icon.style.color = 'black');
        studentMenuText.style.color = 'black';
        localStorage.setItem('darkMode', 'disabled');
    }
});

    </script>
</body>
</html>