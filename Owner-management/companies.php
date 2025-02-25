<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Query to fetch distinct clients with IDs and names
$query = "SELECT DISTINCT id, client_name FROM projects WHERE client_name IS NOT NULL AND client_name != ''";
$result = $conn->query($query);

$companies = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $companies[] = [
            "id" => $row['id'],
            "client_name" => $row['client_name']
        ];
    }
}

$conn->close();

// Return JSON response
echo json_encode([
    "success" => true,
    "companies" => $companies
]);
?>