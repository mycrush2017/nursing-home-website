-- MySQL database schema for nursing home website

CREATE DATABASE IF NOT EXISTS nursing_home;
USE nursing_home;

-- Table for admin users
CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL -- store hashed password
);

-- Insert default admin user (username: admin, password: admin123)
INSERT INTO admins (username, password) VALUES (
  'admin',
  -- Password hashed with PHP password_hash('admin123', PASSWORD_DEFAULT)
  '$2y$10$e0NRzQ6v6v6v6v6v6v6v6u6v6v6v6v6v6v6v6v6v6v6v6v6v6v6v6'
);

-- Table for company info
CREATE TABLE IF NOT EXISTS company_info (
  id INT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  logo VARCHAR(255) NOT NULL,
  address VARCHAR(255) NOT NULL,
  email VARCHAR(100) NOT NULL,
  phone VARCHAR(50) NOT NULL
);

-- Insert default company info
INSERT INTO company_info (id, name, logo, address, email, phone) VALUES (
  1,
  'Sweet Home Adult Family Care',
  'https://static.wixstatic.com/media/11062b_fce4349362194db9a95427b6d511ebaff000.jpg',
  '2953 Bickley Drive, Apopka, FL, USA',
  'info@sweethome.com',
  '(123) 456-7890'
);
