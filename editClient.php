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
$clientId = isset($input['id']) ? intval($input['id']) : 0;
$newClientName = isset($input['client_name']) ? trim($input['client_name']) : '';

if ($clientId <= 0 || empty($newClientName)) {
    echo json_encode(['success' => false, 'message' => 'Invalid client ID or name']);
    exit;
}

$query = "UPDATE projects SET client_name = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $newClientName, $clientId);
$success = $stmt->execute();

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Client updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update client: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>