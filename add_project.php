<?php
// Allow CORS and handle content type
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Set the timezone to IST
date_default_timezone_set('Asia/Kolkata');

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_db";  // Your database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Check if necessary data is provided
if (isset($_POST['clientId']) && isset($_POST['projectName']) && isset($_POST['projectDescription'])) {
    $clientId = $_POST['clientId'];  // The client ID from the React form
    $projectName = $_POST['projectName'];
    $projectDescription = $_POST['projectDescription'];
    $createdAt = date('Y-m-d H:i:s');  // Get the current timestamp

    // Fetch the client_name based on clientId
    $clientNameQuery = "SELECT client_name FROM projects WHERE id = ?";
    $stmtClient = $conn->prepare($clientNameQuery);
    $stmtClient->bind_param("i", $clientId);
    $stmtClient->execute();
    $stmtClient->bind_result($clientName);
    $stmtClient->fetch();
    $stmtClient->close();

    // If clientName is not found, return an error
    if (!$clientName) {
        echo json_encode(['success' => false, 'message' => 'Client name not found for the given client ID']);
        exit();
    }

    // Prepare SQL query to insert project
    $sql = "INSERT INTO client_projects (client_id, client_name, project_name, project_description, created_at) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Check if statement was prepared successfully
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error]);
        exit();
    }

    // Bind parameters and execute the query
    $stmt->bind_param("issss", $clientId, $clientName, $projectName, $projectDescription, $createdAt);
    
    // Execute and check if the insertion was successful
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Project added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add project: ' . $stmt->error]);
    }

    // Close the statement
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'All fields (clientId, projectName, projectDescription) are required']);
}

// Close the connection
$conn->close();
?>
