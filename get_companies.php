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

// Fetch only companies with projects in client_projects
$sql = "
    SELECT DISTINCT p.id, p.client_name, p.created_at 
    FROM projects p
    INNER JOIN client_projects cp ON p.id = cp.client_id
";
$result = $conn->query($sql);

$companies = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $companies[] = [
            "id" => $row["id"],
            "client_name" => $row["client_name"],
            "created_at" => $row["created_at"]
        ];
    }
}

$conn->close();
echo json_encode($companies);
?>