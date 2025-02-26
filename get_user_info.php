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
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

$activity_query = "SELECT user_id FROM user_activity ORDER BY created_at DESC LIMIT 1";
$activity_result = $conn->query($activity_query);

if ($activity_result && $activity_result->num_rows > 0) {
    $activity_row = $activity_result->fetch_assoc();
    $user_id = $activity_row['user_id'];

    $employee_query = "
        SELECT eid, name, address, account_number, ifsc_code, email, 
               image, date_of_birth, contact_number 
        FROM employee_details2 
        WHERE eid = ?
    ";
    $stmt = $conn->prepare($employee_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $employee_result = $stmt->get_result();

    if ($employee_result->num_rows > 0) {
        $employee_row = $employee_result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'employee' => [
                'eid' => $employee_row['eid'], // Use eid instead of id
                'name' => $employee_row['name'],
                'address' => $employee_row['address'],
                'account_number' => $employee_row['account_number'],
                'ifsc_code' => $employee_row['ifsc_code'],
                'email' => $employee_row['email'],
                'image' => $employee_row['image'],
                'date_of_birth' => $employee_row['date_of_birth'],
                'contact_number' => $employee_row['contact_number'],
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Employee details not found']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'No activity record found']);
}

$conn->close();
?>