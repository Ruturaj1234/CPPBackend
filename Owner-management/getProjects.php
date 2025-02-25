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

$client_name = isset($_GET['client_name']) ? $_GET['client_name'] : '';
if (empty($client_name)) {
    echo json_encode(["success" => false, "message" => "Client name not provided"]);
    exit;
}

// Fetch projects by joining client_projects with projects on client_id
$query = "
    SELECT cp.id, cp.project_name, cp.project_description 
    FROM client_projects cp
    JOIN projects p ON cp.client_id = p.id
    WHERE p.client_name = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $client_name);
$stmt->execute();
$result = $stmt->get_result();

$projects = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = [
            "id" => $row['id'],
            "project_name" => $row['project_name'],
            "project_description" => $row['project_description'] ?: "No description available"
        ];
    }
}

$stmt->close();
$conn->close();

echo json_encode([
    "success" => true,
    "projects" => $projects
]);
?>