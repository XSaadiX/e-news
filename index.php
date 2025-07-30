<?php
// Start session for user management
session_start();

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'news_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Handle connection error gracefully
    $pdo = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global News Network - Stay Informed</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                        <li><a href="index.php" class="active">Home</a></li>
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
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <span class="welcome-user">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <a href="admin/dashboard.php" class="btn-login">Admin Panel</a>
                            <?php endif; ?>
                            <a href="logout.php" class="btn-signup">Logout</a>
                        <?php else: ?>
                            <a href="login.php" class="btn-login">Login</a>
                            <a href="register.php" class="btn-signup">Sign Up</a>
                        <?php endif; ?>
                    </div>
                    
                    <button class="theme-toggle" id="themeToggle">
                        <i class="fas fa-moon"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <main>
        <!-- Breaking News Section -->
        <section class="breaking-news">
            <div class="container">
                <div class="breaking-header">
                    <span class="breaking-label">BREAKING NEWS</span>
                    <div class="breaking-ticker" id="breakingTicker">
                        <?php
                        if ($pdo) {
                            try {
                                $stmt = $pdo->prepare("
                                    SELECT title FROM articles 
                                    WHERE is_breaking = 1 
                                    ORDER BY published_date DESC 
                                    LIMIT 3
                                ");
                                $stmt->execute();
                                $breakingNews = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($breakingNews as $news) {
                                    echo '<span class="breaking-item">' . htmlspecialchars($news['title']) . '</span>';
                                }
                            } catch(PDOException $e) {
                                echo '<span class="breaking-item">Welcome to Global News Network</span>';
                            }
                        } else {
                            echo '<span class="breaking-item">Welcome to Global News Network</span>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <div class="hero-content" id="featuredArticles">
                    <?php
                    if ($pdo) {
                        try {
                            $stmt = $pdo->prepare("
                                SELECT a.*, c.category_name 
                                FROM articles a 
                                LEFT JOIN categories c ON a.category_id = c.category_id 
                                WHERE a.is_featured = 1 
                                ORDER BY a.published_date DESC 
                                LIMIT 1
                            ");
                            $stmt->execute();
                            $featuredArticle = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($featuredArticle) {
                                echo '
                                <div class="hero-article">
                                    <img src="' . htmlspecialchars($featuredArticle['image_url']) . '" alt="' . htmlspecialchars($featuredArticle['title']) . '">
                                    <div class="hero-content-text">
                                        <span class="category-tag">' . htmlspecialchars($featuredArticle['category_name']) . '</span>
                                        <h2><a href="article.php?id=' . $featuredArticle['article_id'] . '">' . htmlspecialchars($featuredArticle['title']) . '</a></h2>
                                        <p>' . htmlspecialchars(substr($featuredArticle['content'], 0, 200)) . '...</p>
                                        <div class="article-meta">
                                            <span><i class="fas fa-calendar"></i> ' . date('F j, Y', strtotime($featuredArticle['published_date'])) . '</span>
                                            <span><i class="fas fa-eye"></i> ' . number_format($featuredArticle['views']) . ' views</span>
                                        </div>
                                    </div>
                                </div>';
                            }
                        } catch(PDOException $e) {
                            echo '<div class="hero-article"><p>Featured content will appear here.</p></div>';
                        }
                    }
                    ?>
                </div>
            </div>
        </section>

        <!-- Latest News Section -->
        <section class="latest-news">
            <div class="container">
                <div class="content-wrapper">
                    <div class="main-content">
                        <h2 class="section-title">Latest News</h2>
                        <div class="news-grid" id="latestNews">
                            <?php
                            if ($pdo) {
                                try {
                                    $stmt = $pdo->prepare("
                                        SELECT a.*, c.category_name, u.username as author_name
                                        FROM articles a 
                                        LEFT JOIN categories c ON a.category_id = c.category_id 
                                        LEFT JOIN users u ON a.author_id = u.user_id
                                        ORDER BY a.published_date DESC 
                                        LIMIT 6
                                    ");
                                    $stmt->execute();
                                    $latestArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    foreach ($latestArticles as $article) {
                                        echo '
                                        <article class="news-card">
                                            <img src="' . htmlspecialchars($article['image_url']) . '" alt="' . htmlspecialchars($article['title']) . '">
                                            <div class="news-content">
                                                <span class="category-tag">' . htmlspecialchars($article['category_name']) . '</span>
                                                <h3><a href="article.php?id=' . $article['article_id'] . '">' . htmlspecialchars($article['title']) . '</a></h3>
                                                <p>' . htmlspecialchars(substr($article['content'], 0, 150)) . '...</p>
                                                <div class="article-meta">
                                                    <span><i class="fas fa-calendar"></i> ' . date('M j, Y', strtotime($article['published_date'])) . '</span>
                                                    <span><i class="fas fa-eye"></i> ' . number_format($article['views']) . ' views</span>
                                                </div>
                                            </div>
                                        </article>';
                                    }
                                } catch(PDOException $e) {
                                    echo '<p>Latest news will appear here.</p>';
                                }
                            }
                            ?>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="pagination" id="pagination">
                            <a href="search.php" class="page-btn">View All Articles</a>
                        </div>
                    </div>
                    
                    <aside class="sidebar">
                        <div class="sidebar-section">
                            <h3>Trending Now</h3>
                            <div class="trending-articles" id="trendingArticles">
                                <?php
                                if ($pdo) {
                                    try {
                                        $stmt = $pdo->prepare("
                                            SELECT a.*, c.category_name 
                                            FROM articles a 
                                            LEFT JOIN categories c ON a.category_id = c.category_id 
                                            ORDER BY a.views DESC 
                                            LIMIT 5
                                        ");
                                        $stmt->execute();
                                        $trendingArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        foreach ($trendingArticles as $trending) {
                                            echo '
                                            <div class="trending-item">
                                                <img src="' . htmlspecialchars($trending['image_url']) . '" alt="' . htmlspecialchars($trending['title']) . '">
                                                <div class="trending-content">
                                                    <h4><a href="article.php?id=' . $trending['article_id'] . '">' . htmlspecialchars($trending['title']) . '</a></h4>
                                                    <span class="trending-meta">' . number_format($trending['views']) . ' views</span>
                                                </div>
                                            </div>';
                                        }
                                    } catch(PDOException $e) {
                                        echo '<p>Trending articles will appear here.</p>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div class="sidebar-section">
                            <h3>Newsletter</h3>
                            <form class="newsletter-form" action="subscribe.php" method="POST">
                                <p>Stay updated with our latest news</p>
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

        <!-- Category Sections -->
        <section class="category-sections">
            <div class="container">
                <div class="categories-grid">
                    <?php
                    if ($pdo) {
                        $categories = ['Politics', 'Technology', 'Sports', 'Entertainment'];
                        $icons = [
                            'Politics' => 'fas fa-balance-scale',
                            'Technology' => 'fas fa-laptop-code',
                            'Sports' => 'fas fa-futbol',
                            'Entertainment' => 'fas fa-film'
                        ];
                        
                        foreach ($categories as $category) {
                            try {
                                $stmt = $pdo->prepare("
                                    SELECT a.*, c.category_name 
                                    FROM articles a 
                                    LEFT JOIN categories c ON a.category_id = c.category_id 
                                    WHERE c.category_name = :category 
                                    ORDER BY a.published_date DESC 
                                    LIMIT 3
                                ");
                                $stmt->bindParam(':category', $category);
                                $stmt->execute();
                                $categoryArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                echo '
                                <div class="category-card">
                                    <h3><i class="' . $icons[$category] . '"></i> ' . $category . '</h3>
                                    <div class="category-articles">';
                                
                                foreach ($categoryArticles as $article) {
                                    echo '
                                        <div class="category-item">
                                            <h4><a href="article.php?id=' . $article['article_id'] . '">' . htmlspecialchars($article['title']) . '</a></h4>
                                            <span class="category-meta">' . date('M j, Y', strtotime($article['published_date'])) . '</span>
                                        </div>';
                                }
                                
                                echo '
                                    </div>
                                    <a href="category.php?category=' . urlencode($category) . '" class="view-more">View More</a>
                                </div>';
                            } catch(PDOException $e) {
                                echo '
                                <div class="category-card">
                                    <h3><i class="' . $icons[$category] . '"></i> ' . $category . '</h3>
                                    <div class="category-articles">
                                        <p>Articles will appear here.</p>
                                    </div>
                                    <a href="category.php?category=' . urlencode($category) . '" class="view-more">View More</a>
                                </div>';
                            }
                        }
                    }
                    ?>
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

    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        const icon = themeToggle.querySelector('i');

        // Check for saved theme preference
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
    </script>
</body>
</html>