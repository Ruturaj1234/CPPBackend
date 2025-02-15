<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Database connection
$conn = new mysqli("localhost", "root", "", "user_db");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Step 1: Get the latest logged-in user ID from `user_activity`
$query = "SELECT user_id FROM user_activity ORDER BY created_at DESC LIMIT 1";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode(["success" => false, "message" => "Failed to retrieve logged-in user."]);
    exit;
}

$row = mysqli_fetch_assoc($result);
$user_id = $row['user_id'];

// Step 2: Get JSON input data
$inputData = json_decode(file_get_contents("php://input"), true);

if (!isset($inputData['project_id'], $inputData['date_completed'], $inputData['challenges'], $inputData['additional_notes'])) {
    echo json_encode(["success" => false, "message" => "Missing required fields."]);
    exit;
}

$project_id = $inputData['project_id'];
$date_completed = date("Y-m-d", strtotime($inputData['date_completed']));
$challenges = $inputData['challenges'];
$additional_notes = $inputData['additional_notes'];

// Step 3: Check if the user is a leader of this project
$leaderQuery = "SELECT * FROM assignments WHERE employee_id = '$user_id' AND project_id = '$project_id' AND is_leader = 1";
$leaderResult = mysqli_query($conn, $leaderQuery);

if (!$leaderResult || mysqli_num_rows($leaderResult) === 0) {
    echo json_encode(["success" => false, "message" => "Only the project leader can mark the project as done."]);
    exit;
}

// Step 4: Insert data into `project_done`
$insertQuery = "INSERT INTO project_done (project_id, leader_id, date_completed, challenges, additional_notes, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($insertQuery);
$stmt->bind_param("iisss", $project_id, $user_id, $date_completed, $challenges, $additional_notes);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Project marked as done successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to submit project completion."]);
}

$stmt->close();
$conn->close();

?>
