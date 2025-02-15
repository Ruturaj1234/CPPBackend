<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Set timezone dynamically
if (!date_default_timezone_get()) {
    date_default_timezone_set('Asia/Kolkata');
}

// Database connection
$conn = new mysqli("localhost", "root", "", "user_db");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Step 1: Get the latest user_id from the user_activity table
$query = "SELECT user_id FROM user_activity ORDER BY created_at DESC LIMIT 1";  // Use created_at column
$result = mysqli_query($conn, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $latestUserId = $row['user_id'];

    // Step 2: Check if the latest user is the leader of any project
    $assignmentQuery = "SELECT * FROM assignments WHERE employee_id = '$latestUserId' AND is_leader = 1"; // Use the latest user_id to check leader status
    $assignmentResult = mysqli_query($conn, $assignmentQuery);

    if (mysqli_num_rows($assignmentResult) > 0) {
        // The logged-in user is a project leader
        echo json_encode([
            'success' => true,
            'isLeader' => true
        ]);
    } else {
        // The logged-in user is NOT a project leader
        echo json_encode([
            'success' => true,
            'isLeader' => false
        ]);
    }
} else {
    // Error getting the latest user_id
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve user activity data'
    ]);
}

mysqli_close($conn);
?>
