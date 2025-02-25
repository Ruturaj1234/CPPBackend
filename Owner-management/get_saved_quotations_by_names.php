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

$projectId = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
if ($projectId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid project ID']);
    exit;
}

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

    $quotationRow['items'] = $items;
    $quotations[] = $quotationRow;
}

$response = [
    'success' => !empty($quotations),
    'quotations' => $quotations
];

echo json_encode($response);

$conn->close();
?>