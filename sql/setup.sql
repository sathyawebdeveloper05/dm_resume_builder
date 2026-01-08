-- Create database
CREATE DATABASE IF NOT EXISTS dm_resume_builder CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dm_resume_builder;

-- Admin table
CREATE TABLE IF NOT EXISTS admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('superadmin', 'admin', 'editor') DEFAULT 'admin',
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
);

-- Insert admin user with PLAIN TEXT password (for your login.php)
INSERT INTO admin (username, password) 
VALUES ('DRAGON MEDIA', 'DRAGON#49');

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    last_resume_created TIMESTAMP NULL,
    total_resumes INT DEFAULT 0,
    INDEX idx_email (email),
    INDEX idx_created_at (created_at)
);

-- Resume table
CREATE TABLE IF NOT EXISTS resumes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    resume_name VARCHAR(100) NOT NULL,
    personal_info JSON,
    education JSON,
    experience JSON,
    projects JSON,
    skills JSON,
    certifications JSON,
    template_used VARCHAR(50) DEFAULT 'template-1',
    font_family VARCHAR(100) DEFAULT "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
    primary_color VARCHAR(7) DEFAULT '#3498db',
    text_align VARCHAR(10) DEFAULT 'left',
    pdf_file_path VARCHAR(255) NULL,
    pdf_file_size INT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    view_count INT DEFAULT 0,
    download_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_template (template_used),
    FULLTEXT idx_resume_name (resume_name)
);

-- Activity log table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    admin_id INT NULL,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activity_type (activity_type),
    INDEX idx_created_at (created_at),
    INDEX idx_user_id (user_id),
    INDEX idx_admin_id (admin_id)
);

-- PDF files table (optional, for storing generated PDFs)
CREATE TABLE IF NOT EXISTS pdf_files (
    id INT PRIMARY KEY AUTO_INCREMENT,
    resume_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(50) DEFAULT 'application/pdf',
    download_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE,
    INDEX idx_resume_id (resume_id),
    INDEX idx_created_at (created_at)
);

-- Create views for easier queries
CREATE OR REPLACE VIEW v_resume_stats AS
SELECT 
    DATE(created_at) as date,
    COUNT(*) as resumes_created,
    COUNT(DISTINCT user_id) as unique_users
FROM resumes 
GROUP BY DATE(created_at)
ORDER BY date DESC;

CREATE OR REPLACE VIEW v_user_stats AS
SELECT 
    DATE(created_at) as date,
    COUNT(*) as users_joined
FROM users 
GROUP BY DATE(created_at)
ORDER BY date DESC;

CREATE OR REPLACE VIEW v_popular_templates AS
SELECT 
    template_used,
    COUNT(*) as usage_count,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM resumes), 2) as percentage
FROM resumes 
GROUP BY template_used 
ORDER BY usage_count DESC;

-- Insert sample data for testing (optional)
INSERT INTO users (email, full_name, phone) VALUES 
('john.doe@example.com', 'John Doe', '+1234567890'),
('jane.smith@example.com', 'Jane Smith', '+0987654321'),
('alice.johnson@example.com', 'Alice Johnson', '+1122334455');

INSERT INTO resumes (user_id, resume_name, template_used) VALUES 
(1, 'John Doe - Software Engineer', 'template-1'),
(1, 'John Doe - Updated Resume', 'template-2'),
(2, 'Jane Smith - Web Developer', 'template-1');