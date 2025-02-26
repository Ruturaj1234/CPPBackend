<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

// Fetch top 5 clients by project count
$query = "
    SELECT 
        p.client_name,
        COUNT(cp.id) AS project_count
    FROM projects p
    LEFT JOIN client_projects cp ON p.id = cp.client_id
    GROUP BY p.id, p.client_name
    ORDER BY project_count DESC
    LIMIT 5
";
$result = $conn->query($query);

if ($result) {
    $clients = [];
    $counts = [];
    while ($row = $result->fetch_assoc()) {
        $clients[] = $row['client_name'] ?: "Unknown Client";
        $counts[] = (int)$row['project_count'];
    }
    echo json_encode([
        'success' => true,
        'clients' => $clients,
        'counts' => $counts
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
}

$conn->close();
?>