<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

include('connection.php');

$query = "SELECT id, username, role FROM users WHERE role = 'employee'";
$result = $conn->query($query);

if ($result) {
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
} else {
    echo json_encode(["error" => "Failed to fetch employees"]);
}
?>
