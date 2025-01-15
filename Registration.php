<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

// Include database connection
include('connection.php'); // Ensure this file correctly connects to your database

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    date_default_timezone_set('Asia/Kolkata'); // Set timezone

    // Get data from the POST request
    $username = $_POST['uname'] ?? ''; // Use null coalescing to avoid undefined index error
    $password = $_POST['upwd'] ?? '';
    $role = $_POST['role'] ?? '';

    // Prepare the SQL statement to avoid SQL injection
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role); // "sss" means three string parameters

    // Execute the statement
    if ($stmt->execute()) {
        echo "Entry added successfully"; 
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close(); // Close the statement
} else {
    echo "Invalid request method.";
}
?>
