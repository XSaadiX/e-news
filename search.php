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

$query = trim($_GET['query'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 8;
$offset = ($page - 1) * $limit;
$results = [];
$totalResults = 0;
$totalPages = 0;

if (!empty($query)) {
    // Get total count for pagination
    $countStmt = $pdo->prepare("
        SELECT COUNT(DISTINCT a.article_id) FROM articles a 
        LEFT JOIN categories c ON a.category_id = c.category_id 
        LEFT JOIN users u ON a.author_id = u.user_id
        WHERE a.title LIKE :query OR a.content LIKE :query OR c.category_name LIKE :query OR u.username LIKE :query
    ");
    $searchTerm = '%' . $query . '%';
    $countStmt->bindParam(':query', $searchTerm, PDO::PARAM_STR);
    $countStmt->execute();
    $totalResults = $countStmt->fetchColumn();
    $totalPages = ceil($totalResults / $limit);

    // Get search results
    $stmt = $pdo->prepare("
        SELECT DISTINCT a.*, c.category_name, u.username as author_name,
        CASE 
            WHEN a.title LIKE :query THEN 3
            WHEN c.category_name LIKE :query THEN 2
            WHEN a.content LIKE :query THEN 1
            ELSE 0
        END as relevance_score
        FROM articles a 
        LEFT JOIN categories c ON a.category_id = c.category_id 
        LEFT JOIN users u ON a.author_id = u.user_id
        WHERE a.title LIKE :query OR a.content LIKE :query OR c.category_name LIKE :query OR u.username LIKE :query
        ORDER BY relevance_score DESC, a.published_date DESC 
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindParam(':query', $searchTerm, PDO::PARAM_STR);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

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
    <title>Search Results<?php echo !empty($query) ? ' for "' . htmlspecialchars($query) . '"' : ''; ?> - Global News Network</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .search-header {
            background: var(--gradient-primary);
            color: white;
            padding: 2rem 0;
        }
        
        .search-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .search-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .search-content {
            padding: 3rem 0;
        }
        
        .search-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }
        
        .search-stats {
            color: var(--text-light);
        }
        
        .search-time {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .search-results {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        
        .search-result {
            background: var(--card-background);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .search-result:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
            border-left-color: var(--secondary-color);
        }
        
        .result-header {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .result-image {
            width: 150px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            flex-shrink: 0;
        }
        
        .result-content {
            flex: 1;
        }
        
        .result-title {
            font-size: 1.4rem;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }
        
        .result-title a {
            color: var(--text-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .result-title a:hover {
            color: var(--secondary-color);
        }
        
        .result-snippet {
            color: var(--text-light);
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        .result-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.9rem;
            color: var(--text-light);
        }
        
        .result-meta span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .highlight {
            background: rgba(52, 152, 219, 0.2);
            padding: 0.1rem 0.2rem;
            border-radius: 3px;
            font-weight: bold;
        }
        
        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-light);
        }
        
        .no-results i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .search-suggestions {
            background: var(--card-background);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: var(--shadow);
            margin-top: 2rem;
        }
        
        .suggestions-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .suggestion-tag {
            background: var(--secondary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .suggestion-tag:hover {
            background: var(--primary-color);
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .search-title {
                font-size: 2rem;
            }
            
            .search-info {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .result-header {
                flex-direction: column;
            }
            
            .result-image {
                width: 100%;
                height: 200px;
            }
            
            .result-meta {
                flex-direction: column;
                gap: 0.5rem;
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
                            <input type="text" name="query" placeholder="Search news..." 
                                   value="<?php echo htmlspecialchars($query); ?>" required>
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
        <section class="search-header">
            <div class="container">
                <h1 class="search-title">
                    <i class="fas fa-search"></i> Search Results
                </h1>
                <?php if (!empty($query)): ?>
                <p class="search-subtitle">
                    Results for "<?php echo htmlspecialchars($query); ?>"
                </p>
                <?php endif; ?>
            </div>
        </section>

        <section class="search-content">
            <div class="container">
                <div class="content-wrapper">
                    <div class="main-content">
                        <?php if (empty($query)): ?>
                            <div class="no-results">
                                <i class="fas fa-search"></i>
                                <h3>Enter a search term</h3>
                                <p>Please enter a keyword to search for articles.</p>
                            </div>
                        <?php elseif (empty($results)): ?>
                            <div class="search-info">
                                <div class="search-stats">
                                    <strong>0 results</strong> found for "<?php echo htmlspecialchars($query); ?>"
                                </div>
                            </div>
                            
                            <div class="no-results">
                                <i class="fas fa-exclamation-circle"></i>
                                <h3>No articles found</h3>
                                <p>Sorry, we couldn't find any articles matching your search.</p>
                            </div>
                            
                            <div class="search-suggestions">
                                <h4><i class="fas fa-lightbulb"></i> Search Suggestions</h4>
                                <p>Try searching for these popular topics:</p>
                                <div class="suggestions-list">
                                    <a href="search.php?query=technology" class="suggestion-tag">Technology</a>
                                    <a href="search.php?query=politics" class="suggestion-tag">Politics</a>
                                    <a href="search.php?query=sports" class="suggestion-tag">Sports</a>
                                    <a href="search.php?query=business" class="suggestion-tag">Business</a>
                                    <a href="search.php?query=health" class="suggestion-tag">Health</a>
                                    <a href="search.php?query=science" class="suggestion-tag">Science</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="search-info">
                                <div class="search-stats">
                                    <strong><?php echo number_format($totalResults); ?> results</strong> found for "<?php echo htmlspecialchars($query); ?>"
                                    <?php if ($totalPages > 1): ?>
                                        (Page <?php echo $page; ?> of <?php echo $totalPages; ?>)
                                    <?php endif; ?>
                                </div>
                                <div class="search-time">
                                    Search completed in <?php echo number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3); ?> seconds
                                </div>
                            </div>
                            
                            <div class="search-results">
                                <?php foreach ($results as $result): ?>
                                <article class="search-result">
                                    <div class="result-header">
                                        <img src="<?php echo htmlspecialchars($result['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($result['title']); ?>" 
                                             class="result-image">
                                        <div class="result-content">
                                            <h3 class="result-title">
                                                <a href="article.php?id=<?php echo $result['article_id']; ?>">
                                                    <?php 
                                                    $title = htmlspecialchars($result['title']);
                                                    $title = preg_replace('/(' . preg_quote($query, '/') . ')/i', '<span class="highlight">$1</span>', $title);
                                                    echo $title;
                                                    ?>
                                                </a>
                                            </h3>
                                            <p class="result-snippet">
                                                <?php 
                                                $snippet = htmlspecialchars(substr($result['content'], 0, 200));
                                                $snippet = preg_replace('/(' . preg_quote($query, '/') . ')/i', '<span class="highlight">$1</span>', $snippet);
                                                echo $snippet . '...';
                                                ?>
                                            </p>
                                            <div class="result-meta">
                                                <span class="category-tag"><?php echo htmlspecialchars($result['category_name']); ?></span>
                                                <span><i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($result['published_date'])); ?></span>
                                                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($result['author_name'] ?? 'Admin'); ?></span>
                                                <span><i class="fas fa-eye"></i> <?php echo number_format($result['views']); ?> views</span>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="search.php?query=<?php echo urlencode($query); ?>&page=<?php echo $page - 1; ?>" class="page-btn">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                <?php endif; ?>
                                
                                <?php
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $page + 2);
                                
                                for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <a href="search.php?query=<?php echo urlencode($query); ?>&page=<?php echo $i; ?>" 
                                       class="page-btn <?php echo $i == $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <a href="search.php?query=<?php echo urlencode($query); ?>&page=<?php echo $page + 1; ?>" class="page-btn">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <aside class="sidebar">
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
                            <h3><i class="fas fa-tags"></i> Popular Searches</h3>
                            <div class="suggestions-list">
                                <a href="search.php?query=artificial intelligence" class="suggestion-tag">AI</a>
                                <a href="search.php?query=climate change" class="suggestion-tag">Climate</a>
                                <a href="search.php?query=cryptocurrency" class="suggestion-tag">Crypto</a>
                                <a href="search.php?query=space exploration" class="suggestion-tag">Space</a>
                                <a href="search.php?query=world cup" class="suggestion-tag">World Cup</a>
                                <a href="search.php?query=elections" class="suggestion-tag">Elections</a>
                            </div>
                        </div>
                        
                        <div class="sidebar-section">
                            <h3><i class="fas fa-envelope"></i> Newsletter</h3>
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

        // Highlight search terms in results
        function highlightSearchTerms() {
            const query = "<?php echo addslashes($query); ?>";
            if (query) {
                const results = document.querySelectorAll('.result-title a, .result-snippet');
                results.forEach(element => {
                    const regex = new RegExp(`(${query})`, 'gi');
                    element.innerHTML = element.innerHTML.replace(regex, '<span class="highlight">$1</span>');
                });
            }
        }

        // Auto-focus search input if no query
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="query"]');
            if (searchInput && !searchInput.value) {
                searchInput.focus();
            }
        });
    </script>
</body>
</html>