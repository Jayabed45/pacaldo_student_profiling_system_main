<?php
// Start the session to destroy the session data
session_start();

// Destroy the session to log the user out
session_destroy();

// Redirect to the login page
header('Location: index.php');
exit();
?>
