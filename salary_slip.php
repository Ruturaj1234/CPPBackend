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
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Get the logged-in user's ID from the user_activity table
$latestActivitySql = "SELECT user_id FROM user_activity ORDER BY created_at DESC LIMIT 1";
$result = $conn->query($latestActivitySql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $employee_id = $row['user_id']; // Get the latest user's ID
} else {
    echo json_encode(["success" => false, "message" => "No active user found."]);
    exit;
}

// Get the selected month from the request
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : '';

if (empty($selectedMonth)) {
    echo json_encode(["success" => false, "message" => "Month is required."]);
    exit;
}

// Fix: Convert `January-2025` to `2025-01`
$dateObj = DateTime::createFromFormat('F-Y', $selectedMonth);
if (!$dateObj) {
    echo json_encode(["success" => false, "message" => "Invalid month format."]);
    exit;
}
$formattedMonth = $dateObj->format('Y-m'); // Convert to `YYYY-MM`

// Query to fetch salary data for the logged-in employee for the selected month
$sql = "
    SELECT s.salary_basic, s.salary_da, s.salary_hra, s.salary_maintenance, s.total_salary, s.salary_date, 
           e.account_number, e.ifsc_code
    FROM salaries s
    JOIN employee_details2 e ON s.employee_id = e.eid
    WHERE s.employee_id = ? AND DATE_FORMAT(s.salary_date, '%Y-%m') = ?
";

// Prepare statement to prevent SQL injection
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $employee_id, $formattedMonth); // "i" for integer, "s" for string (month format)
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Check if data exists for the selected month
if ($result->num_rows > 0) {
    $salaryData = $result->fetch_assoc();
    echo json_encode([
        "success" => true,
        "message" => "Salary data found.",
        "data" => $salaryData
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "No salary data found for the selected month."
    ]);
}

// Close the connection
$conn->close();
?>
