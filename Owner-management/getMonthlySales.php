<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

// Get year from query parameter, default to current year
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Validate year (optional, restrict to 2023-2025 if needed)
if ($year < 2023 || $year > 2025) {
    $year = date('Y'); // Fallback to current year
}

// Fetch sales data grouped by month for the specified year
$query = "
    SELECT 
        MONTH(q.created_at) AS month,
        SUM(qi.amount) AS total_sales
    FROM quotation q
    JOIN quotation_items qi ON q.id = qi.quotation_id
    WHERE YEAR(q.created_at) = ?
    GROUP BY MONTH(q.created_at)
    ORDER BY MONTH(q.created_at)
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $year);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    $sales = array_fill(1, 12, 0); // Initialize array for all 12 months
    while ($row = $result->fetch_assoc()) {
        $sales[$row['month']] = (float)$row['total_sales']; // Exact amounts, no division
    }
    echo json_encode([
        'success' => true,
        'sales' => array_values($sales) // Convert to 0-indexed array for frontend
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>