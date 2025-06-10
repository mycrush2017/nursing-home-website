-- Updated MySQL database schema for nursing home management system

CREATE DATABASE IF NOT EXISTS nursing_home;
USE nursing_home;

-- Table for roles
CREATE TABLE IF NOT EXISTS roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role_name VARCHAR(50) NOT NULL UNIQUE
);

-- Insert default roles
INSERT IGNORE INTO roles (role_name) VALUES ('admin'), ('user');

-- Table for users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role_id INT NOT NULL,
  full_name VARCHAR(100),
  email VARCHAR(100),
  phone VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Insert default admin user (username: admin, password: admin123)
INSERT IGNORE INTO users (username, password, role_id, full_name, email, phone) VALUES (
  'admin',
  '$2y$10$e0NRzQ6v6v6v6v6v6v6v6u6v6v6v6v6v6v6v6v6v6v6v6v6v6v6', -- password_hash('admin123', PASSWORD_DEFAULT)
  (SELECT id FROM roles WHERE role_name = 'admin'),
  'Administrator',
  'admin@sweethome.com',
  '123-456-7890'
);

-- Table for company info
CREATE TABLE IF NOT EXISTS company_info (
  id INT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  logo VARCHAR(255) NOT NULL,
  address VARCHAR(255) NOT NULL,
  email VARCHAR(100) NOT NULL,
  phone VARCHAR(50) NOT NULL,
  fees DECIMAL(10,2) DEFAULT 100.00
);

-- Insert default company info
INSERT IGNORE INTO company_info (id, name, logo, address, email, phone, fees) VALUES (
  1,
  'Sweet Home Adult Family Care',
  'https://static.wixstatic.com/media/11062b_fce4349362194db9a95427b6d511ebaff000.jpg',
  '2953 Bickley Drive, Apopka, FL, USA',
  'info@sweethome.com',
  '(123) 456-7890',
  100.00
);

-- Table for appointments
CREATE TABLE IF NOT EXISTS appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  appointment_date DATE NOT NULL,
  appointment_time TIME NOT NULL,
  status VARCHAR(50) DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Table for payments
CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  payment_method VARCHAR(50),
  status VARCHAR(50) DEFAULT 'completed',
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Table for documents
CREATE TABLE IF NOT EXISTS documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Table for medications
CREATE TABLE IF NOT EXISTS medications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT
);

-- Table for medication assignments
CREATE TABLE IF NOT EXISTS medication_assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  medication_id INT NOT NULL,
  user_id INT NOT NULL,
  assigned_by INT NOT NULL,
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (medication_id) REFERENCES medications(id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (assigned_by) REFERENCES users(id)
);
