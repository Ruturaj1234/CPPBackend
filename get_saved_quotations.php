<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

// Get clientId and projectId from query parameters
$clientId = isset($_GET['clientId']) ? intval($_GET['clientId']) : 0;
$projectId = isset($_GET['projectId']) ? intval($_GET['projectId']) : 0;

// Debug input parameters
if ($clientId <= 0 || $projectId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid client or project ID.']);
    exit;
}

// Debug: Check input values
// Uncomment this line during debugging to view inputs
// echo json_encode(['debug_clientId' => $clientId, 'debug_projectId' => $projectId]);

// Check if the project belongs to the client
$checkQuery = "
    SELECT COUNT(*) AS count
    FROM client_projects
    WHERE client_id = ? AND id = ?
";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("ii", $clientId, $projectId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
$checkData = $checkResult->fetch_assoc();

if ($checkData['count'] == 0) {
    echo json_encode(['success' => false, 'message' => 'No project found for this client.']);
    exit;
}

// Fetch quotations for the project
$quotationQuery = "
    SELECT id, reference_no, name, address, subject, created_at
    FROM quotation
    WHERE project_id = ?
";
$quotationStmt = $conn->prepare($quotationQuery);
$quotationStmt->bind_param("i", $projectId);
$quotationStmt->execute();
$quotationResult = $quotationStmt->get_result();

$quotations = [];
while ($quotationRow = $quotationResult->fetch_assoc()) {
    // Fetch items for each quotation
    $quotationId = $quotationRow['id'];
    $itemsQuery = "
        SELECT id, product_name, description, old_po, quantity, unit, rate, amount
        FROM quotation_items
        WHERE quotation_id = ?
    ";
    $itemsStmt = $conn->prepare($itemsQuery);
    $itemsStmt->bind_param("i", $quotationId);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();

    $items = [];
    while ($itemRow = $itemsResult->fetch_assoc()) {
        $items[] = $itemRow;
    }

    // Add items to the quotation
    $quotationRow['items'] = $items;
    $quotations[] = $quotationRow;
}

// Debug: Check if quotations are found
if (empty($quotations)) {
    echo json_encode(['success' => false, 'message' => 'No quotations found for this project.']);
    exit;
}

// Return combined response
$response = [
    'success' => true,
    'quotations' => $quotations
];

echo json_encode($response);

// Close connection
$conn->close();
?>
