<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

if ($client_id <= 0) {
    echo json_encode(["error" => "Invalid client_id"]);
    exit;
}

// Fetch projects for the client, excluding those marked as done
$sql = "
    SELECT cp.id, cp.project_name, cp.project_description, cp.created_at 
    FROM client_projects cp
    LEFT JOIN project_done pd ON cp.id = pd.project_id AND pd.is_project_done = 1
    WHERE cp.client_id = ? AND (pd.id IS NULL OR pd.is_project_done = 0)
";
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