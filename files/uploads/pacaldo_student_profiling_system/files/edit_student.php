<?php
// Database configuration
$servername = "localhost";
$username = "root"; // Change as per your database configuration
$password = ""; // Change as per your database configuration
$dbname = "sheila_db";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get student ID from URL
$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Fetch student data based on the ID
$sql = "SELECT id, firstname, lastname, email, gender, role, year, section, profile_picture FROM students WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
} else {
    echo "Student not found";
    exit();
}

// Update student record
if (isset($_POST['update'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $role = $_POST['role'];
    $year = $_POST['year'];
    $section = $_POST['section'];

    $update_sql = "UPDATE students SET firstname='$firstname', lastname='$lastname', email='$email', gender='$gender', role='$role', year='$year', section='$section' WHERE id=$id";
    if ($conn->query($update_sql) === TRUE) {
        echo "Student updated successfully";
        header('Location: admin_dashboard.php'); // Redirect back to the students list page
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <style>
        /* Basic Reset and Font Setup */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f4f8;
            color: #333;
        }

        /* Dashboard Centering */
        .dashboard {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }

        /* Main Content Box */
        .main-content {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 600px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        /* Hover effect for the main content box */
        .main-content:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
        }

        /* Heading Style */
        h2 {
            text-align: center;
            color: #444;
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        /* Profile Picture Styling */
        .profile-picture {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .profile-picture img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid #ddd;
            transition: transform 0.3s ease;
        }

        /* Hover effect for profile image */
        .profile-picture img:hover {
            transform: scale(1.1);
        }

        /* Form Group Styling */
        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            color: #555;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="email"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        select:focus {
            border-color: #4CAF50;
            outline: none;
        }

        /* Button Styling */
        button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        /* Hover effect for button */
        button:hover {
            background-color: #45a049;
            transform: translateY(-2px);
        }

        /* Cancel Button Styling */
        .cancel-button {
            display: block;
            text-align: center;
            margin-top: 15px;
            text-decoration: none;
            color: #888;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .cancel-button:hover {
            color: #444;
        }

        /* Animation for form */
        .form-section {
            opacity: 0;
            animation: fadeIn 1s forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="main-content">
            <div class="form-section">
                <h2>Edit Student Information</h2>

                <!-- Profile Picture -->
                <div class="profile-picture">
                    <img src="<?php echo !empty($student['profile_picture']) ? $student['profile_picture'] : 'default-profile.png'; ?>" alt="Profile Picture">
                </div>

                <form method="POST" action="">
                    <input type="hidden" name="id" value="<?php echo $student['id']; ?>">

                    <div class="form-group">
                        <label for="firstname">First Name:</label>
                        <input type="text" name="firstname" value="<?php echo $student['firstname']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="lastname">Last Name:</label>
                        <input type="text" name="lastname" value="<?php echo $student['lastname']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" name="email" value="<?php echo $student['email']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="gender">Gender:</label>
                        <select name="gender" required>
                            <option value="Male" <?php echo $student['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $student['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="role">Role:</label>
                        <select name="role" required>
                            <option value="student" <?php echo $student['role'] == 'student' ? 'selected' : ''; ?>>Student</option>
                            <option value="admin" <?php echo $student['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="year">Year:</label>
                        <select name="year" required>
                            <option value="1st-Year" <?php echo $student['year'] == '1st-Year' ? 'selected' : ''; ?>>1st Year</option>
                            <option value="2nd-Year" <?php echo $student['year'] == '2nd-Year' ? 'selected' : ''; ?>>2nd Year</option>
                            <option value="3rd-Year" <?php echo $student['year'] == '3rd-Year' ? 'selected' : ''; ?>>3rd Year</option>
                            <option value="4th-Year" <?php echo $student['year'] == '4th-Year' ? 'selected' : ''; ?>>4th Year</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="section">Section:</label>
                        <select name="section" required>
                            <option value="Section A" <?php echo $student['section'] == 'Section A' ? 'selected' : ''; ?>>Section A</option>
                            <option value="Section B" <?php echo $student['section'] == 'Section B' ? 'selected' : ''; ?>>Section B</option>
                            <option value="Section C" <?php echo $student['section'] == 'Section C' ? 'selected' : ''; ?>>Section C</option>
                            <option value="Section D" <?php echo $student['section'] == 'Section D' ? 'selected' : ''; ?>>Section D</option>
                            <option value="Section E" <?php echo $student['section'] == 'Section E' ? 'selected' : ''; ?>>Section E</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <button type="submit" name="update">Update Student</button>
                        <a href="admin_dashboard.php" class="cancel-button">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
