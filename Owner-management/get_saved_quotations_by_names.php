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

// Get client_name and project_name from query parameters
$clientName = isset($_GET['client_name']) ? $_GET['client_name'] : '';
$projectName = isset($_GET['project_name']) ? $_GET['project_name'] : '';

// Debug input parameters
if (empty($clientName) || empty($projectName)) {
    echo json_encode(['success' => false, 'message' => 'Invalid client name or project name.']);
    exit;
}

// Fetch client ID based on client name
$clientQuery = "SELECT id FROM projects WHERE client_name = ?";
$clientStmt = $conn->prepare($clientQuery);
$clientStmt->bind_param("s", $clientName);
$clientStmt->execute();
$clientResult = $clientStmt->get_result();

if ($clientResult->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Client not found.']);
    exit;
}

$clientData = $clientResult->fetch_assoc();
$clientId = $clientData['id'];

// Fetch project ID based on project name and client ID
$projectQuery = "SELECT id FROM client_projects WHERE client_id = ? AND project_name = ?";
$projectStmt = $conn->prepare($projectQuery);
$projectStmt->bind_param("is", $clientId, $projectName);
$projectStmt->execute();
$projectResult = $projectStmt->get_result();

if ($projectResult->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Project not found for this client.']);
    exit;
}

$projectData = $projectResult->fetch_assoc();
$projectId = $projectData['id'];

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
