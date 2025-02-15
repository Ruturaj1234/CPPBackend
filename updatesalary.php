<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]));
}

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (
    isset($data['employee_id']) && isset($data['salary_basic']) &&
    isset($data['salary_da']) && isset($data['salary_hra']) &&
    isset($data['salary_maintenance'])
) {
    $employee_id = $data['employee_id']; // ID from frontend
    $salary_basic = $data['salary_basic'];
    $salary_da = $data['salary_da'];
    $salary_hra = $data['salary_hra'];
    $salary_maintenance = $data['salary_maintenance'];

    // First, find the corresponding `eid` using `employee_id`
    $eidQuery = "SELECT eid FROM employee_details2 WHERE id = ?";
    $stmtEid = $conn->prepare($eidQuery);
    $stmtEid->bind_param("i", $employee_id);
    $stmtEid->execute();
    $result = $stmtEid->get_result();
    $stmtEid->close();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $eid = $row['eid']; // Correct employee ID

        // Update salary in employee_details2 table using `eid`
        $updateSql = "UPDATE employee_details2 
                      SET salary_basic = ?, salary_da = ?, salary_hra = ?, salary_maintenance = ? 
                      WHERE eid = ?";

        $stmt = $conn->prepare($updateSql);
        if (!$stmt) {
            die(json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]));
        }
        $stmt->bind_param("ddddi", $salary_basic, $salary_da, $salary_hra, $salary_maintenance, $eid);

        if ($stmt->execute()) {
            // Calculate total salary
            $total_salary = $salary_basic + $salary_da + $salary_hra + $salary_maintenance;

            // Insert salary details into salaries table
            $sql = "INSERT INTO salaries (employee_id, salary_basic, salary_da, salary_hra, salary_maintenance, total_salary) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmtInsert = $conn->prepare($sql);
            if (!$stmtInsert) {
                die(json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]));
            }
            $stmtInsert->bind_param("iddddd", $eid, $salary_basic, $salary_da, $salary_hra, $salary_maintenance, $total_salary);

            if ($stmtInsert->execute()) {
                echo json_encode(["success" => true, "message" => "Salary updated successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to insert salary record"]);
            }

            $stmtInsert->close();
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update salary"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Invalid employee ID"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid input data"]);
}

$conn->close();
?>
