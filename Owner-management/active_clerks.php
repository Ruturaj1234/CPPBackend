<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

include('connection.php');

$query = "SELECT COUNT(*) AS active_clerks FROM users WHERE role = 'clerk'";
$result = $conn->query($query);

if ($result) {
    $data = $result->fetch_assoc();
    echo json_encode(["active_clerks" => $data['active_clerks']]);
} else {
    echo json_encode(["error" => "Failed to fetch active clerks"]);
}
?>
