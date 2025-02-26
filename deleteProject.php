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

if ($projectId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid project ID']);
    exit;
}

$query = "DELETE FROM client_projects WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $projectId);
$success = $stmt->execute();

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Project deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete project: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>