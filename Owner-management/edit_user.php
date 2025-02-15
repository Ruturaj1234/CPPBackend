<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

include('connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $password, $role, $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => "User updated successfully"]);
    } else {
        echo json_encode(["error" => "Failed to update user"]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>
