<?php
// Enable CORS for the API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Set timezone dynamically
if (!date_default_timezone_get()) {
    date_default_timezone_set('Asia/Kolkata');
}

// Database connection
$conn = new mysqli("localhost", "root", "", "user_db");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // Map frontend variables to backend
    $projectId = $data['projectId'] ?? null;
    $referenceNo = $data['referenceNo'] ?? null;
    $name = $data['name'] ?? null;
    $address = $data['address'] ?? null;
    $subject = $data['subject'] ?? null;
    $products = $data['products'] ?? [];

    if (!$projectId || !$referenceNo || !$name || !$address) {
        echo json_encode(["success" => false, "message" => "Missing required fields"]);
        exit;
    }

    // Insert into quotation table
    $stmt = $conn->prepare("INSERT INTO quotation (project_id, reference_no, name, address, subject, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issss", $projectId, $referenceNo, $name, $address, $subject);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false, "message" => "Error saving quotation: " . $stmt->error]);
        $stmt->close();
        $conn->close();
        exit;
    }

    $quotationId = $stmt->insert_id;

    // Insert products into quotation_items table
    $stmt = $conn->prepare("INSERT INTO quotation_items (quotation_id, product_name, description, old_po, quantity, unit, rate, amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($products as $product) {
        $productName = $product['productName'] ?? null;
        $description = $product['description'] ?? null;
        $oldPO = $product['oldPO'] ?? null;
        $quantity = $product['qty'] ?? 0;
        $unit = $product['unit'] ?? null;
        $rate = $product['rate'] ?? 0;
        $amount = $product['amount'] ?? 0;

        if (!$productName || !$quantity || !$rate || !$amount) {
            echo json_encode(["success" => false, "message" => "Missing product details"]);
            $stmt->close();
            $conn->close();
            exit;
        }

        $stmt->bind_param("isssdsdd", $quotationId, $productName, $description, $oldPO, $quantity, $unit, $rate, $amount);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false, "message" => "Error saving product: " . $stmt->error]);
            $stmt->close();
            $conn->close();
            exit;
        }
    }
    $stmt->close();

    // Success response
    echo json_encode(["success" => true, "message" => "Quotation and products saved successfully"]);
}

$conn->close();
?>
