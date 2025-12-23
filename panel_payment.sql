-- Database: panel_payment
CREATE DATABASE IF NOT EXISTS panel_payment;
USE panel_payment;

-- Table: users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    balance DECIMAL(10,2) DEFAULT 0.00,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: packages
CREATE TABLE packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    quota VARCHAR(20) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    validity_minutes INT DEFAULT 5,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: transactions
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    package_id INT NOT NULL,
    invoice VARCHAR(50) UNIQUE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    unique_code INT DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'QRIS',
    qr_code TEXT,
    reference VARCHAR(100),
    status ENUM('pending', 'paid', 'expired', 'failed') DEFAULT 'pending',
    expired_at DATETIME NOT NULL,
    paid_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (package_id) REFERENCES packages(id),
    INDEX idx_user_status (user_id, status),
    INDEX idx_invoice (invoice)
);

-- Table: payment_logs
CREATE TABLE payment_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id INT NOT NULL,
    action VARCHAR(50),
    note TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id)
);

-- Insert sample data
INSERT INTO packages (name, description, quota, price, validity_minutes) VALUES
('Paket 1GB', 'Internet 1GB All Network', '1GB', 1000, 5),
('Paket 3GB', 'Internet 3GB All Network', '3GB', 5000, 10),
('Paket 10GB', 'Internet 10GB All Network', '10GB', 15000, 30);

INSERT INTO users (username, email, password, role) VALUES
('demo', 'demo@example.com', '$2y$10$YourHashedPassword', 'user');