<?php
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

// Check if the user is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: index.php');
    exit();
}

// Handle post submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $content = $_POST['content'];
    $admin_id = $_SESSION['student_id']; // Set admin_id to the logged-in user's ID

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/"; // Directory where images will be uploaded
        $image_file = $_FILES['image']['name'];
        $target_file = $target_dir . basename($image_file);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check file type and size (optional)
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowed_types) && $_FILES['image']['size'] < 5000000) { // Limit size to 5MB
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = $target_file; // Store the path of the uploaded image
            } else {
                echo "<div class='alert alert-danger'>Error uploading image.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Invalid file type or size exceeded.</div>";
        }
    }

    // Insert the new post into the database
    $stmt = $conn->prepare("INSERT INTO posts (content, admin_id, image) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $content, $admin_id, $image_path);
    
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Post created successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
}

// Fetch all posts from the database
$sql = "SELECT * FROM posts ORDER BY created_at DESC"; // Assume your posts table is named `posts`
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Posts</title>
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

        .sidebar .brand {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar .nav-link {
            color: inherit;
            padding: 15px;
            text-decoration: none;
            display: block;
            transition: background-color 0.3s;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
        }

        .sidebar .nav-link:hover {
            background-color: transparent;
            color: #fff;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <div class="brand">Student Dashboard</div>
            <a href="view_posts.php" class="nav-link"><i class="fas fa-home"></i> View Posts</a>
            <a href="student_page.php" class="nav-link"><i class="fas fa-eye"></i> View Student Information</a>
            <a href="edit_profile.php" class="nav-link"><i class="fas fa-edit"></i> Edit Student Information</a>
        </div>
    </div>

    <!-- Content -->
    <div class="content">
        <div class="container mt-5">
            <h2 class="mb-4">Create a New Post</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="content" class="form-label">Content</label>
                    <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Upload Image</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Post</button>
            </form>

            <h2 class="mb-4 mt-5">Posts</h2>
            <?php if ($result->num_rows > 0): ?>
                <div class="row">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="col-md-4">
                            <div class="card mb-3">
                                <?php if (!empty($row['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($row['image']); ?>" class="card-img-top" alt="Post Image">
                                <?php endif; ?>
                                <div class="card-body">
                                    <p class="card-text"><?php echo htmlspecialchars($row['content']); ?></p>
                                    <p class="text-muted"><?php echo htmlspecialchars($row['created_at']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No posts available.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>