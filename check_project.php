<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST'); // Allow both GET and POST methods
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Include the database connection
include('connection.php');

// Get the POST data
// Read the input from the POST request
$input = json_decode(file_get_contents('php://input'), true);
$clientId = $input['client_id'];
$projectName = $input['project_name'];


// Get the project ID from the project name in the client_projects table
$sql = "
    SELECT id
    FROM client_projects
    WHERE client_id = ? AND project_name = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $clientId, $projectName);
$stmt->execute();
$result = $stmt->get_result();

// Check if the project exists
if ($result->num_rows > 0) {
    // Get the project_id
    $project = $result->fetch_assoc();
    $projectId = $project['id'];

    // Now check if the project is already assigned in the assignments table
    $sql_check_assignment = "
        SELECT id
        FROM assignments
        WHERE project_id = ?
    ";
    $stmt_check = $conn->prepare($sql_check_assignment);
    $stmt_check->bind_param("i", $projectId);
    $stmt_check->execute();
    $assignment_result = $stmt_check->get_result();

    if ($assignment_result->num_rows > 0) {
        // If project is already assigned
        echo json_encode(["status" => "exists", "message" => "This project is already assigned"]);
    } else {
        // If project hasn't been assigned yet
        echo json_encode(["status" => "success"]);
    }
} else {
    // If project doesn't exist
    echo json_encode(["status" => "error", "message" => "Project not found"]);
}
?>
