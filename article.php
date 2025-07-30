<?php
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

$articleId = intval($_GET['id'] ?? 0);
if ($articleId <= 0) {
    header('Location: index.html');
    exit;
}

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

if (!$article) {
    header('Location: index.html');
    exit;
}

// Increment view count
$updateViews = $pdo->prepare("UPDATE articles SET views = views + 1 WHERE article_id = :id");
$updateViews->bindParam(':id', $articleId, PDO::PARAM_INT);
$updateViews->execute();

// Get related articles
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

// Get comments
$commentsStmt = $pdo->prepare("
    SELECT c.*, u.username 
    FROM comments c 
    LEFT JOIN users u ON c.user_id = u.user_id 
    WHERE c.article_id = :article_id 
    ORDER BY c.timestamp DESC
");
$commentsStmt->bindParam(':article_id', $articleId, PDO::PARAM_INT);
$commentsStmt->execute();
$comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - Global News Network</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .article-header {
            padding: 2rem 0;
            background: var(--background-color);
        }
        
        .article-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .article-title {
            font-size: 2.5rem;
            line-height: 1.2;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .article-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
            color: var(--text-light);
        }
        
        .article-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .article-text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-color);
            margin-bottom: 3rem;
        }
        
        .article-text p {
            margin-bottom: 1.5rem;
        }
        
        .social-share {
            display: flex;
            gap: 1rem;
            margin-bottom: 3rem;
            padding: 1rem;
            background: var(--card-background);
            border-radius: 10px;
            box-shadow: var(--shadow);
        }
        
        .social-share h4 {
            margin-right: 1rem;
            color: var(--primary-color);
        }
        
        .social-btn {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .social-btn.facebook { background: #3b5998; }
        .social-btn.twitter { background: #1da1f2; }
        .social-btn.linkedin { background: #0077b5; }
        .social-btn.whatsapp { background: #25d366; }
        
        .social-btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }
        
        .comments-section {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 2px solid var(--border-color);
        }
        
        .comments-header {
            margin-bottom: 2rem;
        }
        
        .comment-form {
            background: var(--card-background);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }
        
        .comment-form textarea {
            width: 100%;
            min-height: 120px;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 5px;
            background: var(--background-color);
            color: var(--text-color);
            resize: vertical;
            margin-bottom: 1rem;
        }
        
        .comment-form button {
            background: var(--secondary-color);
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .comment-form button:hover {
            background: var(--primary-color);
        }
        
        .comment {
            background: var(--card-background);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-bottom: 1rem;
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .comment-author {
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .comment-date {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .comment-text {
            line-height: 1.6;
            color: var(--text-color);
        }
        
        .related-articles {
            margin-top: 4rem;
            padding-top: 2rem;
            border-top: 2px solid var(--border-color);
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .related-card {
            background: var(--card-background);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }
        
        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
        
        .related-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .related-content {
            padding: 1.5rem;
        }
        
        .related-content h4 {
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }
        
        .related-content h4 a {
            color: var(--text-color);
            text-decoration: none;
        }
        
        .related-content h4 a:hover {
            color: var(--secondary-color);
        }
        
        .related-meta {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .article-title {
                font-size: 2rem;
            }
            
            .article-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .social-share {
                flex-direction: column;
                gap: 1rem;
            }
            
            .social-share h4 {
                margin-right: 0;
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-globe"></i>
                    <h1>Global News Network</h1>
                </div>
                
                <nav>
                    <ul class="nav-menu">
                        <li><a href="index.php">Home</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropbtn">Categories <i class="fas fa-chevron-down"></i></a>
                            <div class="dropdown-content">
                                <a href="category.php?category=Politics">Politics</a>
                                <a href="category.php?category=Technology">Technology</a>
                                <a href="category.php?category=Sports">Sports</a>
                                <a href="category.php?category=Entertainment">Entertainment</a>
                                <a href="category.php?category=Business">Business</a>
                                <a href="category.php?category=Health">Health</a>
                                <a href="category.php?category=Science">Science</a>
                            </div>
                        </li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </nav>
                
                <div class="header-actions">
                    <div class="search-container">
                        <form action="search.php" method="GET">
                            <input type="text" name="query" placeholder="Search news..." required>
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                    
                    <div class="auth-buttons">
                        <a href="login.php" class="btn-login">Login</a>
                        <a href="register.php" class="btn-signup">Sign Up</a>
                    </div>
                    
                    <button class="theme-toggle" id="themeToggle">
                        <i class="fas fa-moon"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section class="article-header">
            <div class="article-content">
                <span class="category-tag"><?php echo htmlspecialchars($article['category_name']); ?></span>
                <h1 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h1>
                
                <div class="article-meta">
                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($article['author_name'] ?? 'Admin'); ?></span>
                    <span><i class="fas fa-calendar"></i> <?php echo date('F j, Y', strtotime($article['published_date'])); ?></span>
                    <span><i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($article['published_date'])); ?></span>
                    <span><i class="fas fa-eye"></i> <?php echo number_format($article['views']); ?> views</span>
                    <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($article['category_name']); ?></span>
                </div>
                
                <?php if ($article['image_url']): ?>
                <img src="<?php echo htmlspecialchars($article['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($article['title']); ?>" 
                     class="article-image">
                <?php endif; ?>
                
                <div class="article-text">
                    <?php 
                    // Convert line breaks to paragraphs
                    $content = htmlspecialchars($article['content']);
                    $paragraphs = explode("\n\n", $content);
                    foreach ($paragraphs as $paragraph) {
                        if (trim($paragraph)) {
                            echo '<p>' . nl2br(trim($paragraph)) . '</p>';
                        }
                    }
                    ?>
                </div>
                
                <div class="social-share">
                    <h4>Share this article:</h4>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" class="social-btn facebook">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($article['title']); ?>" 
                       target="_blank" class="social-btn twitter">
                        <i class="fab fa-twitter"></i> Twitter
                    </a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" class="social-btn linkedin">
                        <i class="fab fa-linkedin-in"></i> LinkedIn
                    </a>
                    <a href="https://wa.me/?text=<?php echo urlencode($article['title'] . ' - ' . 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" class="social-btn whatsapp">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                </div>
            </div>
        </section>

        <!-- Comments Section -->
        <section class="comments-section">
            <div class="article-content">
                <div class="comments-header">
                    <h3><i class="fas fa-comments"></i> Comments (<?php echo count($comments); ?>)</h3>
                </div>
                
                <div class="comment-form">
                    <h4>Leave a Comment</h4>
                    <form action="add_comment.php" method="POST" id="commentForm">
                        <input type="hidden" name="article_id" value="<?php echo $articleId; ?>">
                        <textarea name="comment_text" placeholder="Write your comment here..." required></textarea>
                        <button type="submit">Post Comment</button>
                    </form>
                </div>
                
                <div class="comments-list">
                    <?php if (empty($comments)): ?>
                        <p style="text-align: center; color: var(--text-light); padding: 2rem;">
                            No comments yet. Be the first to share your thoughts!
                        </p>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <div class="comment-header">
                                <span class="comment-author">
                                    <i class="fas fa-user-circle"></i> 
                                    <?php echo htmlspecialchars($comment['username'] ?? 'Anonymous'); ?>
                                </span>
                                <span class="comment-date">
                                    <?php echo date('M j, Y \a\t g:i A', strtotime($comment['timestamp'])); ?>
                                </span>
                            </div>
                            <div class="comment-text">
                                <?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Related Articles -->
        <?php if (!empty($relatedArticles)): ?>
        <section class="related-articles">
            <div class="article-content">
                <h3><i class="fas fa-newspaper"></i> Related Articles</h3>
                <div class="related-grid">
                    <?php foreach ($relatedArticles as $related): ?>
                    <article class="related-card">
                        <img src="<?php echo htmlspecialchars($related['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($related['title']); ?>">
                        <div class="related-content">
                            <h4><a href="article.php?id=<?php echo $related['article_id']; ?>">
                                <?php echo htmlspecialchars($related['title']); ?>
                            </a></h4>
                            <div class="related-meta">
                                <span class="category-tag"><?php echo htmlspecialchars($related['category_name']); ?></span>
                                <span><?php echo date('M j, Y', strtotime($related['published_date'])); ?></span>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <i class="fas fa-globe"></i>
                        <h3>Global News Network</h3>
                    </div>
                    <p>Your trusted source for breaking news and in-depth analysis from around the world.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                        <li><a href="terms.php">Terms of Service</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Categories</h4>
                    <ul>
                        <li><a href="category.php?category=Politics">Politics</a></li>
                        <li><a href="category.php?category=Technology">Technology</a></li>
                        <li><a href="category.php?category=Sports">Sports</a></li>
                        <li><a href="category.php?category=Entertainment">Entertainment</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <div class="contact-info">
                        <p><i class="fas fa-envelope"></i> news@globalnews.com</p>
                        <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                        <p><i class="fas fa-map-marker-alt"></i> 123 News Street, Media City</p>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Global News Network. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        const icon = themeToggle.querySelector('i');

        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            body.classList.add(savedTheme);
            if (savedTheme === 'dark-theme') {
                icon.classList.replace('fa-moon', 'fa-sun');
            }
        }

        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-theme');
            
            if (body.classList.contains('dark-theme')) {
                icon.classList.replace('fa-moon', 'fa-sun');
                localStorage.setItem('theme', 'dark-theme');
            } else {
                icon.classList.replace('fa-sun', 'fa-moon');
                localStorage.setItem('theme', '');
            }
        });

        // Comment form handling
        document.getElementById('commentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('add_comment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Comment added successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to add comment'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while posting your comment.');
            });
        });
    </script>
</body>
</html>