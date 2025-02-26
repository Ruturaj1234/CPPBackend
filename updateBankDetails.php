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

// Get JSON input
$input = json_decode(file_get_contents("php://input"), true);
$employeeId = isset($input['eid']) ? intval($input['eid']) : 0;
$account_number = isset($input['account_number']) ? trim($input['account_number']) : '';
$ifsc_code = isset($input['ifsc_code']) ? trim($input['ifsc_code']) : '';

// Debug logging
file_put_contents('debug.log', "Received: eid=$employeeId, account_number=$account_number, ifsc_code=$ifsc_code\n", FILE_APPEND);

if ($employeeId <= 0 || empty($account_number) || empty($ifsc_code)) {
    echo json_encode(['success' => false, 'message' => 'Invalid employee ID, account number, or IFSC code']);
    exit;
}

$query = "UPDATE employee_details2 SET account_number = ?, ifsc_code = ? WHERE eid = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssi", $account_number, $ifsc_code, $employeeId);
$success = $stmt->execute();

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Bank details updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update bank details: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>