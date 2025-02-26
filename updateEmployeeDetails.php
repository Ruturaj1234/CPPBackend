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

$employeeId = isset($_POST['eid']) ? intval($_POST['eid']) : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : ''; // Add address
$date_of_birth = isset($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
$contact_number = isset($_POST['contact_number']) ? trim($_POST['contact_number']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if ($employeeId <= 0 || empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Invalid employee ID or name']);
    exit;
}

$imagePath = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $imageName = uniqid() . '-' . basename($_FILES['image']['name']);
    $imagePath = $uploadDir . $imageName;
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
        exit;
    }
}

$query = "
    UPDATE employee_details2 
    SET name = ?, address = ?, date_of_birth = ?, contact_number = ?, email = ?" . 
    ($imagePath ? ", image = ?" : "") . "
    WHERE eid = ?
";
$stmt = $conn->prepare($query);
if ($imagePath) {
    $stmt->bind_param("ssssssi", $name, $address, $date_of_birth, $contact_number, $email, $imagePath, $employeeId);
} else {
    $stmt->bind_param("sssssi", $name, $address, $date_of_birth, $contact_number, $email, $employeeId);
}
$success = $stmt->execute();

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Personal details updated successfully']);
} else {
    if ($imagePath && file_exists($imagePath)) unlink($imagePath);
    echo json_encode(['success' => false, 'message' => 'Failed to update details: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>