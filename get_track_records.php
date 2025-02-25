<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
if ($project_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid project_id"]);
    exit;
}

// Check if the project is assigned
$assignmentQuery = "SELECT COUNT(*) as assigned_count FROM assignments WHERE project_id = ?";
$stmt = $conn->prepare($assignmentQuery);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$assignmentResult = $stmt->get_result();
$assigned = $assignmentResult->fetch_assoc()['assigned_count'] > 0;

// Fetch track record details from project_done
$trackQuery = "
    SELECT challenges, progress_percentage, image, image2, image3, is_project_done, created_at, date_completed,
           summary_work_completed, next_steps, estimated_completion_date
    FROM project_done
    WHERE project_id = ? AND is_project_done IN (0, 1)
    ORDER BY created_at DESC
    LIMIT 1
";
$stmt = $conn->prepare($trackQuery);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$trackResult = $stmt->get_result();

$trackRecords = [];
if ($trackResult->num_rows > 0) {
    $row = $trackResult->fetch_assoc();
    $status = $row['is_project_done'] == 1 ? "Completed" : "In Progress";
    $trackRecords = [
        "challenges" => $row['challenges'] ?: "",
        "progress_percentage" => (int)$row['progress_percentage'] ?: 0,
        "image" => $row['image'] ?: "",
        "image2" => $row['image2'] ?: "",
        "image3" => $row['image3'] ?: "",
        "status" => $status,
        "created_at" => $row['created_at'] ?: "",
        "date_completed" => $row['date_completed'] ?: "",
        "summary_work_completed" => $row['summary_work_completed'] ?: "",
        "next_steps" => $row['next_steps'] ?: "",
        "estimated_completion_date" => $row['estimated_completion_date'] ?: ""
    ];
} else {
    $status = $assigned ? "In Progress" : "Not Assigned Yet";
    $trackRecords = [
        "challenges" => "",
        "progress_percentage" => 0,
        "image" => "",
        "image2" => "",
        "image3" => "",
        "status" => $status,
        "created_at" => "",
        "date_completed" => "",
        "summary_work_completed" => "",
        "next_steps" => "",
        "estimated_completion_date" => ""
    ];
}

echo json_encode([
    "success" => true,
    "track_records" => $trackRecords
]);

$stmt->close();
$conn->close();
?>