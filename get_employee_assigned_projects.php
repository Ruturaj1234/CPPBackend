<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$dbname = "user_db";

// Create a connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$projectId = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;

if ($projectId > 0) {
    // Fetch the details of a single project
    $sql = "
        SELECT cp.id AS project_id, cp.project_name, cp.project_description, cp.client_name, a.message, e.name AS leader_name, q.created_at AS quotation_date
        FROM assignments a
        JOIN client_projects cp ON a.project_id = cp.id
        LEFT JOIN employee_details2 e ON a.employee_id = e.id
        LEFT JOIN quotation q ON q.project_id = cp.id
        WHERE cp.id = $projectId
    ";

    $result = $conn->query($sql);
    $projectDetails = [];

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $projectDetails = [
            'id' => $row['project_id'],
            'project_name' => $row['project_name'],
            'project_description' => $row['project_description'],
            'client_name' => $row['client_name'],
            'message' => $row['message'],
            'project_leader' => $row['leader_name'],
            'quotation_date' => $row['quotation_date'],
            'products' => [] // Placeholder for products, we'll fetch them below
        ];

        // Fetch products associated with the project
        $productSql = "
            SELECT qi.product_name, qi.quantity
            FROM quotation_items qi
            JOIN quotation q ON qi.quotation_id = q.id
            WHERE q.project_id = $projectId
        ";

        $productResult = $conn->query($productSql);
        if ($productResult->num_rows > 0) {
            while ($productRow = $productResult->fetch_assoc()) {
                $projectDetails['products'][] = [
                    'name' => $productRow['product_name'],
                    'quantity' => $productRow['quantity']
                ];
            }
        }
    }

    echo json_encode([
        'success' => !empty($projectDetails),
        'project' => $projectDetails,
    ]);
} else {
    // Fetch all assigned projects
    $sql = "
        SELECT cp.id AS project_id, cp.project_name, cp.project_description, cp.client_name, a.message, e.name AS leader_name
        FROM assignments a
        JOIN client_projects cp ON a.project_id = cp.id
        LEFT JOIN employee_details2 e ON a.employee_id = e.id
        WHERE a.is_leader = 1
    ";

    $result = $conn->query($sql);

    $projects = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $projects[] = [
                'id' => $row['project_id'],
                'project_name' => $row['project_name'],
                'project_description' => $row['project_description'],
                'client_name' => $row['client_name'],
                'message' => $row['message'],
                'project_leader' => $row['leader_name']
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'projects' => $projects
    ]);
}

$conn->close();
?>
