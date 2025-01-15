<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Database configuration
$servername = "localhost"; 
$username = "root";        
$password = "";            
$dbname = "user_db";       

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Check if client ID is provided
if (isset($_GET['clientId'])) {
    $clientId = $_GET['clientId'];

    // Fetch the client name
    $clientNameQuery = "SELECT client_name FROM projects WHERE id = ?";
    $stmt = $conn->prepare($clientNameQuery);
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $clientResult = $stmt->get_result();

    if ($clientResult->num_rows > 0) {
        $clientRow = $clientResult->fetch_assoc();
        $clientName = $clientRow['client_name'];
    } else {
        echo json_encode(['success' => false, 'message' => 'Client not found']);
        exit();
    }

    // Fetch the projects for the given client
    $projectsQuery = "SELECT id, project_name, project_description, created_at FROM client_projects WHERE client_id = ?";
    $stmt = $conn->prepare($projectsQuery);
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $projectsResult = $stmt->get_result();

    $projects = [];
    while ($row = $projectsResult->fetch_assoc()) {
        $projects[] = $row;
    }

    // Return both the client name and the projects
    echo json_encode(['success' => true, 'clientName' => $clientName, 'projects' => $projects]);

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Client ID is required']);
}

$conn->close();
?>
