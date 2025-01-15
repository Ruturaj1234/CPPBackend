<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Database connection details
// Include database connection
include 'connection.php'; // Adjust the path as needed
require 'vendor/autoload.php';


// Get the quotation ID from the request
$quotationId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($quotationId > 0) {
    // Fetch the quotation data from the database
    $stmt = $conn->prepare("SELECT * FROM quotation WHERE id = ?");
    $stmt->bind_param("i", $quotationId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $quotation = $result->fetch_assoc();

        // Fetch associated items if needed
        $stmt = $conn->prepare("SELECT * FROM quotation_items WHERE quotation_id = ?");
        $stmt->bind_param("i", $quotationId);
        $stmt->execute();
        $itemsResult = $stmt->get_result();
        $items = $itemsResult->fetch_all(MYSQLI_ASSOC);

        // Load the library for generating PDFs
        require('vendor/autoload.php'); // Adjust the path to your vendor directory
        $pdf = new \Mpdf\Mpdf();

        // Content for the PDF
        $html = "
        <h1>Quotation</h1>
        <p>Reference No: {$quotation['reference_no']}</p>
        <p>Date: {$quotation['created_at']}</p>
        <h3>Client Details</h3>
        <p>Name: {$quotation['name']}</p>
        <p>Address: {$quotation['address']}</p>
        <h3>Subject: {$quotation['subject']}</h3>
        <h3>Products</h3>
        <table border='1' cellpadding='5'>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Description</th>
                    <th>Old PO</th>
                    <th>Box Details</th>
                </tr>
            </thead>
            <tbody>";

        foreach ($items as $item) {
            $html .= "
                <tr>
                    <td>{$item['product_name']}</td>
                    <td>{$item['description']}</td>
                    <td>{$item['old_po']}</td>
                    <td>{$item['box_details']}</td>
                </tr>";
        }

        $html .= "
            </tbody>
        </table>";

        // Write HTML content to PDF
        $pdf->WriteHTML($html);
        
        // Output PDF to browser
        $pdf->Output("quotation_{$quotationId}.pdf", 'D');
    } else {
        echo json_encode(['success' => false, 'message' => 'Quotation not found.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid quotation ID.']);
}

$conn->close();
?>