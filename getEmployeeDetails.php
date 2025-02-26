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

$employeeId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($employeeId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid employee ID']);
    exit;
}

$query = "
    SELECT ed.name, ed.age, ed.address, ed.image, ed.date_of_birth, ed.contact_number, ed.email,
           ed.account_number, ed.ifsc_code
    FROM employee_details2 ed
    WHERE ed.id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $employee = $result->fetch_assoc();
    echo json_encode(['success' => true, 'employee' => $employee]);
} else {
    echo json_encode(['success' => false, 'message' => 'Employee not found']);
}

$stmt->close();
$conn->close();
?>