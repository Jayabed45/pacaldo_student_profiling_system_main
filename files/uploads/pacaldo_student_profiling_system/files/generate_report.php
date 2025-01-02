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

// Handle report generation request
if (isset($_GET['type'])) {
    $type = $_GET['type'];

    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $type . '_students_report.csv');
    $output = fopen('php://output', 'w');

    // Add CSV headers
    fputcsv($output, ['ID', 'First Name', 'Last Name', 'Email', 'Gender', 'Role', 'Year', 'Section']);

    // Fetch data based on type
    if ($type === 'active') {
        $sql = "SELECT * FROM students WHERE status = 'active'";
    } elseif ($type === 'inactive') {
        $sql = "SELECT * FROM students WHERE status = 'inactive'";
    } else {
        exit('Invalid report type');
    }

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Add rows to the CSV
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['id'], 
                $row['firstname'], 
                $row['lastname'], 
                $row['email'], 
                $row['gender'], 
                $row['role'], 
                $row['year'], 
                $row['section']
            ]);
        }
    } else {
        fputcsv($output, ['No data found']);
    }

    fclose($output);
    exit();
}

$conn->close();
?>
