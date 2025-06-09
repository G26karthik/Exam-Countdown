<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "exam_tracker";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === FALSE) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbname);

// Create users table if it doesn't exist
$users_table_sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    roll_number VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($users_table_sql) === FALSE) {
    die("Error creating users table: " . $conn->error);
}

// Create exams table if it doesn't exist
$table_sql = "CREATE TABLE IF NOT EXISTS exams (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) UNSIGNED NOT NULL,
    subject VARCHAR(100) NOT NULL,
    exam_datetime DATETIME NOT NULL,
    semester VARCHAR(20) NOT NULL,
    exam_type ENUM('Mid Term', 'Final', 'Quiz', 'Assignment') NOT NULL DEFAULT 'Mid Term',
    priority ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL DEFAULT 'Medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($table_sql) === FALSE) {
    die("Error creating table: " . $conn->error);
}

// Update existing table structure if needed
$conn->query("ALTER TABLE exams MODIFY COLUMN exam_type ENUM('Mid Term', 'Final', 'Quiz', 'Assignment') NOT NULL DEFAULT 'Mid Term'");
$conn->query("ALTER TABLE exams MODIFY COLUMN priority ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL DEFAULT 'Medium'");

// Add priority column if it doesn't exist (for existing databases)
$check_column = "SHOW COLUMNS FROM exams LIKE 'priority'";
$result = $conn->query($check_column);
if ($result->num_rows == 0) {
    $add_priority = "ALTER TABLE exams ADD COLUMN priority ENUM('Low', 'Medium', 'High') NOT NULL DEFAULT 'Medium' AFTER exam_type";
    $conn->query($add_priority);
}

// Function to get time difference in a human-readable format
function getTimeDifference($exam_datetime) {
    $now = new DateTime();
    $exam = new DateTime($exam_datetime);
    $diff = $now->diff($exam);
    
    if ($exam < $now) {
        return "Exam passed";
    }
    
    $days = $diff->days;
    $hours = $diff->h;
    $minutes = $diff->i;
    $seconds = $diff->s;
    
    return [
        'days' => $days,
        'hours' => $hours,
        'minutes' => $minutes,
        'seconds' => $seconds,
        'total_seconds' => ($days * 86400) + ($hours * 3600) + ($minutes * 60) + $seconds
    ];
}

// Function to calculate dynamic priority based on time remaining
function calculateDynamicPriority($exam_datetime) {
    $now = new DateTime();
    $exam = new DateTime($exam_datetime);
    $diff = $now->diff($exam);
    $total_hours = ($diff->days * 24) + $diff->h;
    
    if ($exam < $now) {
        return 'Low'; // Passed exams get low priority
    } elseif ($total_hours <= 24) { // Less than 24 hours
        return 'Critical';
    } elseif ($total_hours <= 72) { // Less than 3 days
        return 'High';
    } elseif ($total_hours <= 168) { // Less than 1 week
        return 'Medium';
    } else {
        return 'Low';
    }
}

// Function to get urgency level based on time remaining
function getUrgencyLevel($exam_datetime) {
    $now = new DateTime();
    $exam = new DateTime($exam_datetime);
    $diff = $now->diff($exam);
    $total_seconds = ($diff->days * 86400) + ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
    
    if ($exam < $now) {
        return 'passed';
    } elseif ($total_seconds <= 600) { // 10 minutes
        return 'flash';
    } elseif ($total_seconds <= 3600) { // 1 hour
        return 'critical';
    } elseif ($total_seconds <= 86400) { // 24 hours
        return 'urgent';
    } elseif ($total_seconds <= 259200) { // 3 days
        return 'moderate';
    } else {
        return 'normal';
    }
}

// Function to get priority icon
function getPriorityIcon($priority) {
    switch ($priority) {
        case 'High':
            return '🔥';
        case 'Medium':
            return '⚠️';
        case 'Low':
            return '📅';
        default:
            return '📅';
    }
}

// Function to get next upcoming exam
function getNextUpcomingExam($user_id, $conn) {
    $now = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("SELECT * FROM exams WHERE user_id = ? AND exam_datetime > ? ORDER BY exam_datetime ASC LIMIT 1");
    $stmt->bind_param("is", $user_id, $now);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// Function to get exam statistics
function getExamStats($user_id, $conn) {
    $now = date('Y-m-d H:i:s');
    
    // Total exams
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM exams WHERE user_id = ? AND exam_datetime > ?");
    $stmt->bind_param("is", $user_id, $now);
    $stmt->execute();
    $total_result = $stmt->get_result();
    $total = $total_result->fetch_assoc()['total'];
    
    // Exams today
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT COUNT(*) as today FROM exams WHERE user_id = ? AND DATE(exam_datetime) = ?");
    $stmt->bind_param("is", $user_id, $today);
    $stmt->execute();
    $today_result = $stmt->get_result();
    $today = $today_result->fetch_assoc()['today'];
    
    // Exams this week
    $week_start = date('Y-m-d', strtotime('monday this week'));
    $week_end = date('Y-m-d', strtotime('sunday this week'));
    $stmt = $conn->prepare("SELECT COUNT(*) as week FROM exams WHERE user_id = ? AND DATE(exam_datetime) BETWEEN ? AND ?");
    $stmt->bind_param("iss", $user_id, $week_start, $week_end);
    $stmt->execute();
    $week_result = $stmt->get_result();
    $week = $week_result->fetch_assoc()['week'];
    
    return [
        'total' => $total,
        'today' => $today,
        'week' => $week
    ];
}
?>
