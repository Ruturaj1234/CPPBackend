<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "user_db");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
if ($project_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid or missing project_id."]);
    exit;
}

$query = "SELECT challenges, progress_percentage FROM project_done WHERE project_id = ? AND is_project_done = 0 ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(["success" => true, "report" => $row]);
} else {
    echo json_encode(["success" => true, "report" => null]);
}

$stmt->close();
$conn->close();
?>