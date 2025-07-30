-- Create database
CREATE DATABASE IF NOT EXISTS news_db;
USE news_db;

-- Users table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) UNIQUE NOT NULL
);

-- Articles table
CREATE TABLE articles (
    article_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(255),
    category_id INT,
    author_id INT,
    published_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_featured BOOLEAN DEFAULT FALSE,
    is_breaking BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0,
    FOREIGN KEY (category_id) REFERENCES categories(category_id),
    FOREIGN KEY (author_id) REFERENCES users(user_id)
);

-- Comments table
CREATE TABLE comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT,
    user_id INT,
    comment_text TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(article_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Newsletter subscribers table (bonus feature)
CREATE TABLE newsletter_subscribers (
    subscriber_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample categories
INSERT INTO categories (category_name) VALUES 
('Politics'),
('Technology'),
('Sports'),
('Entertainment'),
('Business'),
('Health'),
('Science');

-- Insert sample admin user (password: admin123)
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@globalnews.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample articles
INSERT INTO articles (title, content, image_url, category_id, author_id, is_featured, is_breaking) VALUES 
('Breaking: Major Tech Conference Announces Revolutionary AI Breakthrough', 'In a groundbreaking announcement today, leading technology companies unveiled their latest artificial intelligence innovations that promise to transform industries worldwide. The conference, attended by thousands of tech enthusiasts and industry leaders, showcased cutting-edge developments in machine learning, natural language processing, and computer vision. These advancements are expected to have significant implications for healthcare, education, and business operations across the globe.', 'https://via.placeholder.com/400x250?text=AI+Breakthrough', 2, 1, TRUE, TRUE),

('Political Leaders Meet for Climate Summit', 'World leaders gathered in Geneva this week for an emergency climate summit to discuss urgent environmental policies and international cooperation strategies. The three-day summit focuses on reducing carbon emissions, promoting renewable energy sources, and establishing new frameworks for global environmental protection. Delegates from over 50 countries are participating in these crucial discussions that could shape environmental policy for the next decade.', 'https://via.placeholder.com/400x250?text=Climate+Summit', 1, 1, TRUE, FALSE),

('Championship Finals Draw Record Viewership', 'The highly anticipated championship finals broke television viewership records with over 50 million viewers tuning in worldwide. The thrilling match showcased exceptional athletic performance and sportsmanship, keeping audiences on the edge of their seats until the final moments. Sports analysts are calling it one of the most exciting games in recent history, with both teams displaying remarkable skill and determination throughout the competition.', 'https://via.placeholder.com/400x250?text=Championship', 3, 1, FALSE, FALSE),

('New Streaming Platform Launches with Exclusive Content', 'A major entertainment company launched its new streaming platform today, featuring exclusive original series, documentaries, and films. The platform promises high-quality content across various genres, including drama, comedy, and reality shows. Industry experts predict this launch will intensify competition in the streaming market and provide consumers with more entertainment options than ever before.', 'https://via.placeholder.com/400x250?text=Streaming', 4, 1, FALSE, FALSE),

('Stock Markets Show Strong Growth This Quarter', 'Financial markets demonstrated robust performance this quarter, with major indices reaching new highs amid positive economic indicators. Analysts attribute this growth to strong corporate earnings, increased consumer spending, and favorable monetary policies. Investment experts recommend cautious optimism as markets continue to navigate global economic uncertainties while showing signs of sustained recovery.', 'https://via.placeholder.com/400x250?text=Stock+Market', 5, 1, FALSE, FALSE),

('Breakthrough Medical Research Offers New Hope', 'Researchers at leading medical institutions announced significant progress in treating previously incurable diseases through innovative gene therapy techniques. The groundbreaking research, published in prestigious medical journals, demonstrates promising results in clinical trials and offers new hope for millions of patients worldwide. Medical experts believe these advances could revolutionize treatment approaches and improve patient outcomes significantly.', 'https://via.placeholder.com/400x250?text=Medical+Research', 6, 1, TRUE, FALSE),

('Space Mission Discovers Potential Signs of Life', 'A recent space exploration mission has uncovered intriguing evidence that could indicate the presence of microbial life on a distant planet. Scientists are carefully analyzing data collected by advanced space probes to verify these preliminary findings. This discovery could have profound implications for our understanding of life in the universe and may lead to new space exploration initiatives in the coming years.', 'https://via.placeholder.com/400x250?text=Space+Discovery', 7, 1, FALSE, FALSE);