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

$client_name = isset($_GET['client_name']) ? $_GET['client_name'] : '';
if (empty($client_name)) {
    echo json_encode(['success' => false, 'message' => 'Client name not provided']);
    exit;
}

$query = "SELECT id FROM projects WHERE client_name = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $client_name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['success' => true, 'clientId' => $row['id']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Client not found']);
}

$stmt->close();
$conn->close();
?>