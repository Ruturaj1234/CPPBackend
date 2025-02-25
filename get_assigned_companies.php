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

// Fetch companies with active assigned projects (not marked as done)
$sql = "
    SELECT DISTINCT 
        p.id AS company_id, 
        p.client_name, 
        p.created_at 
    FROM 
        assignments a
    INNER JOIN 
        client_projects cp ON a.project_id = cp.id
    INNER JOIN 
        projects p ON cp.client_id = p.id
    LEFT JOIN 
        project_done pd ON pd.project_id = cp.id AND pd.is_project_done = 1
    WHERE 
        a.project_id IS NOT NULL AND (pd.id IS NULL OR pd.is_project_done = 0)
";
$result = $conn->query($sql);

$companies = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $companies[] = [
            "id" => $row["company_id"],
            "client_name" => $row["client_name"],
            "created_at" => $row["created_at"]
        ];
    }
}

$conn->close();

echo json_encode($companies);
?>