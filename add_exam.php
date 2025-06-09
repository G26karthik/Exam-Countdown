<?php
require_once 'db.php';
require_once 'auth.php';

// Check authentication
checkAuth();
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {    $subject = trim($_POST['subject']);
    $exam_datetime = $_POST['exam_datetime'];
    $semester = trim($_POST['semester']);
    $exam_type = trim($_POST['exam_type']);
    $priority = trim($_POST['priority']);
      // Validate inputs
    if (empty($subject) || empty($exam_datetime) || empty($semester) || empty($exam_type) || empty($priority)) {
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
    
    // Check for duplicate exam (same subject, same datetime, same user)
    $duplicate_check = $conn->prepare("SELECT id FROM exams WHERE user_id = ? AND subject = ? AND exam_datetime = ?");
    $duplicate_check->bind_param("iss", $user['id'], $subject, $exam_datetime);
    $duplicate_check->execute();
    $duplicate_result = $duplicate_check->get_result();
      if ($duplicate_result->num_rows > 0) {
        header("Location: index.php?error=duplicate_exam");
        exit();
    }
      // Prepare and execute the insert statement with user_id, semester, exam_type, and priority
    $stmt = $conn->prepare("INSERT INTO exams (user_id, subject, exam_datetime, semester, exam_type, priority) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user['id'], $subject, $exam_datetime, $semester, $exam_type, $priority);
    
    if ($stmt->execute()) {
        header("Location: index.php?success=exam_added");
    } else {
        header("Location: index.php?error=database_error");
    }
    
    $stmt->close();
} else {
    header("Location: index.php");
}

$conn->close();
?>
