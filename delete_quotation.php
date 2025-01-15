<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
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

// Get the quotation ID from query parameters
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid quotation ID.']);
    exit;
}

// Begin transaction
$conn->begin_transaction();

try {
    // Delete related items from the quotation_items table
    $deleteItemsQuery = "DELETE FROM quotation_items WHERE quotation_id = ?";
    $stmt = $conn->prepare($deleteItemsQuery);
    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        throw new Exception('Failed to delete items from quotation_items table.');
    }
    
    // Delete the quotation from the quotation table
    $deleteQuery = "DELETE FROM quotation WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        throw new Exception('Failed to delete quotation.');
    }

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Quotation and related items deleted successfully.']);
} catch (Exception $e) {
    // Rollback transaction in case of error
    $conn->rollback();

    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>
