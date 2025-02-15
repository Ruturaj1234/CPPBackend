<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

include('connection.php');

$query = "SELECT COUNT(*) AS total_users FROM users";
$result = $conn->query($query);

if ($result) {
    $data = $result->fetch_assoc();
    echo json_encode(["total_users" => $data['total_users']]);
} else {
    echo json_encode(["error" => "Failed to fetch total users"]);
}
?>
