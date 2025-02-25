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
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

if ($client_id > 0) {
    $query = "
        SELECT cp.id, cp.project_name 
        FROM client_projects cp
        INNER JOIN assignments a ON cp.id = a.project_id
        LEFT JOIN project_done pd ON pd.project_id = cp.id AND pd.is_project_done = 1
        WHERE cp.client_id = ? AND (pd.id IS NULL OR pd.is_project_done = 0)
        GROUP BY cp.id
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }

    if (!empty($projects)) {
        echo json_encode(["success" => true, "projects" => $projects]);
    } else {
        echo json_encode(["success" => false, "message" => "No active assigned projects found for this client."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid client ID"]);
}

$conn->close();
?>