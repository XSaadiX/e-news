<?php
session_start();

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'news_db';

$error = '';
$success = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Check user credentials
        $stmt = $pdo->prepare("SELECT user_id, username, email, password, role FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            if ($user['role'] == 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Global News Network</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gradient-primary);
            padding: 2rem 0;
        }
        
        .auth-card {
            background: var(--card-background);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: var(--shadow-hover);
            width: 100%;
            max-width: 450px;
            margin: 0 20px;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .auth-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .auth-logo i {
            font-size: 2rem;
            color: var(--secondary-color);
        }
        
        .auth-title {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .auth-subtitle {
            color: var(--text-light);
            font-size: 1rem;
        }
        
        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .form-group label {
            font-weight: 600;
            color: var(--text-color);
        }
        
        .form-input {
            position: relative;
        }
        
        .form-input i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }
        
        .form-input input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            background: var(--background-color);
            color: var(--text-color);
            font-size: 1rem;
            transition: all 0.3s ease;
            outline: none;
        }
        
        .form-input input:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            padding: 0.25rem;
            transition: color 0.3s ease;
        }
        
        .password-toggle:hover {
            color: var(--secondary-color);
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .remember-me input[type="checkbox"] {
            accent-color: var(--secondary-color);
        }
        
        .forgot-password {
            color: var(--secondary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .forgot-password:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }
        
        .login-btn {
            background: var(--secondary-color);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .login-btn:hover {
            background: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .login-btn:disabled {
            background: var(--text-light);
            cursor: not-allowed;
            transform: none;
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
        }
        
        .auth-footer p {
            color: var(--text-light);
            margin-bottom: 1rem;
        }
        
        .auth-footer a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .auth-footer a:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .alert-error {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.2);
            color: #c0392b;
        }
        
        .alert-success {
            background: rgba(39, 174, 96, 0.1);
            border: 1px solid rgba(39, 174, 96, 0.2);
            color: #27ae60;
        }
        
        .back-home {
            position: absolute;
            top: 2rem;
            left: 2rem;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.1);
            padding: 0.75rem 1rem;
            border-radius: 25px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .back-home:hover {
            background: rgba(255,255,255,0.2);
            transform: translateX(-5px);
        }
        
        @media (max-width: 768px) {
            .auth-card {
                padding: 2rem;
                margin: 0 15px;
            }
            
            .auth-title {
                font-size: 1.5rem;
            }
            
            .back-home {
                position: static;
                margin-bottom: 2rem;
                align-self: flex-start;
            }
        }
        
        /* Loading animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <a href="index.php" class="back-home">
            <i class="fas fa-arrow-left"></i>
            Back to Home
        </a>
        
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-globe"></i>
                    <span>Global News Network</span>
                </div>
                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle">Sign in to your account to continue</p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
            <?php endif; ?>
            
            <form class="auth-form" method="POST" id="loginForm">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="form-input">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" 
                               placeholder="Enter your email address" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="form-input">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" 
                               placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="passwordIcon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" id="remember">
                        Remember me
                    </label>
                    <a href="forgot-password.php" class="forgot-password">Forgot password?</a>
                </div>
                
                <button type="submit" class="login-btn" id="loginBtn">
                    <span id="btnText">Sign In</span>
                    <span id="btnLoading" class="loading" style="display: none;"></span>
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account?</p>
                <a href="register.php">Create new account</a>
            </div>
        </div>
    </div>

    <script>
        // Password toggle functionality
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('passwordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            const btnText = document.getElementById('btnText');
            const btnLoading = document.getElementById('btnLoading');
            
            btn.disabled = true;
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-block';
            
            // Re-enable button after 5 seconds (fallback)
            setTimeout(() => {
                btn.disabled = false;
                btnText.style.display = 'inline-block';
                btnLoading.style.display = 'none';
            }, 5000);
        });

        // Auto-focus email input
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });

        // Theme toggle (inherit from main site)
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark-theme') {
            document.body.classList.add('dark-theme');
        }

        // Enter key handling
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('loginForm').submit();
            }
        });
    </script>
</body>
</html>