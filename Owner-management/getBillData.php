<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET,POST');
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

// Validate input
if ($clientId <= 0 || $projectId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid client or project ID.']);
    exit;
}

// Fetch project details
$projectQuery = "
    SELECT cp.project_name, cp.project_description, cp.client_name, cp.created_at
    FROM client_projects cp
    WHERE cp.client_id = ? AND cp.id = ?
";
$projectStmt = $conn->prepare($projectQuery);
$projectStmt->bind_param("ii", $clientId, $projectId);
$projectStmt->execute();
$projectResult = $projectStmt->get_result();
$projectData = $projectResult->fetch_assoc();

if (!$projectData) {
    echo json_encode(['success' => false, 'message' => 'No project found for this client.']);
    exit;
}

// Fetch quotations and items
$quotationQuery = "
    SELECT q.id AS quotation_id, q.reference_no, q.name, q.address, q.subject, q.created_at
    FROM quotation q
    WHERE q.project_id = ?
";
$quotationStmt = $conn->prepare($quotationQuery);
$quotationStmt->bind_param("i", $projectId);
$quotationStmt->execute();
$quotationResult = $quotationStmt->get_result();

$quotations = [];
while ($quotationRow = $quotationResult->fetch_assoc()) {
    $quotationId = $quotationRow['quotation_id'];

    $itemsQuery = "
        SELECT qi.product_name, qi.description, qi.quantity, qi.unit, qi.rate, (qi.quantity * qi.rate) AS total
        FROM quotation_items qi
        WHERE qi.quotation_id = ?
    ";
    $itemsStmt = $conn->prepare($itemsQuery);
    $itemsStmt->bind_param("i", $quotationId);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();

    $items = [];
    while ($itemRow = $itemsResult->fetch_assoc()) {
        $items[] = $itemRow;
    }

    $quotationRow['items'] = $items;
    $quotations[] = $quotationRow;
}

// Combine response
$response = [
    'success' => true,
    'projectName' => $projectData['project_name'],
    'clientName' => $projectData['client_name'],
    'date' => date('Y-m-d'),
    'invoiceNo' => 'INV-' . strtoupper(uniqid()), // Generate a unique invoice number
    'items' => $quotations,
    'taxableValue' => 0, // Will be calculated in frontend
    'igst' => 0,         // Will be calculated in frontend
    'grandTotal' => 0    // Will be calculated in frontend
];

echo json_encode($response);
$conn->close();
?>
