<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

date_default_timezone_set('Asia/Kolkata');

$conn = new mysqli("localhost", "root", "", "user_db");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Get the latest logged-in user ID
$query = "SELECT user_id FROM user_activity ORDER BY created_at DESC LIMIT 1";
$result = mysqli_query($conn, $query);
if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode(["success" => false, "message" => "Failed to retrieve logged-in user."]);
    exit;
}
$row = mysqli_fetch_assoc($result);
$user_id = $row['user_id'];

// Handle form data
$project_id = $_POST['project_id'] ?? '';
$challenges = $_POST['challenges'] ?? '';
$progress_percentage = $_POST['progress_percentage'] ?? '';
$summary_work_completed = $_POST['summary_work_completed'] ?? '';
$next_steps = $_POST['next_steps'] ?? '';
$estimated_completion_date = $_POST['estimated_completion_date'] ?? '';

if (empty($project_id) || empty($progress_percentage)) {
    echo json_encode(["success" => false, "message" => "Missing required fields: project_id or progress_percentage."]);
    exit;
}

// Check if the user is a leader
$leaderQuery = "SELECT * FROM assignments WHERE employee_id = ? AND project_id = ? AND is_leader = 1";
$stmt = $conn->prepare($leaderQuery);
$stmt->bind_param("ii", $user_id, $project_id);
$stmt->execute();
$leaderResult = $stmt->get_result();
if ($leaderResult->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Only the project leader can submit a report."]);
    exit;
}

// Handle multiple image uploads
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$imageFields = ['image', 'image2', 'image3'];
$images = [];
foreach ($imageFields as $field) {
    $image = '';
    if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
        $imageName = uniqid() . '-' . basename($_FILES[$field]['name']);
        $imagePath = $uploadDir . $imageName;
        if (move_uploaded_file($_FILES[$field]['tmp_name'], $imagePath)) {
            $image = $imagePath;
        } else {
            echo json_encode(["success" => false, "message" => "Failed to upload $field."]);
            exit;
        }
    }
    $images[$field] = $image;
}

// Check if a report exists for this project
$checkQuery = "SELECT id, image, image2, image3 FROM project_done WHERE project_id = ? AND is_project_done = 0 ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$checkResult = $stmt->get_result();

if ($checkResult->num_rows > 0) {
    // Update existing report
    $row = $checkResult->fetch_assoc();
    $existingId = $row['id'];
    $oldImage = $row['image'];
    $oldImage2 = $row['image2'];
    $oldImage3 = $row['image3'];

    $newImage = $images['image'] ?: $oldImage;
    $newImage2 = $images['image2'] ?: $oldImage2;
    $newImage3 = $images['image3'] ?: $oldImage3;

    $updateQuery = "
        UPDATE project_done 
        SET challenges = ?, progress_percentage = ?, image = ?, image2 = ?, image3 = ?, 
            summary_work_completed = ?, next_steps = ?, estimated_completion_date = ?, created_at = NOW()
        WHERE id = ?
    ";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param(
        "sissssssi",
        $challenges,
        $progress_percentage,
        $newImage,
        $newImage2,
        $newImage3,
        $summary_work_completed,
        $next_steps,
        $estimated_completion_date,
        $existingId
    );

    if ($stmt->execute()) {
        // Delete old images if replaced
        if ($images['image'] && $oldImage && file_exists($oldImage) && $oldImage !== $newImage) unlink($oldImage);
        if ($images['image2'] && $oldImage2 && file_exists($oldImage2) && $oldImage2 !== $newImage2) unlink($oldImage2);
        if ($images['image3'] && $oldImage3 && file_exists($oldImage3) && $oldImage3 !== $newImage3) unlink($oldImage3);
        echo json_encode(["success" => true, "message" => "Report updated successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update report: " . $stmt->error]);
    }
} else {
    // Insert new report
    $insertQuery = "
        INSERT INTO project_done (project_id, leader_id, challenges, progress_percentage, image, image2, image3, 
            summary_work_completed, next_steps, estimated_completion_date, is_project_done, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())
    ";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param(
        "iisissssss",
        $project_id,
        $user_id,
        $challenges,
        $progress_percentage,
        $images['image'],
        $images['image2'],
        $images['image3'],
        $summary_work_completed,
        $next_steps,
        $estimated_completion_date
    );

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Report created successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to create report: " . $stmt->error]);
    }
}

$stmt->close();
$conn->close();
?>