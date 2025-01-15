<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

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

// SQL query to fetch companies assigned a project
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
    WHERE 
        a.project_id IS NOT NULL
";

$result = $conn->query($sql);

$companies = [];

// Fetch the results
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

// Return the result as JSON
echo json_encode($companies);
?>
