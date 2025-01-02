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

// Insert a post with an image
if (isset($_POST['post_message'])) {
    $post_content = $_POST['post_content'];
    $admin_id = 1; // Assuming admin ID is 1, change as needed

    // Check if the file is uploaded
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === 0) {
        $image = $_FILES['post_image']['name'];
        $image_tmp_name = $_FILES['post_image']['tmp_name'];
        $image_size = $_FILES['post_image']['size'];
        $image_error = $_FILES['post_image']['error'];

        // Check for upload errors
        if ($image_error === 0) {
            // Check if the target directory exists
            $target_dir = "uploads/posts/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true); // Create directory if it doesn't exist
            }

            $target_file = $target_dir . basename($image);

            if (move_uploaded_file($image_tmp_name, $target_file)) {
                // Insert the post with image
                $sql = "INSERT INTO posts (admin_id, content, image, created_at) 
                        VALUES ($admin_id, '$post_content', '$target_file', NOW())";
                if ($conn->query($sql) === TRUE) {
                    echo "Post created successfully";
                } else {
                    echo "Error creating post: " . $conn->error;
                }
            } else {
                echo "Error uploading image.";
            }
        } else {
            echo "File upload error: " . $image_error;
        }
    } else {
        // If no image is uploaded, insert the post without an image
        $sql = "INSERT INTO posts (admin_id, content, created_at) 
                VALUES ($admin_id, '$post_content', NOW())";
        if ($conn->query($sql) === TRUE) {
            echo "Post created successfully without an image";
        } else {
            echo "Error creating post: " . $conn->error;
        }
    }
}
// Delete post record
if (isset($_GET['delete_post_id'])) {
    $delete_post_id = $_GET['delete_post_id'];
    // Ensure the post exists before deleting (optional but recommended)
    $delete_sql = "SELECT * FROM posts WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param('i', $delete_post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Proceed with deletion
        $delete_post_sql = "DELETE FROM posts WHERE id = ?";
        $stmt = $conn->prepare($delete_post_sql);
        $stmt->bind_param('i', $delete_post_id);
        if ($stmt->execute()) {
            echo "Post deleted successfully.";
            // Redirect back to the same page after deletion
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Error deleting post: " . $conn->error;
        }
    } else {
        echo "Post not found.";
    }
}

// Fetch posts
$posts_sql = "SELECT * FROM posts ORDER BY created_at DESC";
$posts_result = $conn->query($posts_sql);

// Update student record
if (isset($_POST['update'])) {
    $id = $_POST['id'] ?? null;
    $firstname = $_POST['firstname'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $email = $_POST['email'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $role = $_POST['role'] ?? '';
    $year = $_POST['year'] ?? '';
    $section = $_POST['section'] ?? '';
    $profile_picture = $_FILES['profile_picture']['name'] ?? null;

    if ($id !== null) {
        // Handle file upload for profile picture
        if ($profile_picture) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($profile_picture);
            move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file);

            $update_sql = "UPDATE students SET 
                            firstname=?, lastname=?, 
                            email=?, gender=?, 
                            role=?, year=?, 
                            section=?, profile_picture=? 
                            WHERE id=?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param('ssssssssi', $firstname, $lastname, $email, $gender, $role, $year, $section, $profile_picture, $id);
        } else {
            $update_sql = "UPDATE students SET 
                            firstname=?, lastname=?, 
                            email=?, gender=?, 
                            role=?, year=?, section=? 
                            WHERE id=?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param('sssssssi', $firstname, $lastname, $email, $gender, $role, $year, $section, $id);
        }

        if ($stmt->execute()) {
            echo "Student updated successfully";
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
}

// Delete student record
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM students WHERE id=?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param('i', $delete_id);
    if ($stmt->execute()) {
        echo "Student deleted successfully";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// Update admin profile
if (isset($_POST['update'])) {
    $firstname = $_POST['firstname'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $email = $_POST['email'] ?? '';
    $profile_picture = $_FILES['profile_picture']['name'] ?? null;

    // Handle file upload for profile picture
    if ($profile_picture) {
        $target_dir = "uploads/"; // Set the upload directory
        $target_file = $target_dir . basename($profile_picture);
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file);

        // Update admin profile (use the correct table name: 'admin')
        $update_sql = "UPDATE admin SET firstname=?, lastname=?, email=?, profile_picture=? WHERE id=1";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('ssss', $firstname, $lastname, $email, $profile_picture);
    } else {
        // Update without profile picture if not uploaded
        $update_sql = "UPDATE admin SET firstname=?, lastname=?, email=? WHERE id=1";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('sss', $firstname, $lastname, $email);
    }

    if ($stmt->execute()) {
        echo "Profile updated successfully.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error updating profile: " . $conn->error;
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

</head>
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
        /* Post Image Styling */
        /* Styling for the Post Form Section */
.post-section {
    background-color: #f9f9f9;
    padding: 30px;
    margin-top: 30px;
    border-radius: 12px;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    max-width: 700px;
    margin: 0 auto;
}

/* Title for the Post Section */
.post-section h2 {
    font-size: 26px;
    color: #333;
    margin-bottom: 20px;
    font-weight: bold;
    text-align: center;
}

/* Styling for the form itself */
.post-section form {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Textarea for the Post Content */
.post-section textarea {
    width: 100%;
    padding: 14px;
    border-radius: 8px;
    border: 1px solid #ddd;
    font-size: 16px;
    resize: vertical;
    outline: none;
    transition: all 0.3s ease;
}

/* Focus effect on textarea */
.post-section textarea:focus {
    border-color: #4e73df;
    box-shadow: 0 0 5px rgba(78, 115, 223, 0.5);
}

/* File Input */
.post-section input[type="file"] {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #fff;
    margin-bottom: 20px;
    cursor: pointer;
    font-size: 14px;
}

/* Styling for the Submit Button */
.post-section .btn {
    background-color: #4e73df;
    color: #fff;
    padding: 14px;
    border-radius: 8px;
    border: none;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

/* Hover effect for the submit button */
.post-section .btn:hover {
    background-color: #3551b1;
    transform: scale(1.05);
}

/* Small file upload text */
.post-section .file-upload-text {
    font-size: 14px;
    color: #666;
}

/* Adding responsiveness */
@media (max-width: 768px) {
    .post-section {
        padding: 20px;
    }

    .post-section h2 {
        font-size: 22px;
    }

    .post-section form {
        gap: 15px;
    }
}

.recent-posts .post-card .post-image {
    margin-top: 10px;
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #ddd; /* Subtle border around the image */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Adding a soft shadow */
}

.recent-posts .post-card .post-image img {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 8px; /* Soft rounded corners */
}

/* Additional styling for image container */
.recent-posts .post-card .post-image:hover {
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15); /* Enhanced shadow on hover */
    border-color: #4e73df; /* Highlight the border color when hovered */
    transform: scale(1.03); /* Slightly zoom in effect */
    transition: all 0.3s ease;
}

/* Admin Profile Section Styles */
#admin-profile {
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    width: 80%;
    margin: 0 auto;
}

#admin-profile h2 {
    text-align: center;
    font-size: 2em;
    margin-bottom: 20px;
    color: #333;
}

#admin-profile form {
    display: flex;
    flex-direction: column;
    align-items: center;
}

#admin-profile label {
    font-size: 1.1em;
    margin-bottom: 8px;
    color: #555;
}

#admin-profile input[type="text"],
#admin-profile input[type="email"],
#admin-profile input[type="file"] {
    width: 70%;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1em;
    background-color: #fff;
}

#admin-profile input[type="file"] {
    padding: 6px;
}

#admin-profile .btn {
    background-color: #4CAF50;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 4px;
    font-size: 1.2em;
    cursor: pointer;
    transition: background-color 0.3s;
}

#admin-profile .btn:hover {
    background-color: #45a049;
}

#admin-profile .profile-picture-preview {
    margin: 20px 0;
}

#admin-profile .profile-picture-preview img {
    max-width: 150px;
    max-height: 150px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #ddd;
}

/* Mobile Responsiveness */
@media screen and (max-width: 768px) {
    #admin-profile {
        width: 90%;
    }

    #admin-profile input[type="text"],
    #admin-profile input[type="email"],
    #admin-profile input[type="file"] {
        width: 90%;
    }

    #admin-profile .btn {
        width: 90%;
    }
}

        
    </style>
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
                <div class="menu-item" onclick="switchSection('manage-posts')">
                    <i class="fas fa-pencil-alt"></i> Manage Posts
                </div>
                <div class="menu-item" onclick="switchSection('admin-profile')">
                    <i class="fas fa-user-circle"></i> Admin Profile
                </div>
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

<!-- Manage Posts -->
<div id="manage-posts" class="section hidden">
    <div class="post-section">
        <h2>Post Something</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <textarea name="post_content" id="post_content" rows="6" placeholder="Write your message..." required></textarea><br>
            <label for="post_image">Upload an Image:</label>
            <input type="file" name="post_image" id="post_image"><br><br>
            <button type="submit" name="post_message" class="btn">Post Something</button>
        </form>
    </div>
    <h3>Recent Posts</h3>
    <div class="recent-posts">
        <?php while ($post = $posts_result->fetch_assoc()): ?>
            <div class="post-card">
                <div class="post-header">
                  
                    <span><?php echo $post['created_at']; ?></span>
                </div>
                <div class="post-content">
                    <?php echo $post['content']; ?>
                </div>
                <?php if ($post['image']): ?>
                    <div class="post-image">
                        <img src="<?php echo $post['image']; ?>" alt="Post Image">
                    </div>
                <?php endif; ?>
                <div class="post-actions">
                    <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="edit-btn">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <button class="delete-btn" onclick="deletePost(<?php echo $post['id']; ?>)">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<!-- Admin Profile -->
<div id="admin-profile" class="section hidden">
    <h2>Admin Profile</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <label for="firstname">First Name:</label>
        <input type="text" name="firstname"required><br>
        
        <label for="lastname">Last Name:</label>
        <input type="text" name="lastname"  required><br>
        
        <label for="email">Email:</label>
        <input type="email" name="email" required><br>

        <label for="profile_picture">Profile Picture:</label>
        <input type="file" name="profile_picture"><br>

        <button type="submit" name="update" class="btn">Update Profile</button>
    </form>
</div>
 
    </div>

    <script>
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
        
        function deleteStudent(id) {
            if (confirm('Are you sure you want to delete this student?')) {
                window.location.href = `?delete_id=${id}`;
            }
        }
        function deletePost(id) {
    if (confirm('Are you sure you want to delete this post?')) {
        // Redirect to the same page with the 'delete_post_id' in the URL
        window.location.href = `?delete_post_id=${id}`;
    }
}


    </script>
</body>
</html>
