<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

// Fetch the latest user_id from the users table
$user_query = "SELECT user_id FROM user_activity ORDER BY id DESC LIMIT 1";
$user_result = $conn->query($user_query);

if ($user_result && $user_result->num_rows > 0) {
    $user_row = $user_result->fetch_assoc();
    $latest_user_id = $user_row['user_id'];

    // Fetch assigned projects for the latest user_id, including project_description
    $query = "SELECT cp.id, cp.project_name, cp.project_description 
              FROM client_projects cp
              INNER JOIN assignments a ON cp.id = a.project_id
              WHERE a.employee_id = ?
              GROUP BY cp.id";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $latest_user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }

    if (!empty($projects)) {
        echo json_encode(["success" => true, "user_id" => $latest_user_id, "projects" => $projects]);
    } else {
        echo json_encode(["success" => false, "user_id" => $latest_user_id, "message" => "No assigned projects found for this user."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "No users found in the database."]);
}

$conn->close();
?>
