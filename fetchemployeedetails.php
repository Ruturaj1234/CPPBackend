<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST'); // Allow both GET and POST methods
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$database = "user_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if it's a POST request to fetch employee details
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"));
        if (isset($data->id)) {
            $employee_id = (int)$data->id;  // Ensure the ID is a valid integer

            $query = "
                SELECT id, name, age, address, account_number, ifsc_code,
                       salary_basic, salary_da, salary_hra, salary_maintenance
                FROM employee_details2
                WHERE id = :id
            ";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $employee_id, PDO::PARAM_INT);
            $stmt->execute();

            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($employee) {
                echo json_encode([$employee]); // Wrap the employee data in an array
            } else {
                echo json_encode(['error' => 'Employee not found']);
            }
        } else {
            echo json_encode(['error' => 'Invalid employee ID']);
        }
    } else {
        // Default behavior: Fetch all employees via GET
        $query = "
            SELECT id, name, age, address, account_number, ifsc_code,
                   salary_basic, salary_da, salary_hra, salary_maintenance
            FROM employee_details2
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($employees);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
