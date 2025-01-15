<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Database connection
$host = "localhost";
$username = "root";
$password = ""; // Replace with your database password
$dbname = "user_db";

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]));
}

// Get the POST data
$data = json_decode(file_get_contents("php://input"), true);

$project_id = isset($data['project_id']) ? intval($data['project_id']) : null;
$employee_ids = isset($data['employee_ids']) ? $data['employee_ids'] : [];
$project_leader = isset($data['project_leader']) ? intval($data['project_leader']) : null;
$message = isset($data['message']) ? $data['message'] : "";

// Check if required fields are present
if (!$project_id || empty($employee_ids)) {
    echo json_encode([
        "success" => false,
        "message" => "Project ID and Employee IDs are required."
    ]);
    exit;
}

// Insert assignments into the database
foreach ($employee_ids as $employee_id) {
    // Determine if the employee is the project leader
    $is_leader = ($employee_id == $project_leader) ? 1 : 0;

    // Insert into assignments table, including message
    $sql = "INSERT INTO assignments (project_id, employee_id, is_leader, message) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("iiis", $project_id, $employee_id, $is_leader, $message);
        if (!$stmt->execute()) {
            echo json_encode([
                "success" => false,
                "message" => "Failed to assign project to employee ID $employee_id: " . $stmt->error
            ]);
            $stmt->close();
            $conn->close();
            exit;
        }
        $stmt->close();
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to prepare statement: " . $conn->error
        ]);
        $conn->close();
        exit;
    }
}

echo json_encode([
    "success" => true,
    "message" => "Project assigned successfully."
]);

// Close connection
$conn->close();
?>