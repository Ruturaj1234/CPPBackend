<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

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

// Fetch the latest user_id from the user_activity table
$activity_query = "SELECT user_id FROM user_activity ORDER BY created_at DESC LIMIT 1";
$activity_result = $conn->query($activity_query);

// Check if there is a record in the user_activity table
if ($activity_result && $activity_result->num_rows > 0) {
    $activity_row = $activity_result->fetch_assoc();
    $user_id = $activity_row['user_id'];

    // Fetch the personal details of the employee
    $employee_query = "SELECT id, name, age, address, account_number, ifsc_code, email FROM employee_details2 WHERE eid = ?";
    $stmt = $conn->prepare($employee_query);

    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $employee_result = $stmt->get_result();

        if ($employee_result->num_rows > 0) {
            $employee_row = $employee_result->fetch_assoc();

            // Return the employee details as a JSON response
            echo json_encode([
                'success' => true,
                'employee' => [
                    'name' => $employee_row['name'],
                    'age' => $employee_row['age'],
                    'address' => $employee_row['address'],
                    'account_number' => $employee_row['account_number'],
                    'ifsc_code' => $employee_row['ifsc_code'],
                    'email' => $employee_row['email'],
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Employee details not found']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No activity record found']);
}

// Close the database connection
$conn->close();
?>
