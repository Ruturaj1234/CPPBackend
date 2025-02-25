<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$project_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($project_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid project ID"]);
    exit;
}

$query = "
    SELECT project_name, project_description 
    FROM client_projects 
    WHERE id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        "success" => true,
        "project_name" => $row['project_name'],
        "project_description" => $row['project_description'] ?: "No description available"
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Project not found"]);
}

$stmt->close();
$conn->close();
?>