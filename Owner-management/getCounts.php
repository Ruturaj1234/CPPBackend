<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
// Database connection (Update with your actual database credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$clientQuery = "SELECT COUNT(DISTINCT client_id) as client_count FROM client_projects";
$clientResult = $conn->query($clientQuery);
$clientCount = 0;
if ($clientResult && $clientRow = $clientResult->fetch_assoc()) {
    $clientCount = $clientRow['client_count'];
}

// Query to get the number of projects from the "projects" table
$projectQuery = "SELECT COUNT(*) as project_count FROM projects";
$projectResult = $conn->query($projectQuery);
$projectCount = 0;
if ($projectResult && $projectRow = $projectResult->fetch_assoc()) {
    $projectCount = $projectRow['project_count'];
}

$conn->close();

// Return JSON response with client and project counts
echo json_encode([
    'clients' => $clientCount,
    'projects' => $projectCount,
]);
?>