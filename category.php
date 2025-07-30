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

$category = $_GET['category'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 9;
$offset = ($page - 1) * $limit;

if (empty($category)) {
    header('Location: index.html');
    exit;
}

// Get category articles with pagination
$countStmt = $pdo->prepare("
    SELECT COUNT(*) FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.category_id 
    WHERE c.category_name = :category
");
$countStmt->bindParam(':category', $category, PDO::PARAM_STR);
$countStmt->execute();
$totalArticles = $countStmt->fetchColumn();
$totalPages = ceil($totalArticles / $limit);

$stmt = $pdo->prepare("
    SELECT a.*, c.category_name, u.username as author_name
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.category_id 
    LEFT JOIN users u ON a.author_id = u.user_id
    WHERE c.category_name = :category 
    ORDER BY a.published_date DESC 
    LIMIT :limit OFFSET :offset
");
$stmt->bindParam(':category', $category, PDO::PARAM_STR);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get trending articles for sidebar
$trendingStmt = $pdo->prepare("
    SELECT a.*, c.category_name 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.category_id 
    ORDER BY a.views DESC 
    LIMIT 5
");
$trendingStmt->execute();
$trendingArticles = $trendingStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category); ?> News - Global News Network</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .category-header {
            background: var(--gradient-primary);
            color: white;
            padding: 3rem 0;
            text-align: center;
        }
        
        .category-title {
            font-size: 3rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .category-description {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .category-content {
            padding: 3rem 0;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
            color: var(--text-light);
        }
        
        .breadcrumb a {
            color: var(--secondary-color);
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .article-card {
            background: var(--card-background);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }
        
        .article-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }
        
        .article-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .card-content {
            padding: 1.5rem;
        }
        
        .card-title {
            font-size: 1.3rem;
            line-height: 1.4;
            margin-bottom: 0.75rem;
        }
        
        .card-title a {
            color: var(--text-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .card-title a:hover {
            color: var(--secondary-color);
        }
        
        .card-excerpt {
            color: var(--text-light);
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        .card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            color: var(--text-light);
        }
        
        .read-more {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            transition: all 0.3s ease;
        }
        
        .read-more:hover {
            transform: translateX(5px);
        }
        
        .no-articles {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-light);
        }
        
        .no-articles i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .sidebar-category {
            position: sticky;
            top: 100px;
        }
        
        @media (max-width: 768px) {
            .category-title {
                font-size: 2rem;
            }
            
            .articles-grid {
                grid-template-columns: 1fr;
            }
            
            .content-wrapper {
                grid-template-columns: 1fr;
            }
            
            .sidebar-category {
                position: static;
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
        <section class="category-header">
            <div class="container">
                <h1 class="category-title">
                    <?php
                    $icons = [
                        'Politics' => 'fas fa-balance-scale',
                        'Technology' => 'fas fa-laptop-code',
                        'Sports' => 'fas fa-futbol',
                        'Entertainment' => 'fas fa-film',
                        'Business' => 'fas fa-chart-line',
                        'Health' => 'fas fa-heartbeat',
                        'Science' => 'fas fa-atom'
                    ];
                    echo '<i class="' . ($icons[$category] ?? 'fas fa-newspaper') . '"></i> ';
                    echo htmlspecialchars($category);
                    ?>
                </h1>
                <p class="category-description">
                    Stay updated with the latest <?php echo strtolower(htmlspecialchars($category)); ?> news and developments
                </p>
            </div>
        </section>

        <section class="category-content">
            <div class="container">
                <div class="breadcrumb">
                    <a href="index.php"><i class="fas fa-home"></i> Home</a>
                    <i class="fas fa-chevron-right"></i>
                    <span>Categories</span>
                    <i class="fas fa-chevron-right"></i>
                    <span><?php echo htmlspecialchars($category); ?></span>
                </div>
                
                <div class="content-wrapper">
                    <div class="main-content">
                        <?php if (empty($articles)): ?>
                            <div class="no-articles">
                                <i class="fas fa-newspaper"></i>
                                <h3>No articles found</h3>
                                <p>There are currently no articles in the <?php echo htmlspecialchars($category); ?> category.</p>
                                <a href="index.php" class="btn-signup" style="margin-top: 1rem; display: inline-block;">
                                    Browse Other Categories
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="section-title-container" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                                <h2 class="section-title"><?php echo htmlspecialchars($category); ?> Articles</h2>
                                <span class="article-count" style="color: var(--text-light);">
                                    <?php echo number_format($totalArticles); ?> articles found
                                </span>
                            </div>
                            
                            <div class="articles-grid">
                                <?php foreach ($articles as $article): ?>
                                <article class="article-card">
                                    <img src="<?php echo htmlspecialchars($article['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($article['title']); ?>">
                                    <div class="card-content">
                                        <h3 class="card-title">
                                            <a href="article.php?id=<?php echo $article['article_id']; ?>">
                                                <?php echo htmlspecialchars($article['title']); ?>
                                            </a>
                                        </h3>
                                        <p class="card-excerpt">
                                            <?php echo htmlspecialchars(substr($article['content'], 0, 150)) . '...'; ?>
                                        </p>
                                        <div class="card-meta">
                                            <span>
                                                <i class="fas fa-calendar"></i> 
                                                <?php echo date('M j, Y', strtotime($article['published_date'])); ?>
                                            </span>
                                            <span>
                                                <i class="fas fa-eye"></i> 
                                                <?php echo number_format($article['views']); ?>
                                            </span>
                                        </div>
                                        <a href="article.php?id=<?php echo $article['article_id']; ?>" class="read-more">
                                            Read More <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </article>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="category.php?category=<?php echo urlencode($category); ?>&page=<?php echo $page - 1; ?>" class="page-btn">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                <?php endif; ?>
                                
                                <?php
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $page + 2);
                                
                                if ($startPage > 1): ?>
                                    <a href="category.php?category=<?php echo urlencode($category); ?>&page=1" class="page-btn">1</a>
                                    <?php if ($startPage > 2): ?>
                                        <span class="page-btn disabled">...</span>
                                    <?php endif;
                                endif;
                                
                                for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <a href="category.php?category=<?php echo urlencode($category); ?>&page=<?php echo $i; ?>" 
                                       class="page-btn <?php echo $i == $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor;
                                
                                if ($endPage < $totalPages): 
                                    if ($endPage < $totalPages - 1): ?>
                                        <span class="page-btn disabled">...</span>
                                    <?php endif; ?>
                                    <a href="category.php?category=<?php echo urlencode($category); ?>&page=<?php echo $totalPages; ?>" class="page-btn">
                                        <?php echo $totalPages; ?>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <a href="category.php?category=<?php echo urlencode($category); ?>&page=<?php echo $page + 1; ?>" class="page-btn">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <aside class="sidebar sidebar-category">
                        <div class="sidebar-section">
                            <h3><i class="fas fa-fire"></i> Trending Now</h3>
                            <div class="trending-articles">
                                <?php foreach ($trendingArticles as $trending): ?>
                                <div class="trending-item">
                                    <img src="<?php echo htmlspecialchars($trending['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($trending['title']); ?>">
                                    <div class="trending-content">
                                        <h4><a href="article.php?id=<?php echo $trending['article_id']; ?>">
                                            <?php echo htmlspecialchars($trending['title']); ?>
                                        </a></h4>
                                        <span class="trending-meta">
                                            <i class="fas fa-eye"></i> <?php echo number_format($trending['views']); ?> views
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="sidebar-section">
                            <h3><i class="fas fa-list"></i> Categories</h3>
                            <div class="category-list">
                                <?php
                                $categories = ['Politics', 'Technology', 'Sports', 'Entertainment', 'Business', 'Health', 'Science'];
                                foreach ($categories as $cat):
                                ?>
                                <a href="category.php?category=<?php echo urlencode($cat); ?>" 
                                   class="category-link <?php echo $cat == $category ? 'active' : ''; ?>">
                                    <i class="<?php echo $icons[$cat] ?? 'fas fa-newspaper'; ?>"></i>
                                    <?php echo $cat; ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="sidebar-section">
                            <h3><i class="fas fa-envelope"></i> Newsletter</h3>
                            <form class="newsletter-form" action="subscribe.php" method="POST">
                                <p>Get the latest <?php echo strtolower(htmlspecialchars($category)); ?> news delivered to your inbox</p>
                                <input type="email" name="email" placeholder="Your email address" required>
                                <button type="submit">Subscribe</button>
                            </form>
                        </div>
                        
                        <div class="sidebar-section ad-section">
                            <h3>Advertisement</h3>
                            <div class="ad-placeholder">
                                <p>Ad Space</p>
                                <span>300x250</span>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </section>
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

    <style>
        .category-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .category-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .category-link:hover,
        .category-link.active {
            background: var(--secondary-color);
            color: white;
            transform: translateX(5px);
        }
        
        .category-link i {
            width: 20px;
            text-align: center;
        }
        
        .page-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }
    </style>

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

        // Smooth scroll for pagination
        document.querySelectorAll('.pagination a').forEach(link => {
            link.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>