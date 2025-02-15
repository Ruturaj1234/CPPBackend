<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection parameters
$servername = "localhost";
$username = "root"; // Default for XAMPP
$password = ""; // Default password is empty for XAMPP
$dbname = "user_db"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Read the raw input
$input = file_get_contents('php://input');

// Decode the JSON input
$data = json_decode($input, true);

// Check if username and password are set
if (isset($data['username']) && isset($data['password'])) {
    $username = $data['username'];
    $password = $data['password'];

    // Prepare the SQL statement to check username, password, and role
    $stmt = $conn->prepare("SELECT id, role FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);

    // Execute the query
    $stmt->execute();
    $stmt->store_result(); // Store the result to clear any previous results

    // Check if the user exists and fetch the result
    if ($stmt->num_rows > 0) {
        // Bind result variables
        $stmt->bind_result($id, $role);

        // Fetch the result
        $stmt->fetch();

        // Insert activity record into user_activity table
        $activity_stmt = $conn->prepare("INSERT INTO user_activity (user_id, role, username) VALUES (?, ?, ?)");
        $activity_stmt->bind_param("iss", $id, $role, $username);
        $activity_stmt->execute();
        $activity_stmt->close();

        // Return the role and other user details as part of the success response
        echo json_encode(['success' => true, 'message' => 'Login successful', 'role' => $role, 'username' => $username, 'id' => $id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    }

    // Close the statement
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'No data received or missing fields']);
}

// Close the connection
$conn->close();

?>
