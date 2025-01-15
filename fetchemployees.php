<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Set timezone
if (!date_default_timezone_get()) {
    date_default_timezone_set('Asia/Kolkata');
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "user_db";

try {
    // Create a new PDO connection
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to fetch employee id and username
    $query = "SELECT id, username FROM users WHERE role = 'employee'";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    // Fetch all the results
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if employees exist
    if ($employees) {
        echo json_encode($employees);
    } else {
        echo json_encode(['message' => 'No employees found']);
    }
} catch (PDOException $e) {
    // Return an error response
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
