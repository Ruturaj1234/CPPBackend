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

if ($clientId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Delete associated projects from client_projects
    $deleteProjectsQuery = "DELETE FROM client_projects WHERE client_id = ?";
    $stmtProjects = $conn->prepare($deleteProjectsQuery);
    $stmtProjects->bind_param("i", $clientId);
    $stmtProjects->execute();

    // Delete client from projects
    $deleteClientQuery = "DELETE FROM projects WHERE id = ?";
    $stmtClient = $conn->prepare($deleteClientQuery);
    $stmtClient->bind_param("i", $clientId);
    $stmtClient->execute();

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Client and associated projects deleted successfully']);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to delete client and projects: ' . $e->getMessage()]);
}

$stmtProjects->close();
$stmtClient->close();
$conn->close();
?>