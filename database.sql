-- Luis Marte Portfolio Database Schema
-- Created: October 5, 2025
-- Description: Complete database structure for portfolio website with contact management

-- Create database
CREATE DATABASE IF NOT EXISTS luis_portfolio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE luis_portfolio;

-- Create admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'moderator') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_username (username),
    INDEX idx_email (email)
);

-- Create contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    phone VARCHAR(20) NULL,
    company VARCHAR(100) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    replied_at TIMESTAMP NULL,
    admin_notes TEXT NULL,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_email (email),
    INDEX idx_priority (priority)
);

-- Create projects table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    slug VARCHAR(150) UNIQUE NOT NULL,
    description TEXT NOT NULL,
    short_description VARCHAR(300) NULL,
    image_url VARCHAR(255) NULL,
    gallery JSON NULL, -- Store multiple images
    category ENUM('web', 'wordpress', 'app', 'design', 'other') NOT NULL,
    technologies JSON NULL, -- Store array of technologies used
    project_url VARCHAR(255) NULL,
    github_url VARCHAR(255) NULL,
    demo_url VARCHAR(255) NULL,
    status ENUM('completed', 'in_progress', 'planned', 'archived') DEFAULT 'completed',
    featured BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    meta_title VARCHAR(150) NULL,
    meta_description VARCHAR(300) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL,
    is_published BOOLEAN DEFAULT TRUE,
    view_count INT DEFAULT 0,
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_published (is_published),
    INDEX idx_slug (slug)
);

-- Create skills table
CREATE TABLE IF NOT EXISTS skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category ENUM('frontend', 'backend', 'design', 'tools', 'database', 'other') NOT NULL,
    proficiency_level INT NOT NULL CHECK (proficiency_level >= 0 AND proficiency_level <= 100),
    icon_url VARCHAR(255) NULL,
    icon_class VARCHAR(100) NULL, -- For FontAwesome or other icon classes
    description TEXT NULL,
    years_experience DECIMAL(3,1) NULL,
    is_featured BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_featured (is_featured),
    INDEX idx_proficiency (proficiency_level)
);

-- Create services table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    slug VARCHAR(150) UNIQUE NOT NULL,
    description TEXT NOT NULL,
    short_description VARCHAR(300) NULL,
    icon_class VARCHAR(100) NULL,
    features JSON NULL, -- Store array of service features
    pricing JSON NULL, -- Store pricing information
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_featured (is_featured),
    INDEX idx_slug (slug)
);

-- Create testimonials table
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100) NOT NULL,
    client_company VARCHAR(100) NULL,
    client_position VARCHAR(100) NULL,
    client_email VARCHAR(150) NULL,
    client_photo VARCHAR(255) NULL,
    testimonial_text TEXT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    project_id INT NULL,
    is_featured BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    INDEX idx_featured (is_featured),
    INDEX idx_approved (is_approved),
    INDEX idx_rating (rating)
);

-- Create blog posts table (for future blog functionality)
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    excerpt TEXT NULL,
    content LONGTEXT NOT NULL,
    featured_image VARCHAR(255) NULL,
    category VARCHAR(100) NULL,
    tags JSON NULL,
    author_id INT NOT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    meta_title VARCHAR(150) NULL,
    meta_description VARCHAR(300) NULL,
    view_count INT DEFAULT 0,
    like_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL,
    FOREIGN KEY (author_id) REFERENCES admin_users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_slug (slug),
    INDEX idx_published_at (published_at),
    INDEX idx_category (category)
);

-- Create site settings table
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NULL,
    setting_type ENUM('text', 'number', 'boolean', 'json', 'url', 'email') DEFAULT 'text',
    description TEXT NULL,
    is_public BOOLEAN DEFAULT FALSE, -- Whether setting can be accessed from frontend
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key),
    INDEX idx_public (is_public)
);

-- Create email templates table
CREATE TABLE IF NOT EXISTS email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(100) UNIQUE NOT NULL,
    template_subject VARCHAR(200) NOT NULL,
    template_body LONGTEXT NOT NULL,
    template_type ENUM('contact_confirmation', 'contact_notification', 'newsletter', 'custom') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (template_type),
    INDEX idx_active (is_active)
);

-- Create analytics table
CREATE TABLE IF NOT EXISTS analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_path VARCHAR(255) NOT NULL,
    visitor_ip VARCHAR(45) NULL,
    user_agent TEXT NULL,
    referrer VARCHAR(255) NULL,
    session_id VARCHAR(100) NULL,
    country VARCHAR(100) NULL,
    city VARCHAR(100) NULL,
    device_type ENUM('desktop', 'tablet', 'mobile', 'unknown') DEFAULT 'unknown',
    browser VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_page_path (page_path),
    INDEX idx_created_at (created_at),
    INDEX idx_session (session_id)
);

-- Insert default admin user (password: admin123 - should be changed)
INSERT INTO admin_users (username, email, password_hash, full_name, role) VALUES 
('admin', 'Roandy1017@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Luis Marte', 'admin');

-- Insert default site settings
INSERT INTO site_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('site_title', 'Luis Marte | Frontend Developer Portfolio', 'text', 'Main site title', true),
('site_description', 'Experienced Frontend Developer with expertise in modern web technologies', 'text', 'Site meta description', true),
('contact_email', 'Roandy1017@gmail.com', 'email', 'Main contact email', true),
('contact_phone', '857-399-7397', 'text', 'Contact phone number', true),
('location', 'Boston, MA, USA', 'text', 'Location', true),
('social_github', 'https://github.com/LsMarte', 'url', 'GitHub profile URL', true),
('social_linkedin', 'https://www.linkedin.com/in/luis-marte-shim/', 'url', 'LinkedIn profile URL', true),
('social_instagram', 'https://www.instagram.com/web0.progra/', 'url', 'Instagram profile URL', true),
('social_youtube', 'https://www.youtube.com/@LuisMarte-FED', 'url', 'YouTube channel URL', true),
('smtp_host', '', 'text', 'SMTP server host', false),
('smtp_port', '587', 'number', 'SMTP server port', false),
('smtp_username', '', 'text', 'SMTP username', false),
('smtp_password', '', 'text', 'SMTP password', false),
('smtp_encryption', 'tls', 'text', 'SMTP encryption type', false),
('google_analytics_id', '', 'text', 'Google Analytics tracking ID', false),
('recaptcha_site_key', '', 'text', 'reCAPTCHA site key', false),
('recaptcha_secret_key', '', 'text', 'reCAPTCHA secret key', false);

-- Insert sample skills
INSERT INTO skills (name, category, proficiency_level, icon_class, description, years_experience, is_featured, sort_order) VALUES
('HTML5', 'frontend', 95, 'fab fa-html5', 'Semantic HTML markup and accessibility', 3.0, true, 1),
('CSS3', 'frontend', 90, 'fab fa-css3-alt', 'Advanced CSS, Flexbox, Grid, and animations', 3.0, true, 2),
('JavaScript', 'frontend', 85, 'fab fa-js-square', 'Modern ES6+ JavaScript and DOM manipulation', 2.5, true, 3),
('React', 'frontend', 80, 'fab fa-react', 'Component-based UI development', 1.5, true, 4),
('WordPress', 'backend', 92, 'fab fa-wordpress', 'Custom themes and plugin development', 3.0, true, 5),
('PHP', 'backend', 75, 'fab fa-php', 'Server-side development and APIs', 2.0, false, 6),
('MySQL', 'database', 70, 'fas fa-database', 'Database design and optimization', 2.0, false, 7),
('Figma', 'design', 88, 'fab fa-figma', 'UI/UX design and prototyping', 2.5, true, 8),
('Photoshop', 'design', 82, 'fab fa-adobe', 'Image editing and digital art', 3.0, false, 9),
('Git', 'tools', 85, 'fab fa-git-alt', 'Version control and collaboration', 2.5, false, 10);

-- Insert sample services
INSERT INTO services (title, slug, description, short_description, icon_class, features, is_active, is_featured, sort_order) VALUES
('Web Development', 'web-development', 'Creating responsive, modern websites using HTML5, CSS3, JavaScript, and popular frameworks. Focus on performance, accessibility, and user experience.', 'Modern, responsive websites with optimal performance', 'fas fa-code', '["Responsive Design", "Performance Optimization", "SEO Friendly", "Cross-browser Compatible"]', true, true, 1),
('WordPress Development', 'wordpress-development', 'Custom WordPress themes and plugins development. From simple blogs to complex e-commerce solutions, tailored to your specific needs.', 'Custom WordPress solutions for any business need', 'fab fa-wordpress', '["Custom Themes", "Plugin Development", "E-commerce", "Content Management"]', true, true, 2),
('UI/UX Design', 'ui-ux-design', 'Designing intuitive user interfaces and experiences using Figma, Adobe XD, and other design tools. Focus on user-centered design principles.', 'User-centered design for optimal user experience', 'fas fa-mobile-alt', '["User Research", "Wireframing", "Prototyping", "Design Systems"]', true, true, 3),
('Frontend Development', 'frontend-development', 'Building interactive user interfaces using modern JavaScript frameworks and libraries. Specializing in React and Vue.js development.', 'Interactive frontends with modern frameworks', 'fas fa-laptop-code', '["React Development", "Vue.js", "Component Libraries", "State Management"]', true, false, 4),
('Graphic Design', 'graphic-design', 'Creating visual content using Adobe Creative Suite including Photoshop, Illustrator, and other design tools for web and print media.', 'Professional graphics for web and print', 'fas fa-palette', '["Adobe Creative Suite", "Brand Identity", "Digital Art", "Print Design"]', true, false, 5),
('Video Content', 'video-content', 'Creating engaging video content for social media, YouTube, and websites using professional editing tools and storytelling techniques.', 'Engaging video content for digital platforms', 'fas fa-video', '["Video Editing", "Motion Graphics", "Social Media", "Storytelling"]', true, false, 6);

-- Insert sample projects
INSERT INTO projects (title, slug, description, short_description, category, technologies, project_url, github_url, status, featured, sort_order, is_published) VALUES
('Portfolio Website', 'portfolio-website', 'A modern, responsive portfolio website built with HTML5, CSS3, and JavaScript. Features smooth animations, mobile-first design, and optimized performance.', 'Modern portfolio with smooth animations', 'web', '["HTML5", "CSS3", "JavaScript", "Responsive Design"]', 'https://luismarte.dev', 'https://github.com/LsMarte/portfolio', 'completed', true, 1, true),
('E-commerce WordPress Site', 'ecommerce-wordpress', 'Custom WordPress e-commerce solution with WooCommerce integration, custom theme development, and payment gateway setup.', 'Full-featured e-commerce solution', 'wordpress', '["WordPress", "WooCommerce", "PHP", "MySQL", "Custom Theme"]', null, null, 'completed', true, 2, true),
('Task Management App', 'task-management-app', 'A React-based task management application with drag-and-drop functionality, real-time updates, and responsive design.', 'Interactive task management with React', 'app', '["React", "JavaScript", "CSS3", "Local Storage", "Drag & Drop"]', null, null, 'in_progress', false, 3, true);

-- Insert email templates
INSERT INTO email_templates (template_name, template_subject, template_body, template_type, is_active) VALUES
('contact_confirmation', 'Thank you for contacting Luis Marte', 
'<html><body>
<h2>Thank you for your message!</h2>
<p>Dear {{name}},</p>
<p>Thank you for reaching out to me. I have received your message and will get back to you within 24-48 hours.</p>
<p><strong>Your message:</strong></p>
<p>{{message}}</p>
<p>Best regards,<br>Luis Marte<br>Frontend Developer</p>
</body></html>', 'contact_confirmation', true),

('contact_notification', 'New Contact Form Submission', 
'<html><body>
<h2>New Contact Form Submission</h2>
<p><strong>Name:</strong> {{name}}</p>
<p><strong>Email:</strong> {{email}}</p>
<p><strong>Subject:</strong> {{subject}}</p>
<p><strong>Message:</strong></p>
<p>{{message}}</p>
<p><strong>Submitted:</strong> {{date}}</p>
<p><strong>IP Address:</strong> {{ip}}</p>
</body></html>', 'contact_notification', true);

-- Create indexes for better performance
CREATE INDEX idx_messages_status_date ON contact_messages(status, created_at);
CREATE INDEX idx_projects_category_featured ON projects(category, featured);
CREATE INDEX idx_skills_category_featured ON skills(category, is_featured);

-- Create views for easier data access
CREATE VIEW v_featured_projects AS
SELECT p.*, 
       (SELECT COUNT(*) FROM testimonials t WHERE t.project_id = p.id AND t.is_approved = TRUE) as testimonial_count
FROM projects p 
WHERE p.featured = TRUE AND p.is_published = TRUE 
ORDER BY p.sort_order ASC;

CREATE VIEW v_portfolio_stats AS
SELECT 
    (SELECT COUNT(*) FROM contact_messages WHERE status = 'new') as new_messages,
    (SELECT COUNT(*) FROM contact_messages) as total_messages,
    (SELECT COUNT(*) FROM projects WHERE is_published = TRUE) as published_projects,
    (SELECT COUNT(*) FROM skills WHERE is_featured = TRUE) as featured_skills,
    (SELECT COUNT(*) FROM testimonials WHERE is_approved = TRUE) as approved_testimonials;

-- Add triggers for automatic timestamping
DELIMITER //

CREATE TRIGGER tr_contact_messages_updated 
BEFORE UPDATE ON contact_messages
FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END//

CREATE TRIGGER tr_projects_updated 
BEFORE UPDATE ON projects
FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END//

DELIMITER ;

-- Grant permissions (adjust as needed for your setup)
-- CREATE USER 'portfolio_user'@'localhost' IDENTIFIED BY 'your_secure_password';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON luis_portfolio.* TO 'portfolio_user'@'localhost';
-- FLUSH PRIVILEGES;

-- Display table information
SHOW TABLES;

-- Display portfolio stats
SELECT * FROM v_portfolio_stats;

-- Success message
SELECT 'Database schema created successfully! You can now connect your PHP backend.' as Status;
