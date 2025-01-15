<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$database = "user_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['id'])) {
        echo json_encode(['error' => 'Employee ID is required']);
        exit;
    }

    $query = "
        INSERT INTO employee_details (id, name, age, address, account_number, ifsc_code, salary_basic, salary_da, salary_hra, salary_maintenance)
        VALUES (:id, :name, :age, :address, :account_number, :ifsc_code, :salary_basic, :salary_da, :salary_hra, :salary_maintenance)
        ON DUPLICATE KEY UPDATE
        name = :name, age = :age, address = :address, account_number = :account_number, ifsc_code = :ifsc_code, 
        salary_basic = :salary_basic, salary_da = :salary_da, salary_hra = :salary_hra, salary_maintenance = :salary_maintenance
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute($input);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>

