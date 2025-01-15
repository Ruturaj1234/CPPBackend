<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Database configuration
$servername = "localhost"; 
$username = "root";        
$password = "";            
$dbname = "user_db";       

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// SQL query to fetch clients
$sql = "SELECT id, client_name, created_at FROM projects ORDER BY created_at DESC";
$result = $conn->query($sql);

$clients = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $clients[] = $row;
    }
}

echo json_encode(['success' => true, 'clients' => $clients]);

$conn->close();
?>
