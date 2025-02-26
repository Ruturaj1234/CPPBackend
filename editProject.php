<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$projectId = isset($input['id']) ? intval($input['id']) : 0;
$newProjectName = isset($input['project_name']) ? trim($input['project_name']) : '';
$newProjectDescription = isset($input['project_description']) ? trim($input['project_description']) : '';

if ($projectId <= 0 || empty($newProjectName)) {
    echo json_encode(['success' => false, 'message' => 'Invalid project ID or name']);
    exit;
}

$query = "UPDATE client_projects SET project_name = ?, project_description = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssi", $newProjectName, $newProjectDescription, $projectId);
$success = $stmt->execute();

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Project updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update project: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>