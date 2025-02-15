<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
// Database connection (Update with your actual database credentials)
// Database connection (Update with your actual database credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_db";  // Update with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch distinct client names from projects table
$query = "SELECT DISTINCT client_name FROM projects WHERE client_name IS NOT NULL";
$result = $conn->query($query);

$companies = [];
if ($result->num_rows > 0) {
    // Fetching all client names
    while ($row = $result->fetch_assoc()) {
        $companies[] = $row['client_name'];
    }
} else {
    $companies = ['No companies found'];  // Fallback if no data is found
}

$conn->close();

// Return JSON response with companies data
echo json_encode($companies);
?>
