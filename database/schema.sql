-- database/schema.sql

-- Departments Table
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE
);

-- Issue Types Table
CREATE TABLE IF NOT EXISTS issue_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE
);

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('User', 'Branch Admin', 'Super Admin') NOT NULL,
    department_id INT NULL, -- A user might not be in a department initially or if their role doesn't require it
    branch ENUM('HO', 'SH', 'SP', 'SW') NOT NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL -- Or ON DELETE RESTRICT if a department is critical
);

-- Tickets Table
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    department_id INT NOT NULL,
    issue_type_id INT NOT NULL,
    comment TEXT NOT NULL,
    status ENUM('Pending', 'In Progress', 'Resolved', 'Closed') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, -- If user is deleted, their tickets are deleted
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT, -- Prevent deleting department if tickets exist
    FOREIGN KEY (issue_type_id) REFERENCES issue_types(id) ON DELETE RESTRICT -- Prevent deleting issue type if tickets exist
);

-- Initial Data for Departments
INSERT INTO departments (name) VALUES
('IT'),
('HR'),
('Finance'),
('Marketing');

-- Initial Data for Issue Types
INSERT INTO issue_types (name) VALUES
('Network'),
('Hardware'),
('Software'),
('Other');
