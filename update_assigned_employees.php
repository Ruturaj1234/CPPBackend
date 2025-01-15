<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST'); // Allow both GET and POST methods
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
// Include the database connection file
include('connection.php');

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

// Debugging: Output the received data to check if it's being passed correctly
// You can remove this after you've confirmed it's working.
if (is_null($data)) {
    echo json_encode(['success' => false, 'message' => 'No data received.']);
    exit;
}

// Extract data from the received JSON
$project_id = isset($data['project_id']) ? $data['project_id'] : null;
$leader_id = isset($data['leader_id']) ? $data['leader_id'] : null;
$allocated_employees = isset($data['allocated_employees']) ? $data['allocated_employees'] : null;

// Check if necessary fields are set
if ($project_id === null || $leader_id === null || $allocated_employees === null) {
    echo json_encode(['success' => false, 'message' => 'Missing data: project_id, leader_id or allocated_employees.']);
    exit;
}

// Start a transaction to ensure atomicity
$conn->begin_transaction();

try {
    // 1. Delete any existing assignments for the project
    $stmt_delete = $conn->prepare("DELETE FROM assignments WHERE project_id = ?");
    $stmt_delete->bind_param("i", $project_id);
    $stmt_delete->execute();

    // 2. Insert the newly allocated employees into the assignments table
    $stmt_insert = $conn->prepare("INSERT INTO assignments (project_id, employee_id, is_leader, assigned_at) VALUES (?, ?, ?, NOW())");

    // Loop through each allocated employee
    foreach ($allocated_employees as $employee_id) {
        // Check if this employee is the leader
        $is_leader = ($employee_id == $leader_id) ? 1 : 0;
        
        // Insert the employee into the assignments table
        $stmt_insert->bind_param("iii", $project_id, $employee_id, $is_leader);
        $stmt_insert->execute();
    }

    // Commit the transaction if everything is successful
    $conn->commit();

    // Return a success response
    echo json_encode(['success' => true, 'message' => 'Employees assigned successfully']);

    // Close the prepared statements
    $stmt_delete->close();
    $stmt_insert->close();

} catch (Exception $e) {
    // Rollback the transaction if any error occurs
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// Close the database connection
$conn->close();
?>
