<?php
require_once 'db.php';
require_once 'auth.php';

// Check authentication
checkAuth();
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    
    // Validate input
    if (empty($id)) {
        header("Location: index.php?error=missing_id");
        exit();
    }
    
    // Verify exam belongs to current user before deleting
    $ownership_check = $conn->prepare("SELECT id FROM exams WHERE id = ? AND user_id = ?");
    $ownership_check->bind_param("ii", $id, $user['id']);
    $ownership_check->execute();
    $ownership_result = $ownership_check->get_result();
    
    if ($ownership_result->num_rows === 0) {
        header("Location: index.php?error=unauthorized");
        exit();
    }
    
    // Prepare and execute the delete statement with user verification
    $stmt = $conn->prepare("DELETE FROM exams WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user['id']);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            header("Location: index.php?success=exam_deleted");
        } else {
            header("Location: index.php?error=exam_not_found");
        }
    } else {
        header("Location: index.php?error=database_error");
    }
    
    $stmt->close();
} else {
    header("Location: index.php");
}

$conn->close();
?>
