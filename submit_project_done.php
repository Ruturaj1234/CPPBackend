<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

date_default_timezone_set('Asia/Kolkata');

$conn = new mysqli("localhost", "root", "", "user_db");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Get the latest logged-in user ID
$query = "SELECT user_id FROM user_activity ORDER BY created_at DESC LIMIT 1";
$result = mysqli_query($conn, $query);
if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode(["success" => false, "message" => "Failed to retrieve logged-in user."]);
    exit;
}
$row = mysqli_fetch_assoc($result);
$user_id = $row['user_id'];

// Get JSON input data
$inputData = json_decode(file_get_contents("php://input"), true);
$project_id = $inputData['project_id'] ?? '';
if (empty($project_id)) {
    echo json_encode(["success" => false, "message" => "Missing project_id."]);
    exit;
}

// Check if the user is a leader
$leaderQuery = "SELECT * FROM assignments WHERE employee_id = ? AND project_id = ? AND is_leader = 1";
$stmt = $conn->prepare($leaderQuery);
$stmt->bind_param("ii", $user_id, $project_id);
$stmt->execute();
$leaderResult = $stmt->get_result();
if ($leaderResult->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Only the project leader can mark the project as done."]);
    exit;
}

// Check if a report exists
$checkQuery = "SELECT id FROM project_done WHERE project_id = ? AND is_project_done = 0 ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$checkResult = $stmt->get_result();

$date_completed = date("Y-m-d"); // Current date, e.g., "2025-02-24"

if ($checkResult->num_rows > 0) {
    // Update existing report to done with date_completed
    $row = $checkResult->fetch_assoc();
    $existingId = $row['id'];

    $updateQuery = "UPDATE project_done SET is_project_done = 1, date_completed = ?, created_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("si", $date_completed, $existingId);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Project marked as done successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to mark project as done: " . $stmt->error]);
    }
} else {
    // Insert new done entry with date_completed
    $insertQuery = "INSERT INTO project_done (project_id, leader_id, is_project_done, date_completed, created_at) 
                    VALUES (?, ?, 1, ?, NOW())";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("iis", $project_id, $user_id, $date_completed);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Project marked as done successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to mark project as done: " . $stmt->error]);
    }
}

$stmt->close();
$conn->close();
?>