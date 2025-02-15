<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

session_unset();
session_destroy();

echo json_encode(['success' => true, 'message' => 'Logout successful']);
?>