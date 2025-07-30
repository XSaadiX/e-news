<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Global News Network</title>
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
                        <li><a href="about.php" class="active">About Us</a></li>
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

    <main style="padding: 4rem 0; min-height: 60vh;">
        <div class="container">
            <div class="content-wrapper" style="max-width: 800px; margin: 0 auto;">
                <h1 style="font-size: 3rem; text-align: center; margin-bottom: 2rem; color: var(--primary-color);">About Global News Network</h1>
                
                <div style="background: var(--card-background); padding: 3rem; border-radius: 15px; box-shadow: var(--shadow); margin-bottom: 3rem;">
                    <h2 style="color: var(--secondary-color); margin-bottom: 1.5rem;">Our Mission</h2>
                    <p style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 2rem;">
                        Global News Network is your trusted source for breaking news and in-depth analysis from around the world. 
                        We are committed to delivering accurate, timely, and unbiased news coverage across multiple categories 
                        including politics, technology, sports, entertainment, business, health, and science.
                    </p>
                    
                    <h2 style="color: var(--secondary-color); margin-bottom: 1.5rem;">What We Offer</h2>
                    <ul style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 2rem;">
                        <li>Breaking news updates as they happen</li>
                        <li>In-depth analysis and investigative reporting</li>
                        <li>Coverage across multiple categories and regions</li>
                        <li>User-friendly interface with search functionality</li>
                        <li>Mobile-responsive design for reading on any device</li>
                        <li>Community engagement through comments and discussions</li>
                    </ul>
                    
                    <h2 style="color: var(--secondary-color); margin-bottom: 1.5rem;">Our Values</h2>
                    <p style="font-size: 1.1rem; line-height: 1.8;">
                        We believe in the power of informed journalism to create a better world. Our team of dedicated 
                        journalists and editors work tirelessly to bring you news that matters, with integrity, 
                        accuracy, and respect for all perspectives.
                    </p>
                </div>
            </div>
        </div>
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
    </script>
</body>
</html>