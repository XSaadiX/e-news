<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'news_db';

$response = ['success' => false, 'message' => ''];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    $response['message'] = 'Database connection failed';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    // Validation
    if (empty($email)) {
        $response['message'] = 'Email address is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT subscriber_id FROM newsletter_subscribers WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $response['message'] = 'You are already subscribed to our newsletter';
            } else {
                // Add new subscriber
                $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email, subscribed_at) VALUES (:email, NOW())");
                $stmt->bindParam(':email', $email);
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Successfully subscribed to our newsletter!';
                    
                    // Here you could add email sending functionality
                    // For example, send a welcome email or confirmation email
                    
                } else {
                    $response['message'] = 'Subscription failed. Please try again.';
                }
            }
        } catch(PDOException $e) {
            $response['message'] = 'Database error occurred';
        }
    }
    
    // Handle AJAX requests
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Handle regular form submissions
    $redirectUrl = $_POST['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? 'index.php';
    $redirectUrl .= (strpos($redirectUrl, '?') !== false ? '&' : '?') . 'subscribe=' . ($response['success'] ? 'success' : 'error') . '&message=' . urlencode($response['message']);
    header('Location: ' . $redirectUrl);
    exit;
}

// If not a POST request, redirect to home
header('Location: index.php');
exit;
?>