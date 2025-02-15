<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Leave Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch the latest logged-in user from the user_activity table
    $latestActivitySql = "SELECT user_id FROM user_activity ORDER BY created_at DESC LIMIT 1";
    $result = $conn->query($latestActivitySql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $employee_id = $row['user_id']; // Get the latest user's ID
    } else {
        echo json_encode(["status" => "error", "message" => "No active user found."]);
        exit;
    }

    // Get other form data
    $subject = $conn->real_escape_string($_POST['subject']);
    $content = $conn->real_escape_string($_POST['content']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Insert into leave_requests table
    $sql = "INSERT INTO leave_requests (employee_id, subject, content, start_date, end_date, status) 
            VALUES ('$employee_id', '$subject', '$content', '$start_date', '$end_date', 'pending')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Leave request submitted successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
    }
}

// Close connection
$conn->close();
?>
