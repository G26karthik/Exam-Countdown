<?php
/**
 * MyExamTrack - Database Setup and Verification Script
 * Run this script to set up and verify your database configuration
 */

require_once 'db.php';

// Function to test database connection
function testDatabaseConnection() {
    global $conn;
    
    if ($conn->connect_error) {
        return [
            'success' => false,
            'message' => 'Connection failed: ' . $conn->connect_error
        ];
    }
    
    return [
        'success' => true,
        'message' => 'Database connection successful!'
    ];
}

// Function to verify table creation
function verifyTables() {
    global $conn;
    
    $tables = ['users', 'exams'];
    $results = [];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        $results[$table] = $result->num_rows > 0;
    }
    
    return $results;
}

// Function to get table counts
function getTableCounts() {
    global $conn;
    
    $counts = [];
    
    // Count users
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $counts['users'] = $result->fetch_assoc()['count'];
    
    // Count exams
    $result = $conn->query("SELECT COUNT(*) as count FROM exams");
    $counts['exams'] = $result->fetch_assoc()['count'];
    
    return $counts;
}

// Function to create sample data (for testing)
function createSampleData() {
    global $conn;
    
    // Create a test user if none exists
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $userCount = $result->fetch_assoc()['count'];
    
    if ($userCount == 0) {
        $stmt = $conn->prepare("INSERT INTO users (roll_number, name, password) VALUES (?, ?, ?)");
        $roll = "TEST001";
        $name = "Test Student";
        $password = "TEST001";
        $stmt->bind_param("sss", $roll, $name, $password);
        $stmt->execute();
        
        return "Sample user created: Roll Number: TEST001, Password: TEST001";
    }
    
    return "Users already exist in database";
}

// Run setup verification
$dbTest = testDatabaseConnection();
$tableStatus = verifyTables();
$counts = getTableCounts();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyExamTrack - Setup Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .setup-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
            padding: 2rem;
        }
        .status-card {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .success { border-left: 5px solid #28a745; }
        .error { border-left: 5px solid #dc3545; }
        .warning { border-left: 5px solid #ffc107; }
        .info { border-left: 5px solid #17a2b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="setup-container">
                    <div class="text-center mb-4">
                        <h1 class="display-5">
                            <i class="fas fa-graduation-cap text-primary me-3"></i>
                            MyExamTrack Setup
                        </h1>
                        <p class="lead text-muted">Database Configuration & Verification</p>
                    </div>

                    <!-- Database Connection Status -->
                    <div class="status-card <?php echo $dbTest['success'] ? 'success' : 'error'; ?>">
                        <h5>
                            <i class="fas fa-<?php echo $dbTest['success'] ? 'check-circle text-success' : 'times-circle text-danger'; ?> me-2"></i>
                            Database Connection
                        </h5>
                        <p class="mb-0"><?php echo $dbTest['message']; ?></p>
                    </div>

                    <!-- Table Status -->
                    <div class="status-card info">
                        <h5>
                            <i class="fas fa-table text-info me-2"></i>
                            Database Tables
                        </h5>
                        <div class="row">
                            <?php foreach ($tableStatus as $table => $exists): ?>
                            <div class="col-md-6">
                                <span class="badge bg-<?php echo $exists ? 'success' : 'danger'; ?> me-2">
                                    <i class="fas fa-<?php echo $exists ? 'check' : 'times'; ?>"></i>
                                </span>
                                <?php echo ucfirst($table); ?> Table
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Data Counts -->
                    <div class="status-card success">
                        <h5>
                            <i class="fas fa-chart-bar text-success me-2"></i>
                            Database Statistics
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Users:</strong> <?php echo $counts['users']; ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Exams:</strong> <?php echo $counts['exams']; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Sample Data Creation -->
                    <?php if (isset($_POST['create_sample'])): ?>
                    <div class="status-card warning">
                        <h5>
                            <i class="fas fa-user-plus text-warning me-2"></i>
                            Sample Data
                        </h5>
                        <p class="mb-0"><?php echo createSampleData(); ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Actions -->
                    <div class="text-center">
                        <?php if ($dbTest['success'] && $tableStatus['users'] && $tableStatus['exams']): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Setup Complete!</strong> Your MyExamTrack application is ready to use.
                        </div>
                        <a href="index.php" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                        </a>
                        <?php endif; ?>
                        
                        <a href="login.php" class="btn btn-outline-primary btn-lg me-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Login / Signup
                        </a>
                        
                        <?php if ($counts['users'] == 0): ?>
                        <form method="POST" class="d-inline">
                            <button type="submit" name="create_sample" class="btn btn-warning btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Create Test User
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Start Guide -->
                    <div class="mt-5">
                        <h5><i class="fas fa-rocket text-primary me-2"></i>Quick Start Guide</h5>
                        <ol class="list-group list-group-flush">
                            <li class="list-group-item d-flex align-items-center">
                                <span class="badge bg-primary rounded-pill me-3">1</span>
                                Create your account or login with existing credentials
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <span class="badge bg-primary rounded-pill me-3">2</span>
                                Add your first exam using the floating + button
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <span class="badge bg-primary rounded-pill me-3">3</span>
                                Watch the real-time countdown and stay organized!
                            </li>
                        </ol>
                    </div>

                    <!-- System Information -->
                    <div class="mt-4">
                        <small class="text-muted">
                            <strong>System Info:</strong> 
                            PHP <?php echo phpversion(); ?> | 
                            MySQL <?php echo $conn->server_info; ?> | 
                            Server: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
