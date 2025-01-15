<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connection.php';

if (!isset($_GET['project_id'])) {
    echo json_encode(["success" => false, "message" => "Project ID is required."]);
    exit;
}

$projectId = $_GET['project_id'];

try {
    // Fetch all employees from `users` table where role is 'employee'
    $allEmployeesQuery = "
        SELECT id AS employee_id, username
        FROM users
        WHERE role = 'employee'
    ";
    $allEmployeesResult = $conn->query($allEmployeesQuery);
    $allEmployees = $allEmployeesResult->fetch_all(MYSQLI_ASSOC);

    // Fetch assigned employees and leader for the project
    $assignedQuery = "
        SELECT employee_id, is_leader
        FROM assignments
        WHERE project_id = ?
    ";
    $stmt = $conn->prepare($assignedQuery);
    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $assignedResult = $stmt->get_result();
    $assignedEmployees = [];
    $leaderId = null;

    while ($row = $assignedResult->fetch_assoc()) {
        $assignedEmployees[] = $row['employee_id'];
        if ($row['is_leader'] == 1) {
            $leaderId = $row['employee_id'];
        }
    }

    echo json_encode([
        "success" => true,
        "employees" => $allEmployees,
        "assigned_employees" => $assignedEmployees,
        "leader_id" => $leaderId,
    ]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
