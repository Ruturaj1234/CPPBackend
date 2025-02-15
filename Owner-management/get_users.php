<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

include('connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $role = $_GET['role'] ?? ''; // Role can be passed as a query parameter

    // Prepare SQL query based on role
    if ($role) {
        $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE role = ?");
        $stmt->bind_param("s", $role);
    } else {
        $stmt = $conn->prepare("SELECT id, username, role FROM users");
    }

    // Execute and fetch results
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode($users);
    } else {
        echo json_encode(["error" => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>
