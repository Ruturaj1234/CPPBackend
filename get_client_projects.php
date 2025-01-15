<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');


// Database configuration
$servername = "localhost"; 
$username = "root";        
$password = "";            
$dbname = "user_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Get client_id from the GET request
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

if ($client_id <= 0) {
    echo json_encode(["error" => "Invalid client_id"]);
    exit;
}

// Fetch projects for the specified client
$sql = "SELECT id, project_name, project_description, created_at 
        FROM client_projects 
        WHERE client_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

$projects = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = [
            "id" => $row["id"],
            "project_name" => $row["project_name"],
            "project_description" => $row["project_description"],
            "created_at" => $row["created_at"]
        ];
    }
}

$stmt->close();
$conn->close();

echo json_encode($projects);
?>
