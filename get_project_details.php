<?php
// Allow requests from all origins (you can restrict it to your frontend domain)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Your existing database code goes here...

// Rest of the code

$servername = "localhost";
$username = "root"; // Update this as per your setup
$password = ""; // Update this as per your setup
$dbname = "user_db"; // Update this to the correct database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get clientId and projectId from URL parameters
$clientId = $_GET['clientId'];
$projectId = $_GET['projectId'];

// Query to fetch the project name
$sql = "SELECT project_name FROM client_projects WHERE client_id = ? AND id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $clientId, $projectId);
$stmt->execute();
$result = $stmt->get_result();

// Check if project is found
if ($result->num_rows > 0) {
    $projectDetails = $result->fetch_assoc();
    echo json_encode(["success" => true, "project_name" => $projectDetails['project_name']]);
} else {
    echo json_encode(["success" => false, "message" => "Project not found"]);
}

$conn->close();
?>
