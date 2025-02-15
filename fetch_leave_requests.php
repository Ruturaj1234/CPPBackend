<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
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
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

// SQL query to fetch leave requests along with the employee name and content column
$query = "
    SELECT lr.id, lr.employee_id, lr.subject, lr.content, lr.start_date, lr.end_date, lr.status, ed.name AS employee_name
    FROM leave_requests lr
    JOIN employee_details2 ed ON lr.employee_id = ed.eid
";

$result = mysqli_query($conn, $query);

if ($result) {
    $leaveRequests = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $leaveRequests[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $leaveRequests]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch leave requests.']);
}
?>
