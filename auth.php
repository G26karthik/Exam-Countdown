<?php
session_start();

// Check if user is logged in
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Login user
function loginUser($roll_number, $password, $conn) {
    $stmt = $conn->prepare("SELECT id, roll_number, name FROM users WHERE roll_number = ? AND password = ?");
    $stmt->bind_param("ss", $roll_number, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['roll_number'] = $user['roll_number'];
        $_SESSION['name'] = $user['name'];
        return true;
    }
    return false;
}

// Register user
function registerUser($roll_number, $name, $password, $conn) {
    // Check if roll number already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE roll_number = ?");
    $stmt->bind_param("s", $roll_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return false; // User already exists
    }
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (roll_number, name, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $roll_number, $name, $password);
    
    if ($stmt->execute()) {
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['roll_number'] = $roll_number;
        $_SESSION['name'] = $name;
        return true;
    }
    return false;
}

// Logout user
function logoutUser() {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Get current user info
function getCurrentUser() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'roll_number' => $_SESSION['roll_number'] ?? null,
        'name' => $_SESSION['name'] ?? null
    ];
}
?>
