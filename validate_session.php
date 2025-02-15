<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");

// Check if 'id' exists in $_SESSION
echo "Session timeout: " . ini_get('session.gc_maxlifetime');

?>
