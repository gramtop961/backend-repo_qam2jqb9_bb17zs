-- MySQL schema for Automation & Electrical E-commerce
SET NAMES utf8mb4;
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS brands (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  slug VARCHAR(140) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  slug VARCHAR(140) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  brand_id INT NOT NULL,
  category_id INT NOT NULL,
  model VARCHAR(160) NOT NULL,
  price DECIMAL(12,2) NULL,
  short_description VARCHAR(400) NULL,
  long_description TEXT NULL,
  specs JSON NULL,
  image VARCHAR(255) NULL,
  availability ENUM('in_stock','out_of_stock') DEFAULT 'in_stock',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_brand (brand_id),
  INDEX idx_category (category_id),
  FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE RESTRICT,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(60) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  email VARCHAR(160) NOT NULL,
  phone VARCHAR(60) NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed brands
INSERT IGNORE INTO brands (name, slug) VALUES
('Yaskawa','yaskawa'),('Delta','delta'),('Mitsubishi','mitsubishi'),('Fuji','fuji'),('Autonics','autonics'),('Schneider','schneider'),('Siemens','siemens');

-- Seed categories
INSERT IGNORE INTO categories (name, slug) VALUES
('AC Drives / VFD','ac-drives-vfd'),
('PLCs','plcs'),
('HMIs','hmis'),
('Servo Drives & Motors','servo-drives-motors'),
('Power Supply Units','power-supply-units'),
('Industrial Sensors','industrial-sensors'),
('Control Boards','control-boards'),
('Touch Panels','touch-panels'),
('Communication Cables','communication-cables'),
('Industrial Controllers','industrial-controllers'),
('Temperature Controllers','temperature-controllers'),
('Spare Parts','spare-parts');

-- Seed admin (username: admin, password: admin123)
-- Password hash for 'admin123' generated with password_hash('admin123', PASSWORD_BCRYPT)
INSERT IGNORE INTO admin (username, password) VALUES (
  'admin', '$2y$10$k0I1H6zV8l4eYwH2dlQwDOSpUqgQhZC7xQdK1NnQx9i1Lx4e0qI7W'
);
