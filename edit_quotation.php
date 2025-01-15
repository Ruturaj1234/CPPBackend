<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch quotation details
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid quotation ID.']);
        exit;
    }

    $query = "SELECT * FROM quotation WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'quotation' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Quotation not found.']);
    }

    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update quotation details
    $data = json_decode(file_get_contents("php://input"), true);

    $id = isset($data['id']) ? intval($data['id']) : 0;
    $referenceNo = isset($data['reference_no']) ? $data['reference_no'] : '';
    $name = isset($data['name']) ? $data['name'] : '';
    $address = isset($data['address']) ? $data['address'] : '';
    $subject = isset($data['subject']) ? $data['subject'] : '';
    $products = isset($data['products']) ? $data['products'] : [];

    // Validation
    if ($id <= 0 || empty($referenceNo) || empty($name) || empty($address) || empty($subject)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data. All fields are required.']);
        exit;
    }

    // Update the quotation
    $updateQuery = "
        UPDATE quotation
        SET reference_no = ?, name = ?, address = ?, subject = ?
        WHERE id = ?
    ";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssssi", $referenceNo, $name, $address, $subject, $id);

    if ($stmt->execute()) {
        // Update product items if any are provided
        if (!empty($products)) {
            foreach ($products as $product) {
                $productId = isset($product['id']) ? intval($product['id']) : 0;
                $productName = isset($product['product_name']) ? $product['product_name'] : '';
                $productDescription = isset($product['description']) ? $product['description'] : '';
                $productOldPo = isset($product['old_po']) ? $product['old_po'] : '';
                $productQuantity = isset($product['quantity']) ? intval($product['quantity']) : 0;
                $productUnit = isset($product['unit']) ? $product['unit'] : '';
                $productRate = isset($product['rate']) ? floatval($product['rate']) : 0;
                $productAmount = isset($product['amount']) ? floatval($product['amount']) : 0;

                // Ensure product data is valid
                if (empty($productName) || $productQuantity <= 0 || $productRate <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid product data.']);
                    exit;
                }

                // Update the product in the quotation_items table
                $productQuery = "
                    UPDATE quotation_items
                    SET product_name = ?, description = ?, old_po = ?, quantity = ?, unit = ?, rate = ?, amount = ?
                    WHERE id = ?
                ";
                $productStmt = $conn->prepare($productQuery);
                $productStmt->bind_param("sssisdii", $productName, $productDescription, $productOldPo, $productQuantity, $productUnit, $productRate, $productAmount, $productId);

                if (!$productStmt->execute()) {
                    echo json_encode(['success' => false, 'message' => 'Failed to update product items.']);
                    $productStmt->close();
                    exit;
                }
                $productStmt->close();
            }
        }

        echo json_encode(['success' => true, 'message' => 'Quotation and product items updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update quotation.']);
    }

    $stmt->close();
}

$conn->close();
?>
