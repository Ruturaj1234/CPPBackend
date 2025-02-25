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

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the latest logged-in user ID
$userQuery = "SELECT user_id FROM user_activity ORDER BY created_at DESC LIMIT 1";
$userResult = $conn->query($userQuery);
if (!$userResult || $userResult->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Failed to retrieve logged-in user."]);
    exit;
}
$userRow = $userResult->fetch_assoc();
$user_id = $userRow['user_id'];

$projectId = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;

if ($projectId > 0) {
    // Fetch details of a single project for any assigned user
    $sql = "
        SELECT cp.id AS project_id, cp.project_name, cp.project_description, cp.client_name, a.message, e.name AS leader_name, q.created_at AS quotation_date, a.is_leader
        FROM assignments a
        JOIN client_projects cp ON a.project_id = cp.id
        JOIN assignments leader_a ON leader_a.project_id = cp.id AND leader_a.is_leader = 1
        LEFT JOIN employee_details2 e ON leader_a.employee_id = e.eid
        LEFT JOIN quotation q ON q.project_id = cp.id
        LEFT JOIN project_done pd ON pd.project_id = cp.id AND pd.is_project_done = 1
        WHERE cp.id = ? AND a.employee_id = ? AND (pd.id IS NULL OR pd.is_project_done = 0)
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $projectId, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

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
            'is_leader' => (int)$row['is_leader'],
            'products' => [],
            'associated_employees' => []
        ];

        // Fetch products
        $productSql = "
            SELECT qi.product_name, qi.quantity
            FROM quotation_items qi
            JOIN quotation q ON qi.quotation_id = q.id
            WHERE q.project_id = ?
        ";
        $productStmt = $conn->prepare($productSql);
        $productStmt->bind_param("i", $projectId);
        $productStmt->execute();
        $productResult = $productStmt->get_result();

        while ($productRow = $productResult->fetch_assoc()) {
            $projectDetails['products'][] = [
                'name' => $productRow['product_name'],
                'quantity' => $productRow['quantity']
            ];
        }
        $productStmt->close();

        // Fetch associated employees (non-leaders)
        $employeeSql = "
            SELECT DISTINCT e.name
            FROM assignments a
            LEFT JOIN employee_details2 e ON a.employee_id = e.eid
            WHERE a.project_id = ? AND a.is_leader = 0
        ";
        $employeeStmt = $conn->prepare($employeeSql);
        $employeeStmt->bind_param("i", $projectId);
        $employeeStmt->execute();
        $employeeResult = $employeeStmt->get_result();

        while ($employeeRow = $employeeResult->fetch_assoc()) {
            if ($employeeRow['name']) { // Only add non-null names
                $projectDetails['associated_employees'][] = $employeeRow['name'];
            }
        }
        $employeeStmt->close();
    }

    echo json_encode([
        'success' => !empty($projectDetails),
        'project' => $projectDetails,
    ]);
} else {
    // Fetch projects where the user is a leader
    $leaderSql = "
        SELECT cp.id AS project_id, cp.project_name, cp.project_description, cp.client_name, a.message, e.name AS leader_name, a.is_leader
        FROM assignments a
        JOIN client_projects cp ON a.project_id = cp.id
        LEFT JOIN employee_details2 e ON a.employee_id = e.eid
        LEFT JOIN project_done pd ON pd.project_id = cp.id AND pd.is_project_done = 1
        WHERE a.employee_id = ? AND a.is_leader = 1 AND (pd.id IS NULL OR pd.is_project_done = 0)
    ";
    $leaderStmt = $conn->prepare($leaderSql);
    $leaderStmt->bind_param("i", $user_id);
    $leaderStmt->execute();
    $leaderResult = $leaderStmt->get_result();

    $leaderProjects = [];
    while ($row = $leaderResult->fetch_assoc()) {
        $leaderProjects[] = [
            'id' => $row['project_id'],
            'project_name' => $row['project_name'],
            'project_description' => $row['project_description'],
            'client_name' => $row['client_name'],
            'message' => $row['message'],
            'project_leader' => $row['leader_name'],
            'is_leader' => (int)$row['is_leader']
        ];
    }

    // Fetch projects where the user is an employee (not leader)
    $employeeSql = "
        SELECT cp.id AS project_id, cp.project_name, cp.project_description, cp.client_name, a.message, leader_e.name AS leader_name, a.is_leader
        FROM assignments a
        JOIN client_projects cp ON a.project_id = cp.id
        JOIN assignments leader_a ON leader_a.project_id = cp.id AND leader_a.is_leader = 1
        LEFT JOIN employee_details2 leader_e ON leader_a.employee_id = leader_e.eid
        LEFT JOIN project_done pd ON pd.project_id = cp.id AND pd.is_project_done = 1
        WHERE a.employee_id = ? AND a.is_leader = 0 AND (pd.id IS NULL OR pd.is_project_done = 0)
    ";
    $employeeStmt = $conn->prepare($employeeSql);
    $employeeStmt->bind_param("i", $user_id);
    $employeeStmt->execute();
    $employeeResult = $employeeStmt->get_result();

    $employeeProjects = [];
    while ($row = $employeeResult->fetch_assoc()) {
        $employeeProjects[] = [
            'id' => $row['project_id'],
            'project_name' => $row['project_name'],
            'project_description' => $row['project_description'],
            'client_name' => $row['client_name'],
            'message' => $row['message'],
            'project_leader' => $row['leader_name'],
            'is_leader' => (int)$row['is_leader']
        ];
    }

    echo json_encode([
        'success' => true,
        'leader_projects' => $leaderProjects,
        'employee_projects' => $employeeProjects
    ]);

    $leaderStmt->close();
    $employeeStmt->close();
}

$conn->close();
?>