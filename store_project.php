
<?php
// Allow CORS
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json'); // Ensure we are sending JSON data

// Your existing code starts here
date_default_timezone_set('Asia/Kolkata'); // Set the timezone to IST

$servername = "localhost"; 
$username = "root";        
$password = "";            
$dbname = "user_db";       

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Check if client name is provided
if (isset($_POST['clientName']) && !empty($_POST['clientName'])) {
    $clientName = $_POST['clientName'];
    $createdAt = date('Y-m-d H:i:s'); // Get the current timestamp

    $sql = "INSERT INTO projects (client_name, created_at) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error]);
        exit();
    }

    // Bind parameters and execute the query
    $stmt->bind_param("ss", $clientName, $createdAt);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Client added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add client: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Client name is required']);
}

$conn->close();
?>