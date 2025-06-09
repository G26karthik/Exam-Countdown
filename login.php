<?php
require_once 'db.php';
require_once 'auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Login logic
        $roll_number = strtoupper(trim($_POST['roll_number']));
        $password = strtoupper(trim($_POST['password']));
        
        if (empty($roll_number) || empty($password)) {
            $error = 'Please fill in all fields';
        } else {
            if (loginUser($roll_number, $password, $conn)) {
                header("Location: index.php");
                exit();
            } else {
                $error = 'Invalid roll number or password';
            }
        }
    }
    
    if (isset($_POST['signup'])) {
        // Signup logic
        $roll_number = strtoupper(trim($_POST['roll_number']));
        $name = trim($_POST['name']);
        $password = strtoupper(trim($_POST['password']));
        
        if (empty($roll_number) || empty($name) || empty($password)) {
            $error = 'Please fill in all fields';
        } else {
            if (registerUser($roll_number, $name, $password, $conn)) {
                header("Location: index.php");
                exit();
            } else {
                $error = 'Roll number already exists';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyExamTrack - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        /* Login-specific styles */
        .login-body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 2rem;
        }
        
        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-subtle);
            border-radius: 24px;
            padding: 2.5rem;
            max-width: 450px;
            width: 100%;
            box-shadow: var(--shadow-strong);
            position: relative;
            overflow: hidden;
        }
        
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--brand-primary), var(--brand-secondary));
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .brand-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--text-primary);
            margin: 0 auto 1.5rem;
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
            animation: brandFloat 3s ease-in-out infinite;
        }
        
        @keyframes brandFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
        }
        
        .login-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--text-primary), var(--text-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .login-header p {
            color: var(--text-tertiary);
            font-size: 0.95rem;
            margin: 0;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .form-control {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            padding: 0.875rem 1rem;
            color: var(--text-primary);
            font-size: 0.9rem;
            transition: all var(--transition-fast);
        }
        
        .form-control:focus {
            background: var(--bg-elevated);
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            color: var(--text-primary);
        }
        
        .form-control::placeholder {
            color: var(--text-muted);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--brand-primary), #4f46e5);
            border: none;
            border-radius: 12px;
            padding: 0.875rem 1.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-primary);
            transition: all var(--transition-fast);
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left var(--transition-normal);
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
        }
        
        .btn-outline-primary {
            background: transparent;
            border: 1px solid var(--border-medium);
            color: var(--text-secondary);
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all var(--transition-fast);
        }
        
        .btn-outline-primary:hover {
            background: var(--bg-tertiary);
            border-color: var(--brand-primary);
            color: var(--brand-primary);
            transform: translateY(-1px);
        }
        
        .text-muted {
            color: var(--text-muted) !important;
            font-size: 0.85rem;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--brand-danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: var(--brand-success);
            border: 1px solid rgba(34, 197, 94, 0.2);
        }
        
        .toggle-form {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-subtle);
        }
        
        @media (max-width: 576px) {
            .login-card {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }
            
            .login-header h2 {
                font-size: 1.75rem;
            }
            
            .brand-icon {
                width: 64px;
                height: 64px;
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body class="login-body">
    <div class="login-card">
        <div class="login-header">
            <div class="brand-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h2>MyExamTrack</h2>
            <p>Your Personal Exam Dashboard</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <div id="loginForm">
            <form method="POST">
                <div class="form-group">
                    <label for="login_roll_number" class="form-label">
                        <i class="fas fa-id-card me-2"></i>Roll Number
                    </label>
                    <input type="text" class="form-control" id="login_roll_number" name="roll_number" 
                           placeholder="Enter your roll number" required style="text-transform: uppercase;">
                </div>
                <div class="form-group">
                    <label for="login_password" class="form-label">
                        <i class="fas fa-lock me-2"></i>Password
                    </label>
                    <input type="password" class="form-control" id="login_password" name="password" 
                           placeholder="Enter your password" required style="text-transform: uppercase;">
                    <small class="text-muted mt-1 d-block">
                        <i class="fas fa-lightbulb me-1"></i>Password is same as your roll number
                    </small>
                </div>
                <button type="submit" name="login" class="btn btn-primary w-100 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i>Login to Dashboard
                </button>
            </form>
            
            <div class="toggle-form">
                <p class="mb-2 text-muted">Don't have an account?</p>
                <button class="btn btn-outline-primary w-100" onclick="toggleForms()">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
            </div>
        </div>

        <!-- Signup Form -->
        <div id="signupForm" style="display: none;">
            <form method="POST">
                <div class="form-group">
                    <label for="signup_name" class="form-label">
                        <i class="fas fa-user me-2"></i>Full Name
                    </label>
                    <input type="text" class="form-control" id="signup_name" name="name" 
                           placeholder="Enter your full name" required>
                </div>
                <div class="form-group">
                    <label for="signup_roll_number" class="form-label">
                        <i class="fas fa-id-card me-2"></i>Roll Number
                    </label>
                    <input type="text" class="form-control" id="signup_roll_number" name="roll_number" 
                           placeholder="Enter your roll number" required style="text-transform: uppercase;">
                </div>
                <div class="form-group">
                    <label for="signup_password" class="form-label">
                        <i class="fas fa-lock me-2"></i>Password
                    </label>
                    <input type="password" class="form-control" id="signup_password" name="password" 
                           placeholder="Enter your password" required style="text-transform: uppercase;">
                    <small class="text-muted mt-1 d-block">
                        <i class="fas fa-lightbulb me-1"></i>Use your roll number as password (recommended)
                    </small>
                </div>
                <button type="submit" name="signup" class="btn btn-primary w-100 mb-3">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
            </form>
            
            <div class="toggle-form">
                <p class="mb-2 text-muted">Already have an account?</p>
                <button class="btn btn-outline-primary w-100" onclick="toggleForms()">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </div>
        </div>    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleForms() {
            const loginForm = document.getElementById('loginForm');
            const signupForm = document.getElementById('signupForm');
            
            if (loginForm.style.display === 'none') {
                loginForm.style.display = 'block';
                signupForm.style.display = 'none';
            } else {
                loginForm.style.display = 'none';
                signupForm.style.display = 'block';
            }
        }

        // Auto-fill password with roll number for convenience
        document.getElementById('signup_roll_number').addEventListener('input', function() {
            document.getElementById('signup_password').value = this.value.toUpperCase();
        });

        document.getElementById('login_roll_number').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });

        document.getElementById('signup_roll_number').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    </script>
</body>
</html>