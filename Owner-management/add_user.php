<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

// Include database connection
include('connection.php'); // Ensure this file correctly connects to your database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    date_default_timezone_set('Asia/Kolkata'); // Set timezone

    // Get data from the POST request
    $username = $_POST['username'] ?? ''; // Use null coalescing operator
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    // Prepare SQL statement to avoid SQL injection
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(["message" => "User added successfully"]);
    } else {
        echo json_encode(["error" => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>
