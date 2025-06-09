<?php
/**
 * MyExamTrack - Final Verification and Health Check Script
 * Run this after installation to ensure everything is working correctly
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyExamTrack - System Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .verify-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
            padding: 2rem;
        }
        .check-item {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .check-success { border-left: 5px solid #28a745; }
        .check-error { border-left: 5px solid #dc3545; }
        .check-warning { border-left: 5px solid #ffc107; }
        .check-info { border-left: 5px solid #17a2b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="verify-container">
                    <div class="text-center mb-4">
                        <h1 class="display-5">
                            <i class="fas fa-graduation-cap text-primary me-3"></i>
                            MyExamTrack System Verification
                        </h1>
                        <p class="lead text-muted">Complete System Health Check</p>
                    </div>

                    <?php
                    // 1. Check PHP Version
                    $phpVersion = phpversion();
                    $phpOk = version_compare($phpVersion, '7.4.0', '>=');
                    ?>
                    <div class="check-item <?php echo $phpOk ? 'check-success' : 'check-error'; ?>">
                        <h5>
                            <i class="fas fa-<?php echo $phpOk ? 'check-circle text-success' : 'times-circle text-danger'; ?> me-2"></i>
                            PHP Version Check
                        </h5>
                        <p class="mb-0">
                            <strong>Current Version:</strong> <?php echo $phpVersion; ?>
                            <?php echo $phpOk ? ' ✅ Compatible' : ' ❌ Requires PHP 7.4+'; ?>
                        </p>
                    </div>

                    <?php
                    // 2. Check Required PHP Extensions
                    $requiredExtensions = ['mysqli', 'session', 'date', 'json'];
                    $missingExtensions = [];
                    foreach ($requiredExtensions as $ext) {
                        if (!extension_loaded($ext)) {
                            $missingExtensions[] = $ext;
                        }
                    }
                    ?>
                    <div class="check-item <?php echo empty($missingExtensions) ? 'check-success' : 'check-error'; ?>">
                        <h5>
                            <i class="fas fa-<?php echo empty($missingExtensions) ? 'check-circle text-success' : 'times-circle text-danger'; ?> me-2"></i>
                            Required PHP Extensions
                        </h5>
                        <?php if (empty($missingExtensions)): ?>
                            <p class="mb-0">✅ All required extensions are loaded</p>
                        <?php else: ?>
                            <p class="mb-0">❌ Missing extensions: <?php echo implode(', ', $missingExtensions); ?></p>
                        <?php endif; ?>
                    </div>

                    <?php
                    // 3. Check File Permissions
                    $files = ['db.php', 'auth.php', 'index.php', 'login.php', 'add_exam.php', 'edit_exam.php', 'delete_exam.php'];
                    $missingFiles = [];
                    foreach ($files as $file) {
                        if (!file_exists($file)) {
                            $missingFiles[] = $file;
                        }
                    }
                    ?>
                    <div class="check-item <?php echo empty($missingFiles) ? 'check-success' : 'check-error'; ?>">
                        <h5>
                            <i class="fas fa-<?php echo empty($missingFiles) ? 'check-circle text-success' : 'times-circle text-danger'; ?> me-2"></i>
                            Application Files
                        </h5>
                        <?php if (empty($missingFiles)): ?>
                            <p class="mb-0">✅ All required files are present</p>
                        <?php else: ?>
                            <p class="mb-0">❌ Missing files: <?php echo implode(', ', $missingFiles); ?></p>
                        <?php endif; ?>
                    </div>

                    <?php
                    // 4. Test Database Connection
                    $dbTest = ['success' => false, 'message' => 'Not tested'];
                    try {
                        require_once 'db.php';
                        $dbTest = ['success' => true, 'message' => 'Database connection successful!'];
                    } catch (Exception $e) {
                        $dbTest = ['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()];
                    }
                    ?>
                    <div class="check-item <?php echo $dbTest['success'] ? 'check-success' : 'check-error'; ?>">
                        <h5>
                            <i class="fas fa-<?php echo $dbTest['success'] ? 'check-circle text-success' : 'times-circle text-danger'; ?> me-2"></i>
                            Database Connection
                        </h5>
                        <p class="mb-0"><?php echo $dbTest['message']; ?></p>
                    </div>

                    <?php
                    // 5. Check Table Structure
                    $tablesOk = false;
                    $tableMessage = 'Could not verify tables';
                    if ($dbTest['success']) {
                        try {
                            $tables = ['users', 'exams'];
                            $existingTables = [];
                            foreach ($tables as $table) {
                                $result = $conn->query("SHOW TABLES LIKE '$table'");
                                if ($result->num_rows > 0) {
                                    $existingTables[] = $table;
                                }
                            }
                            if (count($existingTables) === count($tables)) {
                                $tablesOk = true;
                                $tableMessage = 'All required tables exist: ' . implode(', ', $existingTables);
                            } else {
                                $tableMessage = 'Missing tables: ' . implode(', ', array_diff($tables, $existingTables));
                            }
                        } catch (Exception $e) {
                            $tableMessage = 'Error checking tables: ' . $e->getMessage();
                        }
                    }
                    ?>
                    <div class="check-item <?php echo $tablesOk ? 'check-success' : 'check-warning'; ?>">
                        <h5>
                            <i class="fas fa-<?php echo $tablesOk ? 'check-circle text-success' : 'exclamation-triangle text-warning'; ?> me-2"></i>
                            Database Tables
                        </h5>
                        <p class="mb-0"><?php echo $tableMessage; ?></p>
                    </div>

                    <?php
                    // 6. Check write permissions
                    $sessionPath = session_save_path();
                    if (empty($sessionPath)) {
                        $sessionPath = sys_get_temp_dir();
                    }
                    $sessionWritable = is_writable($sessionPath);
                    ?>
                    <div class="check-item <?php echo $sessionWritable ? 'check-success' : 'check-warning'; ?>">
                        <h5>
                            <i class="fas fa-<?php echo $sessionWritable ? 'check-circle text-success' : 'exclamation-triangle text-warning'; ?> me-2"></i>
                            Session Support
                        </h5>
                        <p class="mb-0">
                            Session path: <?php echo $sessionPath; ?><br>
                            <?php echo $sessionWritable ? '✅ Sessions can be written' : '⚠️ Session directory may not be writable'; ?>
                        </p>
                    </div>

                    <!-- Overall Status -->
                    <?php
                    $allChecksPass = $phpOk && empty($missingExtensions) && empty($missingFiles) && $dbTest['success'] && $tablesOk;
                    ?>
                    <div class="check-item <?php echo $allChecksPass ? 'check-success' : 'check-warning'; ?>">
                        <h5>
                            <i class="fas fa-<?php echo $allChecksPass ? 'rocket text-success' : 'tools text-warning'; ?> me-2"></i>
                            Overall System Status
                        </h5>
                        <?php if ($allChecksPass): ?>
                            <p class="mb-3">🎉 <strong>Excellent!</strong> Your MyExamTrack application is ready to use.</p>
                            <div class="text-center">
                                <a href="index.php" class="btn btn-success btn-lg me-3">
                                    <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                                </a>
                                <a href="login.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login / Sign Up
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="mb-3">⚠️ Some issues need attention. Please review the checks above.</p>
                            <div class="text-center">
                                <a href="setup.php" class="btn btn-warning btn-lg me-3">
                                    <i class="fas fa-wrench me-2"></i>Run Setup
                                </a>
                                <button onclick="location.reload()" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-redo me-2"></i>Re-check
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- System Information -->
                    <div class="check-item check-info">
                        <h5>
                            <i class="fas fa-info-circle text-info me-2"></i>
                            System Information
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>PHP Version:</strong> <?php echo phpversion(); ?><br>
                                <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?><br>
                                <strong>Operating System:</strong> <?php echo PHP_OS; ?>
                            </div>
                            <div class="col-md-6">
                                <strong>MySQL Version:</strong> <?php echo isset($conn) ? $conn->server_info : 'Not connected'; ?><br>
                                <strong>Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?><br>
                                <strong>Timezone:</strong> <?php echo date_default_timezone_get(); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Start Guide -->
                    <div class="check-item check-info">
                        <h5>
                            <i class="fas fa-rocket text-info me-2"></i>
                            Quick Start Guide
                        </h5>
                        <ol>
                            <li><strong>Create Account:</strong> Click "Login / Sign Up" and create your account</li>
                            <li><strong>Add First Exam:</strong> Use the floating "+" button to add your first exam</li>
                            <li><strong>Watch Countdown:</strong> See real-time countdown timers for all your exams</li>
                            <li><strong>Stay Organized:</strong> Use filters and priority system to stay on top of your studies</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
