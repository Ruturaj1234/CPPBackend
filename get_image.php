<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$imagePath = isset($_GET['path']) ? $_GET['path'] : '';
$fullPath = __DIR__ . '/' . $imagePath;

if (file_exists($fullPath)) {
    $mimeType = mime_content_type($fullPath);
    header("Content-Type: $mimeType");
    header("Content-Length: " . filesize($fullPath));
    readfile($fullPath);
    exit;
} else {
    header("HTTP/1.1 404 Not Found");
    echo json_encode(["error" => "Image not found"]);
    exit;
}
?>