<?php
session_start();
header('Content-Type: application/json');

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'news_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$articleId = intval($_POST['article_id'] ?? 0);
$commentText = trim($_POST['comment_text'] ?? '');

// Validation
if ($articleId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid article ID']);
    exit;
}

if (empty($commentText)) {
    echo json_encode(['success' => false, 'message' => 'Comment text is required']);
    exit;
}

if (strlen($commentText) < 3) {
    echo json_encode(['success' => false, 'message' => 'Comment must be at least 3 characters long']);
    exit;
}

if (strlen($commentText) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Comment must be less than 1000 characters']);
    exit;
}

// Check if article exists
$stmt = $pdo->prepare("SELECT article_id FROM articles WHERE article_id = :article_id");
$stmt->bindParam(':article_id', $articleId, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    echo json_encode(['success' => false, 'message' => 'Article not found']);
    exit;
}

// For demo purposes, we'll allow anonymous comments
// In a real application, you'd want to require login
$userId = $_SESSION['user_id'] ?? null;

try {
    if ($userId) {
        // Logged in user
        $stmt = $pdo->prepare("
            INSERT INTO comments (article_id, user_id, comment_text, timestamp) 
            VALUES (:article_id, :user_id, :comment_text, NOW())
        ");
        $stmt->bindParam(':article_id', $articleId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':comment_text', $commentText, PDO::PARAM_STR);
    } else {
        // Anonymous comment - create a temporary anonymous user entry
        // First check if anonymous user exists
        $anonStmt = $pdo->prepare("SELECT user_id FROM users WHERE username = 'Anonymous' AND email = 'anonymous@example.com'");
        $anonStmt->execute();
        $anonUser = $anonStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$anonUser) {
            // Create anonymous user
            $createAnonStmt = $pdo->prepare("
                INSERT INTO users (username, email, password, role) 
                VALUES ('Anonymous', 'anonymous@example.com', '', 'user')
            ");
            $createAnonStmt->execute();
            $anonUserId = $pdo->lastInsertId();
        } else {
            $anonUserId = $anonUser['user_id'];
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO comments (article_id, user_id, comment_text, timestamp) 
            VALUES (:article_id, :user_id, :comment_text, NOW())
        ");
        $stmt->bindParam(':article_id', $articleId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $anonUserId, PDO::PARAM_INT);
        $stmt->bindParam(':comment_text', $commentText, PDO::PARAM_STR);
    }
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Comment added successfully',
            'comment_id' => $pdo->lastInsertId()
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add comment']);
    }
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>