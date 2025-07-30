<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Global News Network</title>
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
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php" class="active">Contact</a></li>
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
            <h1 style="font-size: 3rem; text-align: center; margin-bottom: 3rem; color: var(--primary-color);">Contact Us</h1>
            
            <div class="content-wrapper" style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; max-width: 1000px; margin: 0 auto;">
                <div style="background: var(--card-background); padding: 3rem; border-radius: 15px; box-shadow: var(--shadow);">
                    <h2 style="color: var(--secondary-color); margin-bottom: 2rem;">Get in Touch</h2>
                    
                    <div style="margin-bottom: 2rem;">
                        <h3 style="margin-bottom: 1rem;"><i class="fas fa-envelope" style="color: var(--secondary-color); margin-right: 0.5rem;"></i>Email</h3>
                        <p>saadi.dev.ps@gmail.com</p>
                        <p>saadi.dev.ps@gmail.com</p>
                    </div>
                    
                    <div style="margin-bottom: 2rem;">
                        <h3 style="margin-bottom: 1rem;"><i class="fas fa-phone" style="color: var(--secondary-color); margin-right: 0.5rem;"></i>Phone</h3>
                        <p>+201094719807</p>
                        <p> (Newsroom)</p>
                    </div>
                    
                    <div style="margin-bottom: 2rem;">
                        <h3 style="margin-bottom: 1rem;"><i class="fas fa-map-marker-alt" style="color: var(--secondary-color); margin-right: 0.5rem;"></i>Address</h3>
                        <p>Gaza<br>Gaza City, shek-eglin<br>Palestine</p>
                    </div>
                    
                    <div>
                        <h3 style="margin-bottom: 1rem;"><i class="fas fa-clock" style="color: var(--secondary-color); margin-right: 0.5rem;"></i>Business Hours</h3>
                        <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                        <p>Saturday: 10:00 AM - 4:00 PM</p>
                        <p>Sunday: Closed</p>
                    </div>
                </div>
                
                <div style="background: var(--card-background); padding: 3rem; border-radius: 15px; box-shadow: var(--shadow);">
                    <h2 style="color: var(--secondary-color); margin-bottom: 2rem;">Send us a Message</h2>
                    
                    <form style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Name *</label>
                            <input type="text" required style="width: 100%; padding: 1rem; border: 2px solid var(--border-color); border-radius: 8px; background: var(--background-color); color: var(--text-color);">
                        </div>
                        
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Email *</label>
                            <input type="email" required style="width: 100%; padding: 1rem; border: 2px solid var(--border-color); border-radius: 8px; background: var(--background-color); color: var(--text-color);">
                        </div>
                        
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Subject *</label>
                            <input type="text" required style="width: 100%; padding: 1rem; border: 2px solid var(--border-color); border-radius: 8px; background: var(--background-color); color: var(--text-color);">
                        </div>
                        
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Message *</label>
                            <textarea required rows="5" style="width: 100%; padding: 1rem; border: 2px solid var(--border-color); border-radius: 8px; background: var(--background-color); color: var(--text-color); resize: vertical;"></textarea>
                        </div>
                        
                        <button type="submit" style="background: var(--secondary-color); color: white; padding: 1rem 2rem; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: all 0.3s ease;">
                            <i class="fas fa-paper-plane" style="margin-right: 0.5rem;"></i>Send Message
                        </button>
                    </form>
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

    <style>
        @media (max-width: 768px) {
            .content-wrapper {
                grid-template-columns: 1fr !important;
            }
        }
        
        button[type="submit"]:hover {
            background: var(--primary-color) !important;
            transform: translateY(-2px);
        }
        
        input:focus, textarea:focus {
            border-color: var(--secondary-color) !important;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
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

        // Form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for your message! We will get back to you soon.');
        });
    </script>
</body>
</html>