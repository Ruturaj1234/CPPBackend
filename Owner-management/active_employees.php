<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

include('connection.php');

$query = "SELECT COUNT(*) AS active_employees FROM users WHERE role = 'employee'";
$result = $conn->query($query);

if ($result) {
    $data = $result->fetch_assoc();
    echo json_encode(["active_employees" => $data['active_employees']]);
} else {
    echo json_encode(["error" => "Failed to fetch active employees"]);
}
?>
