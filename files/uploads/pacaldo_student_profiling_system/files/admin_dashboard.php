<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sheila_db";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize counters
$total_students = 0;
$total_male = 0;
$total_female = 0;

// Fetch all students from the database
$sql = "SELECT id, profile_picture, firstname, lastname, email, role, year, section, gender FROM students";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $total_students++;
        if (strtolower($row['gender']) == 'male') {
            $total_male++;
        } elseif (strtolower($row['gender']) == 'female') {
            $total_female++;
        }
    }
}

// Update student record
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $role = $_POST['role'];
    $year = $_POST['year'];
    $section = $_POST['section'];
    $profile_picture = $_FILES['profile_picture']['name'];

    // Handle file upload
    if ($profile_picture) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($profile_picture);
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file);

        $update_sql = "UPDATE students SET 
                        firstname='$firstname', lastname='$lastname', 
                        email='$email', gender='$gender', 
                        role='$role', year='$year', 
                        section='$section', profile_picture='$profile_picture' 
                        WHERE id=$id";
    } else {
        $update_sql = "UPDATE students SET 
                        firstname='$firstname', lastname='$lastname', 
                        email='$email', gender='$gender', 
                        role='$role', year='$year', section='$section' 
                        WHERE id=$id";
    }

    if ($conn->query($update_sql) === TRUE) {
        echo "Student updated successfully";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// Delete student record
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM students WHERE id=$delete_id";
    if ($conn->query($delete_sql) === TRUE) {
        echo "Student deleted successfully";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BSIT Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="folders/css/admin_dashboard.css">
    <style>
        /* Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.3s, color 0.3s;
        }

        /* Theme Variables */
        :root {
            --bg-color: #2b2f3a; /* Dark Background */
            --text-color: #eaeaea; /* Light Text */
            --sidebar-bg: #1f232b; /* Sidebar Background */
            --menu-hover-bg: #3498db; /* Accent Blue */
            --card-bg: #343a46; /* Card Background */
            --button-hover: #ff6b6b; /* Hover Red */
            --light-bg: #fdfdfd; /* Light Background */
            --light-text: #2d3436; /* Dark Text */
            --light-card: #ffffff; /* Light Card */
            --shadow-color: rgba(0, 0, 0, 0.2);
            --shadow-hover: rgba(0, 0, 0, 0.3);
            --highlight-color: #f39c12; /* Gold */
            --button-color: #4caf50; /* Green */
            --header-bg: #273c75; /* Header Background */
        }

        .light-mode {
            --bg-color: var(--light-bg);
            --text-color: var(--light-text);
            --card-bg: var(--light-card);
            --shadow-color: rgba(200, 200, 200, 0.2);
            --shadow-hover: rgba(200, 200, 200, 0.4);
            --header-bg: #74b9ff; /* Light Blue Header */
        }

        /* Layout Styling */
        .dashboard {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: var(--sidebar-bg);
            color: #fff;
            padding: 30px 20px;
            box-shadow: 2px 0 8px var(--shadow-color);
            position: fixed;
            height: 100%;
            transition: all 0.3s ease;
        }

        .sidebar h2 {
            font-size: 24px;
            text-align: center;
            margin-bottom: 40px;
            color: var(--highlight-color);
            letter-spacing: 2px;
        }

        .menu {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .menu-item {
            padding: 18px 20px;
            font-size: 18px;
            cursor: pointer;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 20px;
            transition: 0.3s;
        }

        .menu-item:hover,
        .menu-item.active {
            background-color: var(--menu-hover-bg);
            transform: scale(1.05);
            box-shadow: 0 4px 8px var(--shadow-hover);
        }

        .menu-item i {
            font-size: 20px;
        }

        /* Main Content Styling */
        .main-content {
            margin-left: 270px;
            padding: 30px 40px;
            width: 100%;
            overflow-y: auto;
            transition: margin-left 0.3s;
            margin-top: 80px;
        }

        /* Header Styling */
        .header {
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            background-color: var(--header-bg);
            color: #fff;
            padding: 20px 40px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            box-shadow: 0 4px 8px var(--shadow-color);
            z-index: 1000;
        }

        .header .logout {
            background-color: #e74c3c;
            color: #fff;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            transition: 0.3s;
        }

        .header .logout:hover {
            background-color: #c0392b;
        }

        /* Theme Switch */
        .theme-switch {
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .theme-switch .toggle-label {
            font-size: 16px;
            margin-right: 10px;
        }

        .theme-switch .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 25px;
        }

        .theme-switch .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .theme-switch .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 34px;
        }

        .theme-switch .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 4px;
            bottom: 3.5px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        .theme-switch input:checked + .slider {
            background-color: #4caf50;
        }

        .theme-switch input:checked + .slider:before {
            transform: translateX(24px);
        }

        /* Search Bar Styling */
        #searchBar {
            width: 100%;
            padding: 12px 20px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            background-color: var(--light-card);
            color: var(--light-text);
        }

        #searchBar:focus {
            outline: none;
            border-color: var(--highlight-color);
            box-shadow: 0 0 5px var(--highlight-color);
        }

        /* Cards Section */
        .cards {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
            justify-content: space-between;
        }

        .card {
            background-color: var(--card-bg);
            padding: 35px;
            flex: 1;
            border-radius: 12px;
            box-shadow: 0 4px 8px var(--shadow-color);
            text-align: center;
            transition: 0.3s;
            overflow: hidden;
            position: relative;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px var(--shadow-hover);
        }

        .card h3 {
            margin-bottom: 12px;
            font-size: 24px;
            color: var(--highlight-color);
            font-weight: 600;
        }

        .card span {
            font-size: 30px;
            font-weight: bold;
            color: var(--highlight-color);
        }

        .card .card-icon {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 45px;
            color: rgba(255, 255, 255, 0.2);
        }

        /* Utility Classes */
        .hidden {
            display: none;
        }

        .edit-btn,
        .delete-btn {
            background-color: transparent;
            border: none;
            color: var(--highlight-color);
            font-size: 18px;
            cursor: pointer;
            transition: 0.3s;
        }

        .edit-btn:hover,
        .delete-btn:hover {
            text-decoration: underline;
            color: var(--button-color);
        }

        .edit-btn i,
        .delete-btn i {
            font-size: 22px;
        }

        img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
        }

        /* Report Form Styling */
        #report-form {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 8px var(--shadow-color);
            width: 50%;
            margin: 0 auto;
        }

        #report-form label {
            font-size: 16px;
            margin-bottom: 8px;
            display: block;
            color: var(--text-color);
        }

        #report-form select,
        #report-form input {
            width: 100%;
            padding: 12px;
            margin: 8px 0 20px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
            background-color: var(--light-card);
            color: var(--light-text);
        }

        #report-form select:focus,
        #report-form input:focus {
            border-color: var(--highlight-color);
            box-shadow: 0 0 5px var(--highlight-color);
            outline: none;
        }

        .generate-btn {
            background-color: var(--button-color);
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: all 0.3s ease;
        }

        .generate-btn:hover {
            background-color: var(--button-hover);
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2 id="sidebar-title">BSIT Admin</h2>
            <div class="menu">
                <div class="menu-item active" onclick="switchSection('dashboard')">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </div>
                <div class="menu-item" onclick="switchSection('view-students')">
                    <i class="fas fa-users"></i> Manage Students
                </div>
                <div class="menu-item" onclick="switchSection('generate-reports')">
                    <i class="fas fa-file-alt"></i> Generate Reports
                </div>
            </div>
            <div class="theme-switch">
                <label class="toggle-label" id="modeLabel">Light Mode</label>
                <label class="switch">
                    <input type="checkbox" id="toggleSwitch" onchange="toggleMode()">
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <a href="index.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
            <!-- Dashboard -->
            <div id="dashboard" class="section">
                <div class="cards">
                    <div class="card">
                        <i class="fa fa-users card-icon"></i>
                        <h3>Total Students</h3>
                        <span id="total-students"><?php echo $total_students; ?></span>
                    </div>
                    <div class="card">
                        <i class="fa fa-mars card-icon"></i>
                        <h3>Total Male</h3>
                        <span id="total-male"><?php echo $total_male; ?></span>
                    </div>
                    <div class="card">
                        <i class="fa fa-venus card-icon"></i>
                        <h3>Total Female</h3>
                        <span id="total-female"><?php echo $total_female; ?></span>
                    </div>
                </div>
            </div>

            <!-- View Students -->
            <div id="view-students" class="section hidden">
                <input type="text" id="searchBar" placeholder="Search students..." oninput="searchStudents()">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Profile</th>
                            <th>ID</th>
                            <th>Email</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Gender</th>
                            <th>Role</th>
                            <th>Year</th>
                            <th>Section</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentTable">
                        <?php 
                        $result->data_seek(0);
                        while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $profile_picture_path = $row['profile_picture'] ? $row['profile_picture'] : 'uploads/default.png'; 
                                    ?>
                                    <img src="<?php echo $profile_picture_path; ?>" alt="Profile Picture">
                                </td>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo $row['firstname']; ?></td>
                                <td><?php echo $row['lastname']; ?></td>
                                <td><?php echo $row['gender']; ?></td>
                                <td><?php echo $row['role']; ?></td>
                                <td><?php echo $row['year']; ?></td>
                                <td><?php echo $row['section']; ?></td>
                                <td>
                                    <a href="edit_student.php?id=<?php echo $row['id']; ?>" class="edit-btn"><i class="fas fa-edit"></i></a>
                                    <button class="delete-btn" onclick="deleteStudent(<?php echo $row['id']; ?>)"><i class="fas fa-trash-alt"></i></button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Generate Reports -->
            <div id="generate-reports" class="section hidden">
                <h2>Generate Reports</h2>
                <form action="generate_report.php" method="post" id="report-form">
                    <label for="report-type">Select Report Type:</label>
                    <select name="report-type" id="report-type" required>
                        <option value="students-summary">Students Summary</option>
                        <option value="gender-distribution">Gender Distribution</option>
                        <option value="year-section-breakdown">Year & Section Breakdown</option>
                    </select>
                    <label for="date-range">Date Range:</label>
                    <input type="date" name="start-date" required>
                    <span>to</span>
                    <input type="date" name="end-date" required>
                    <button type="submit" class="btn generate-btn">Generate Report</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Script for section switching
        function switchSection(sectionId) {
            document.querySelectorAll('.section').forEach(section => {
                section.classList.add('hidden');
            });
            document.getElementById(sectionId).classList.remove('hidden');
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active');
            });
            document.querySelector(`.menu-item[onclick="switchSection('${sectionId}')"]`).classList.add('active');
        }

        // Script for toggling theme
        function toggleMode() {
            const body = document.body;
            const label = document.getElementById('modeLabel');
            body.classList.toggle('light-mode');
            label.textContent = body.classList.contains('light-mode') ? 'Dark Mode' : 'Light Mode';
        }

        // Script for searching students
        function searchStudents() {
            const searchInput = document.getElementById('searchBar').value.toLowerCase();
            const tableRows = document.querySelectorAll('#studentTable tr');
            tableRows.forEach(row => {
                const rowText = row.innerText.toLowerCase();
                row.style.display = rowText.includes(searchInput) ? '' : 'none';
            });
        }

        // Script for deleting a student (confirmation dialog)
        function deleteStudent(id) {
            if (confirm('Are you sure you want to delete this student?')) {
                window.location.href = `delete_student.php?id=${id}`;
            }
        }
    </script>
</body>
</html>
