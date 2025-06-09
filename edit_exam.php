<?php
require_once 'db.php';
require_once 'auth.php';

// Check authentication
checkAuth();
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);    $subject = trim($_POST['subject']);
    $exam_datetime = $_POST['exam_datetime'];
    $semester = trim($_POST['semester']);
    $exam_type = trim($_POST['exam_type']);
    $priority = trim($_POST['priority']);
      // Validate inputs
    if (empty($id) || empty($subject) || empty($exam_datetime) || empty($semester) || empty($exam_type) || empty($priority)) {
        header("Location: index.php?error=missing_fields");
        exit();
    }
      // Sanitize inputs
    $subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
    $semester = htmlspecialchars($semester, ENT_QUOTES, 'UTF-8');
    $exam_type = htmlspecialchars($exam_type, ENT_QUOTES, 'UTF-8');
    $priority = htmlspecialchars($priority, ENT_QUOTES, 'UTF-8');
    
    // Validate that exam datetime is in the future
    $exam_date = new DateTime($exam_datetime);
    $now = new DateTime();
    
    if ($exam_date <= $now) {
        header("Location: index.php?error=past_date");
        exit();
    }
    
    // Verify exam belongs to current user
    $ownership_check = $conn->prepare("SELECT id FROM exams WHERE id = ? AND user_id = ?");
    $ownership_check->bind_param("ii", $id, $user['id']);
    $ownership_check->execute();
    $ownership_result = $ownership_check->get_result();
    
    if ($ownership_result->num_rows === 0) {
        header("Location: index.php?error=unauthorized");
        exit();
    }
    
    // Check for duplicate exam (same subject, same datetime, same user, different id)
    $duplicate_check = $conn->prepare("SELECT id FROM exams WHERE user_id = ? AND subject = ? AND exam_datetime = ? AND id != ?");
    $duplicate_check->bind_param("issi", $user['id'], $subject, $exam_datetime, $id);
    $duplicate_check->execute();
    $duplicate_result = $duplicate_check->get_result();
    
    if ($duplicate_result->num_rows > 0) {
        header("Location: index.php?error=duplicate_exam");
        exit();
    }    // Prepare and execute the update statement
    $stmt = $conn->prepare("UPDATE exams SET subject = ?, exam_datetime = ?, semester = ?, exam_type = ?, priority = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sssssii", $subject, $exam_datetime, $semester, $exam_type, $priority, $id, $user['id']);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            header("Location: index.php?success=exam_updated");
        } else {
            header("Location: index.php?error=exam_not_found");
        }
    } else {
        header("Location: index.php?error=database_error");
    }
    
    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    // Get exam data for editing - ensure it belongs to current user
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM exams WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $exam = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($exam);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Exam not found']);
    }
    
    $stmt->close();
} else {
    header("Location: index.php");
}

$conn->close();
?>
