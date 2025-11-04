CREATE DATABASE sparrow;
USE sparrow;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role ENUM('employee', 'admin') DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE announcements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    author_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id)
);

CREATE TABLE documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    uploaded_by INT,
    file_type VARCHAR(100),
    file_size INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

CREATE TABLE tickets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('open', 'in_progress', 'closed') DEFAULT 'open',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE employees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100),
    department VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    extension VARCHAR(10)
);

-- Insert default users (password is "sparrow123" for both)
INSERT INTO users (username, password, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@sparrow.local', 'admin'),
('john.doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'john@sparrow.local', 'employee');

-- Insert sample employees
INSERT INTO employees (name, position, department, email, phone, extension) VALUES
('Sarah Johnson', 'CEO', 'Executive', 'sarah.johnson@sparrow.local', '+1-555-0101', '1001'),
('Mike Chen', 'CTO', 'Technology', 'mike.chen@sparrow.local', '+1-555-0102', '1002'),
('Emily Davis', 'HR Manager', 'Human Resources', 'emily.davis@sparrow.local', '+1-555-0103', '1003'),
('David Wilson', 'Senior Consultant', 'Consulting', 'david.wilson@sparrow.local', '+1-555-0104', '1004');

-- Insert sample announcements
INSERT INTO announcements (title, content, author_id) VALUES
('Welcome to Sparrow Intranet', 'Welcome to our new intranet system! This platform will help us stay connected and share information efficiently.', 1),
('IT Maintenance Schedule', 'There will be scheduled maintenance this weekend. Please save all your work before Friday 5 PM.', 1);