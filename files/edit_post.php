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

// Get the post ID from URL
if (isset($_GET['id'])) {
    $post_id = $_GET['id'];
    
    // Fetch the post data
    $sql = "SELECT * FROM posts WHERE id = $post_id";
    $result = $conn->query($sql);
    $post = $result->fetch_assoc();
    
    if (!$post) {
        die("Post not found.");
    }
}

// Update the post
if (isset($_POST['edit_post'])) {
    $post_content = $_POST['post_content'];
    
    // Check if an image is uploaded
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === 0) {
        $image = $_FILES['post_image']['name'];
        $image_tmp_name = $_FILES['post_image']['tmp_name'];
        $image_size = $_FILES['post_image']['size'];
        $image_error = $_FILES['post_image']['error'];

        // Move image to target directory
        if ($image_error === 0) {
            $target_dir = "uploads/posts/";
            $target_file = $target_dir . basename($image);

            if (move_uploaded_file($image_tmp_name, $target_file)) {
                // Update post with image
                $update_sql = "UPDATE posts SET content='$post_content', image='$target_file' WHERE id=$post_id";
                if ($conn->query($update_sql) === TRUE) {
                    echo "Post updated successfully";
                    header('Location: admin_dashboard.php'); // Redirect to the manage posts page
                    exit();
                } else {
                    echo "Error updating post: " . $conn->error;
                }
            } else {
                echo "Error uploading image.";
            }
        } else {
            echo "File upload error: " . $image_error;
        }
    } else {
        // Update post without image
        $update_sql = "UPDATE posts SET content='$post_content' WHERE id=$post_id";
        if ($conn->query($update_sql) === TRUE) {
            echo "Post updated successfully";
            header('Location: admin_dashboard.php'); // Redirect to the manage posts page
            exit();
        } else {
            echo "Error updating post: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="folders/css/admin_dashboard.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Edit Post Section */
        .edit-post-section {
            background-color: white;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            max-width: 700px;
            margin: auto;
            margin-top: 20px;
        }

        .edit-post-section h2 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }

        input[type="file"] {
            padding: 10px;
            font-size: 16px;
            margin-bottom: 20px;
        }

        label {
            font-size: 16px;
            font-weight: bold;
            color: #34495e;
        }

        .btn {
            background-color: #3498db;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .edit-post-section img {
            margin-top: 10px;
            max-width: 150px;
            border-radius: 5px;
        }

        .edit-post-section p {
            font-size: 14px;
            color: #7f8c8d;
        }
    </style>
</head>

<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2 id="sidebar-title">BSIT Admin</h2>
            <div class="menu">
                <div class="menu-item" onclick="window.location.href='admin_dashboard.php'">
                    <i class="fas fa-pencil-alt"></i> Manage Posts
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <a href="index.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>

            <!-- Edit Post Form -->
            <div class="edit-post-section">
                <h2>Edit Post</h2>
                <form action="edit_post.php?id=<?php echo $post['id']; ?>" method="POST" enctype="multipart/form-data">
                    <textarea name="post_content" id="post_content" rows="6" placeholder="Edit your message..." required><?php echo $post['content']; ?></textarea><br>
                    
                    <label for="post_image">Upload a New Image (Optional):</label>
                    <input type="file" name="post_image" id="post_image"><br><br>
                    
                    <?php if ($post['image']): ?>
                        <div>
                            <img src="<?php echo $post['image']; ?>" alt="Post Image" style="width: 150px;">
                            <p>Current Image</p>
                        </div>
                    <?php endif; ?>
                    
                    <button type="submit" name="edit_post" class="btn">Update Post</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

