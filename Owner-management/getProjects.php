<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection (Update with your actual database credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_db";  // Update with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get client name from the query parameter
$client_name = isset($_GET['client_name']) ? $_GET['client_name'] : '';

// Check if client_name is provided
if (!empty($client_name)) {
    // Query to fetch project details for the specific client
    $query = "SELECT project_name, project_description FROM client_projects WHERE client_name = ? AND project_name IS NOT NULL";
    
    // Prepare the statement
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $client_name);  // Bind client_name parameter to the query
    $stmt->execute();
    
    // Get the result
    $result = $stmt->get_result();

    $projects = [];
    if ($result->num_rows > 0) {
        // Fetching all project details
        while ($row = $result->fetch_assoc()) {
            $projects[] = [
                'project_name' => $row['project_name'],
                'project_description' => $row['project_description']
            ];
        }
    } else {
        $projects = []; // Return an empty array if no projects are found
    }
    
    // Close connection
    $stmt->close();
    $conn->close();
    
    // Return the project details as a JSON response
    echo json_encode($projects);
} else {
    echo json_encode(['error' => 'Client name not provided']);
}
?>
