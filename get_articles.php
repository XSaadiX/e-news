<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'news_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Get request parameters
$type = $_GET['type'] ?? '';
$category = $_GET['category'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = max(1, min(50, intval($_GET['limit'] ?? 6)));
$offset = ($page - 1) * $limit;

try {
    switch ($type) {
        case 'breaking':
            $stmt = $pdo->prepare("
                SELECT a.*, c.category_name 
                FROM articles a 
                LEFT JOIN categories c ON a.category_id = c.category_id 
                WHERE a.is_breaking = 1 
                ORDER BY a.published_date DESC 
                LIMIT 5
            ");
            $stmt->execute();
            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($articles);
            break;

        case 'featured':
            $stmt = $pdo->prepare("
                SELECT a.*, c.category_name 
                FROM articles a 
                LEFT JOIN categories c ON a.category_id = c.category_id 
                WHERE a.is_featured = 1 
                ORDER BY a.published_date DESC 
                LIMIT 3
            ");
            $stmt->execute();
            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($articles);
            break;

        case 'latest':
            // Get total count for pagination
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM articles");
            $countStmt->execute();
            $totalArticles = $countStmt->fetchColumn();
            $totalPages = ceil($totalArticles / $limit);

            // Get articles for current page
            $stmt = $pdo->prepare("
                SELECT a.*, c.category_name, u.username as author_name
                FROM articles a 
                LEFT JOIN categories c ON a.category_id = c.category_id 
                LEFT JOIN users u ON a.author_id = u.user_id
                ORDER BY a.published_date DESC 
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = [
                'articles' => $articles,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_articles' => $totalArticles,
                    'per_page' => $limit
                ]
            ];
            echo json_encode($response);
            break;

        case 'trending':
            $stmt = $pdo->prepare("
                SELECT a.*, c.category_name 
                FROM articles a 
                LEFT JOIN categories c ON a.category_id = c.category_id 
                ORDER BY a.views DESC, a.published_date DESC 
                LIMIT 5
            ");
            $stmt->execute();
            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($articles);
            break;

        case 'category':
            if (empty($category)) {
                echo json_encode(['error' => 'Category parameter is required']);
                break;
            }

            $stmt = $pdo->prepare("
                SELECT a.*, c.category_name, u.username as author_name
                FROM articles a 
                LEFT JOIN categories c ON a.category_id = c.category_id 
                LEFT JOIN users u ON a.author_id = u.user_id
                WHERE c.category_name = :category 
                ORDER BY a.published_date DESC 
                LIMIT :limit
            ");
            $stmt->bindParam(':category', $category, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($articles);
            break;

        case 'single':
            $articleId = intval($_GET['id'] ?? 0);
            if ($articleId <= 0) {
                echo json_encode(['error' => 'Valid article ID is required']);
                break;
            }

            // Increment view count
            $updateViews = $pdo->prepare("UPDATE articles SET views = views + 1 WHERE article_id = :id");
            $updateViews->bindParam(':id', $articleId, PDO::PARAM_INT);
            $updateViews->execute();

            // Get article details
            $stmt = $pdo->prepare("
                SELECT a.*, c.category_name, u.username as author_name
                FROM articles a 
                LEFT JOIN categories c ON a.category_id = c.category_id 
                LEFT JOIN users u ON a.author_id = u.user_id
                WHERE a.article_id = :id
            ");
            $stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
            $stmt->execute();
            $article = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($article) {
                // Get related articles from same category
                $relatedStmt = $pdo->prepare("
                    SELECT a.*, c.category_name 
                    FROM articles a 
                    LEFT JOIN categories c ON a.category_id = c.category_id 
                    WHERE a.category_id = :category_id AND a.article_id != :article_id 
                    ORDER BY a.published_date DESC 
                    LIMIT 4
                ");
                $relatedStmt->bindParam(':category_id', $article['category_id'], PDO::PARAM_INT);
                $relatedStmt->bindParam(':article_id', $articleId, PDO::PARAM_INT);
                $relatedStmt->execute();
                $relatedArticles = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);

                $response = [
                    'article' => $article,
                    'related_articles' => $relatedArticles
                ];
                echo json_encode($response);
            } else {
                echo json_encode(['error' => 'Article not found']);
            }
            break;

        case 'search':
            $query = $_GET['query'] ?? '';
            if (empty($query)) {
                echo json_encode(['error' => 'Search query is required']);
                break;
            }

            // Get total count for pagination
            $countStmt = $pdo->prepare("
                SELECT COUNT(*) FROM articles a 
                LEFT JOIN categories c ON a.category_id = c.category_id 
                WHERE a.title LIKE :query OR a.content LIKE :query OR c.category_name LIKE :query
            ");
            $searchTerm = '%' . $query . '%';
            $countStmt->bindParam(':query', $searchTerm, PDO::PARAM_STR);
            $countStmt->execute();
            $totalArticles = $countStmt->fetchColumn();
            $totalPages = ceil($totalArticles / $limit);

            // Get search results
            $stmt = $pdo->prepare("
                SELECT a.*, c.category_name, u.username as author_name
                FROM articles a 
                LEFT JOIN categories c ON a.category_id = c.category_id 
                LEFT JOIN users u ON a.author_id = u.user_id
                WHERE a.title LIKE :query OR a.content LIKE :query OR c.category_name LIKE :query
                ORDER BY a.published_date DESC 
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindParam(':query', $searchTerm, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = [
                'articles' => $articles,
                'query' => $query,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_articles' => $totalArticles,
                    'per_page' => $limit
                ]
            ];
            echo json_encode($response);
            break;

        case 'categories':
            $stmt = $pdo->prepare("
                SELECT c.*, COUNT(a.article_id) as article_count 
                FROM categories c 
                LEFT JOIN articles a ON c.category_id = a.category_id 
                GROUP BY c.category_id 
                ORDER BY c.category_name
            ");
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($categories);
            break;

        default:
            echo json_encode(['error' => 'Invalid request type']);
            break;
    }

} catch(PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>