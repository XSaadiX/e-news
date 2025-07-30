<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'news_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Get dashboard statistics
$stats = [];

// Total articles
$stmt = $pdo->query("SELECT COUNT(*) as total FROM articles");
$stats['total_articles'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total users
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total comments
$stmt = $pdo->query("SELECT COUNT(*) as total FROM comments");
$stats['total_comments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total newsletter subscribers
$stmt = $pdo->query("SELECT COUNT(*) as total FROM newsletter_subscribers");
$stats['total_subscribers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Recent articles
$stmt = $pdo->prepare("
    SELECT a.*, c.category_name 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.category_id 
    ORDER BY a.published_date DESC 
    LIMIT 5
");
$stmt->execute();
$recent_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent comments
$stmt = $pdo->prepare("
    SELECT c.*, a.title as article_title, u.username 
    FROM comments c 
    LEFT JOIN articles a ON c.article_id = a.article_id 
    LEFT JOIN users u ON c.user_id = u.user_id 
    ORDER BY c.timestamp DESC 
    LIMIT 5
");
$stmt->execute();
$recent_comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Global News Network</title>
    <link rel="stylesheet" href="../styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-container {
            display: flex;
            min-height: 100vh;
            background: var(--background-color);
        }
        
        .admin-sidebar {
            width: 250px;
            background: var(--primary-color);
            color: white;
            padding: 2rem 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .admin-logo {
            text-align: center;
            padding: 0 1rem 2rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 2rem;
        }
        
        .admin-logo h2 {
            margin-top: 0.5rem;
        }
        
        .admin-nav {
            list-style: none;
            padding: 0;
        }
        
        .admin-nav li {
            margin-bottom: 0.5rem;
        }
        
        .admin-nav a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .admin-nav a:hover,
        .admin-nav a.active {
            background: rgba(255,255,255,0.1);
            border-right: 3px solid var(--secondary-color);
        }
        
        .admin-main {
            margin-left: 250px;
            flex: 1;
            padding: 2rem;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }
        
        .admin-title {
            font-size: 2rem;
            color: var(--primary-color);
        }
        
        .admin-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: var(--card-background);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: var(--text-light);
            font-size: 1rem;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .content-section {
            background: var(--card-background);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: var(--shadow);
        }
        
        .section-title {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .recent-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .recent-item:last-child {
            border-bottom: none;
        }
        
        .recent-content h4 {
            margin-bottom: 0.25rem;
            color: var(--text-color);
        }
        
        .recent-meta {
            font-size: 0.8rem;
            color: var(--text-light);
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: var(--secondary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: var(--accent-color);
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .logout-btn {
            background: transparent;
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.1);
            border-color: var(--accent-color);
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .admin-sidebar.active {
                transform: translateX(0);
            }
            
            .admin-main {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <i class="fas fa-globe" style="font-size: 2rem; color: var(--secondary-color);"></i>
                <h2>Admin Panel</h2>
            </div>
            
            <ul class="admin-nav">
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="articles.php"><i class="fas fa-newspaper"></i> Articles</a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="comments.php"><i class="fas fa-comments"></i> Comments</a></li>
                <li><a href="subscribers.php"><i class="fas fa-envelope"></i> Subscribers</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
        </aside>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1 class="admin-title">Dashboard</h1>
                <div class="admin-actions">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="../index.php" class="btn btn-primary" target="_blank">
                        <i class="fas fa-external-link-alt"></i> View Site
                    </a>
                    <a href="logout.php" class="btn logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--secondary-color);">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['total_articles']); ?></div>
                    <div class="stat-label">Total Articles</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="color: #27ae60;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['total_users']); ?></div>
                    <div class="stat-label">Registered Users</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="color: #f39c12;">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['total_comments']); ?></div>
                    <div class="stat-label">Total Comments</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="color: #e74c3c;">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['total_subscribers']); ?></div>
                    <div class="stat-label">Newsletter Subscribers</div>
                </div>
            </div>
            
            <div class="content-grid">
                <div class="content-section">
                    <h3 class="section-title">
                        <i class="fas fa-newspaper"></i>
                        Recent Articles
                    </h3>
                    
                    <?php if (empty($recent_articles)): ?>
                        <p style="text-align: center; color: var(--text-light); padding: 2rem;">
                            No articles found. <a href="articles.php">Create your first article</a>
                        </p>
                    <?php else: ?>
                        <?php foreach ($recent_articles as $article): ?>
                        <div class="recent-item">
                            <div class="recent-content">
                                <h4><?php echo htmlspecialchars($article['title']); ?></h4>
                                <div class="recent-meta">
                                    <span class="category-tag"><?php echo htmlspecialchars($article['category_name']); ?></span>
                                    <span><?php echo date('M j, Y', strtotime($article['published_date'])); ?></span>
                                    <span><?php echo number_format($article['views']); ?> views</span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="../article.php?id=<?php echo $article['article_id']; ?>" 
                                   class="btn btn-primary" style="font-size: 0.8rem;" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit-article.php?id=<?php echo $article['article_id']; ?>" 
                                   class="btn btn-primary" style="font-size: 0.8rem;">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div style="text-align: center; margin-top: 1rem;">
                            <a href="articles.php" class="btn btn-primary">View All Articles</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="content-section">
                    <h3 class="section-title">
                        <i class="fas fa-comments"></i>
                        Recent Comments
                    </h3>
                    
                    <?php if (empty($recent_comments)): ?>
                        <p style="text-align: center; color: var(--text-light); padding: 2rem;">
                            No comments yet.
                        </p>
                    <?php else: ?>
                        <?php foreach ($recent_comments as $comment): ?>
                        <div class="recent-item">
                            <div class="recent-content">
                                <h4><?php echo htmlspecialchars(substr($comment['comment_text'], 0, 60)) . '...'; ?></h4>
                                <div class="recent-meta">
                                    <span>By <?php echo htmlspecialchars($comment['username']); ?></span>
                                    <span>on "<?php echo htmlspecialchars(substr($comment['article_title'], 0, 30)) . '...'; ?>"</span>
                                    <span><?php echo date('M j, Y', strtotime($comment['timestamp'])); ?></span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="../article.php?id=<?php echo $comment['article_id']; ?>" 
                                   class="btn btn-primary" style="font-size: 0.8rem;" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn btn-danger" style="font-size: 0.8rem;" 
                                        onclick="deleteComment(<?php echo $comment['comment_id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div style="text-align: center; margin-top: 1rem;">
                            <a href="comments.php" class="btn btn-primary">View All Comments</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div style="margin-top: 3rem; text-align: center;">
                <h3 style="margin-bottom: 2rem; color: var(--primary-color);">Quick Actions</h3>
                <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                    <a href="add-article.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Article
                    </a>
                    <a href="articles.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> Manage Articles
                    </a>
                    <a href="users.php" class="btn btn-primary">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                    <a href="settings.php" class="btn btn-primary">
                        <i class="fas fa-cog"></i> Site Settings
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Theme toggle (inherit from main site)
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark-theme') {
            document.body.classList.add('dark-theme');
        }

        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.admin-sidebar');
            sidebar.classList.toggle('active');
        }

        // Add mobile menu button for smaller screens
        if (window.innerWidth <= 768) {
            const header = document.querySelector('.admin-header');
            const menuBtn = document.createElement('button');
            menuBtn.innerHTML = '<i class="fas fa-bars"></i>';
            menuBtn.className = 'btn btn-primary';
            menuBtn.onclick = toggleSidebar;
            header.insertBefore(menuBtn, header.firstChild);
        }

        // Delete comment function
        function deleteComment(commentId) {
            if (confirm('Are you sure you want to delete this comment?')) {
                fetch('delete-comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'comment_id=' + commentId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the comment.');
                });
            }
        }

        // Auto-refresh dashboard every 30 seconds
        setInterval(() => {
            // You could implement AJAX refresh for stats here
            // For now, we'll just update the timestamp
        }, 30000);
    </script>
</body>
</html>