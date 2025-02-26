<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
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

$input = json_decode(file_get_contents("php://input"), true);
$employeeId = isset($input['eid']) ? intval($input['eid']) : 0;
$currentPassword = isset($input['current_password']) ? $input['current_password'] : '';
$newPassword = isset($input['new_password']) ? $input['new_password'] : '';
$confirmPassword = isset($input['confirm_password']) ? $input['confirm_password'] : '';

if ($employeeId <= 0 || empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if ($newPassword !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'New password and confirmation do not match']);
    exit;
}

$query = "SELECT u.password FROM users u JOIN employee_details2 ed ON u.id = ed.eid WHERE ed.eid = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($currentPassword !== $row['password']) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Employee not found in users']);
    exit;
}

$updateQuery = "UPDATE users SET password = ? WHERE id = (SELECT eid FROM employee_details2 WHERE eid = ?)";
$stmt = $conn->prepare($updateQuery);
$stmt->bind_param("si", $newPassword, $employeeId);
$success = $stmt->execute();

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to change password: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>