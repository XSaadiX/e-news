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

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters long.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = :username OR email = :email");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = 'Username or email already exists.';
        } else {
            // Create new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, 'user')");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            
            if ($stmt->execute()) {
                $success = 'Account created successfully! You can now log in.';
                // Clear form data
                $_POST = array();
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Global News Network</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Inherit auth styles from login.php */
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
        
        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.8rem;
        }
        
        .strength-meter {
            height: 4px;
            background: var(--border-color);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 0.25rem;
        }
        
        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }
        
        .strength-weak { background: #e74c3c; width: 25%; }
        .strength-fair { background: #f39c12; width: 50%; }
        .strength-good { background: #f1c40f; width: 75%; }
        .strength-strong { background: #27ae60; width: 100%; }
        
        .register-btn {
            background: var(--secondary-color);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .register-btn:hover {
            background: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }
        
        .register-btn:disabled {
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
        
        .terms-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.9rem;
            color: var(--text-light);
        }
        
        .terms-checkbox input[type="checkbox"] {
            accent-color: var(--secondary-color);
            margin-top: 0.25rem;
        }
        
        .terms-checkbox a {
            color: var(--secondary-color);
            text-decoration: none;
        }
        
        .terms-checkbox a:hover {
            text-decoration: underline;
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
                <h1 class="auth-title">Create Account</h1>
                <p class="auth-subtitle">Join our community to get personalized news</p>
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
                <a href="login.php" style="margin-left: auto; color: #27ae60; font-weight: bold;">Login Now</a>
            </div>
            <?php endif; ?>
            
            <form class="auth-form" method="POST" id="registerForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="form-input">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" 
                               placeholder="Choose a username" 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                               required minlength="3">
                    </div>
                </div>
                
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
                               placeholder="Create a strong password" 
                               required minlength="6">
                        <button type="button" class="password-toggle" onclick="togglePassword('password', 'passwordIcon')">
                            <i class="fas fa-eye" id="passwordIcon"></i>
                        </button>
                    </div>
                    <div class="password-strength" id="passwordStrength">
                        <div class="strength-meter">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <span id="strengthText">Enter a password</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="form-input">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               placeholder="Confirm your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', 'confirmIcon')">
                            <i class="fas fa-eye" id="confirmIcon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="terms-checkbox">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">
                        I agree to the <a href="terms.php" target="_blank">Terms of Service</a> 
                        and <a href="privacy.php" target="_blank">Privacy Policy</a>
                    </label>
                </div>
                
                <button type="submit" class="register-btn" id="registerBtn">
                    Create Account
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account?</p>
                <a href="login.php">Sign in here</a>
            </div>
        </div>
    </div>

    <script>
        // Password toggle functionality
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const passwordIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Password strength checker
        function checkPasswordStrength(password) {
            let strength = 0;
            const checks = {
                length: password.length >= 8,
                lowercase: /[a-z]/.test(password),
                uppercase: /[A-Z]/.test(password),
                numbers: /\d/.test(password),
                special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
            };
            
            strength = Object.values(checks).filter(Boolean).length;
            
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            
            strengthFill.className = 'strength-fill';
            
            if (password.length === 0) {
                strengthText.textContent = 'Enter a password';
                return;
            }
            
            switch(strength) {
                case 0:
                case 1:
                    strengthFill.classList.add('strength-weak');
                    strengthText.textContent = 'Weak password';
                    strengthText.style.color = '#e74c3c';
                    break;
                case 2:
                    strengthFill.classList.add('strength-fair');
                    strengthText.textContent = 'Fair password';
                    strengthText.style.color = '#f39c12';
                    break;
                case 3:
                case 4:
                    strengthFill.classList.add('strength-good');
                    strengthText.textContent = 'Good password';
                    strengthText.style.color = '#f1c40f';
                    break;
                case 5:
                    strengthFill.classList.add('strength-strong');
                    strengthText.textContent = 'Strong password';
                    strengthText.style.color = '#27ae60';
                    break;
            }
        }

        // Real-time password strength checking
        document.getElementById('password').addEventListener('input', function() {
            checkPasswordStrength(this.value);
            validatePasswordMatch();
        });

        // Password confirmation validation
        function validatePasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const confirmInput = document.getElementById('confirm_password');
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    confirmInput.style.borderColor = '#27ae60';
                } else {
                    confirmInput.style.borderColor = '#e74c3c';
                }
            } else {
                confirmInput.style.borderColor = 'var(--border-color)';
            }
        }

        document.getElementById('confirm_password').addEventListener('input', validatePasswordMatch);

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const terms = document.getElementById('terms').checked;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }
            
            if (!terms) {
                e.preventDefault();
                alert('Please accept the Terms of Service and Privacy Policy.');
                return;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long.');
                return;
            }
        });

        // Auto-focus username input
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });

        // Theme toggle (inherit from main site)
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark-theme') {
            document.body.classList.add('dark-theme');
        }
    </script>
</body>
</html>