<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Get the logged-in user's ID from the user_activity table
$latestActivitySql = "SELECT user_id FROM user_activity ORDER BY created_at DESC LIMIT 1";
$result = $conn->query($latestActivitySql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $employee_id = $row['user_id']; // Get the latest user's ID
} else {
    echo json_encode(["success" => false, "message" => "No active user found."]);
    exit;
}

// Step 2: Get all leave requests for the current user (including pending, approved, rejected, and completed)
$checkRequestSql = "SELECT * FROM leave_requests WHERE employee_id = '$employee_id'";
$requestResult = $conn->query($checkRequestSql);

$leaveRequests = [];

if ($requestResult->num_rows > 0) {
    // Fetch all requests
    while ($row = $requestResult->fetch_assoc()) {
        $leaveRequests[] = $row; // Add each request to the array
    }
    echo json_encode([
        "success" => true,
        "message" => "Leave requests found.",
        "data" => $leaveRequests
    ]);
} else {
    // No leave requests found
    echo json_encode([
        "success" => false,
        "message" => "No leave requests found for the user."
    ]);
}

// Close the connection
$conn->close();
?>
