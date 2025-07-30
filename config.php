<?php
/**
 * Database Configuration File
 * Global News Network
 */

// Database connection settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'news_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8');

// Site configuration
define('SITE_NAME', 'Global News Network');
define('SITE_URL', 'http://localhost/news-website');
define('SITE_EMAIL', 'news@globalnews.com');

// Security settings
define('SESSION_LIFETIME', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 6);

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_PATH', 'uploads/');
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Pagination settings
define('ARTICLES_PER_PAGE', 6);
define('COMMENTS_PER_PAGE', 10);

// Email settings (for newsletter and notifications)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');

// Error reporting (set to false in production)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

/**
 * Database connection function
 */
function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die('Database connection failed: ' . $e->getMessage());
            } else {
                die('Database connection failed. Please try again later.');
            }
        }
    }
    
    return $pdo;
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Redirect with message
 */
function redirectWithMessage($url, $message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit;
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'M j, Y') {
    return date($format, strtotime($date));
}

/**
 * Format number with commas
 */
function formatNumber($number) {
    return number_format($number);
}

/**
 * Generate excerpt from content
 */
function generateExcerpt($content, $length = 150) {
    $content = strip_tags($content);
    if (strlen($content) <= $length) {
        return $content;
    }
    return substr($content, 0, $length) . '...';
}

/**
 * Upload file
 */
function uploadFile($file, $directory = UPLOAD_PATH) {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Invalid parameters.');
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        throw new RuntimeException('Exceeded filesize limit.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];

    if (!array_key_exists($mimeType, $allowedTypes)) {
        throw new RuntimeException('Invalid file format.');
    }

    $extension = $allowedTypes[$mimeType];
    $filename = sprintf('%s.%s', uniqid(), $extension);
    $filepath = $directory . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    return $filepath;
}

/**
 * Send email (basic implementation)
 */
function sendEmail($to, $subject, $message, $headers = '') {
    if (empty($headers)) {
        $headers = "From: " . SITE_EMAIL . "\r\n";
        $headers .= "Reply-To: " . SITE_EMAIL . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    }
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Log activity (for admin)
 */
function logActivity($action, $details = '') {
    if (!isLoggedIn()) return;
    
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (user_id, action, details, timestamp) 
            VALUES (:user_id, :action, :details, NOW())
        ");
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'action' => $action,
            'details' => $details
        ]);
    } catch (Exception $e) {
        // Log error if needed
    }
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    session_start();
}
?>