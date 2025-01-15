<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST'); // Allow both GET and POST methods
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
// Include the database connection

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_db"; // Update this with your actual database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the POST data
$data = json_decode(file_get_contents("php://input"), true);

// Prepare and bind
$stmt = $conn->prepare("UPDATE employee_details2 SET salary_basic = ?, salary_da = ?, salary_hra = ?, salary_maintenance = ? WHERE id = ?");
$stmt->bind_param("ddddd", $salary_basic, $salary_da, $salary_hra, $salary_maintenance, $employee_id);

// Set parameters and execute
$salary_basic = $data['salary_basic'];
$salary_da = $data['salary_da'];
$salary_hra = $data['salary_hra'];
$salary_maintenance = $data['salary_maintenance'];
$employee_id = $data['employee_id'];

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
