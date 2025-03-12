-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    UNIQUE KEY (email)
);

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    UNIQUE KEY (name)
);

-- Create documents table
CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    upload_date DATETIME NOT NULL,
    category VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    file_size INT DEFAULT 0,
    file_type VARCHAR(100),
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert default users if not exists
INSERT INTO users (id, email, firstname, lastname)
VALUES (1, 'sergey@example.com', 'Sergey', 'Osokin')
ON DUPLICATE KEY UPDATE email=email;

INSERT INTO users (id, email, firstname, lastname)
VALUES (2, 'galina@example.com', 'Galina', 'Treneva')
ON DUPLICATE KEY UPDATE email=email;

-- Insert default categories
INSERT INTO categories (name) VALUES ('Personal') ON DUPLICATE KEY UPDATE name=name;
INSERT INTO categories (name) VALUES ('Work') ON DUPLICATE KEY UPDATE name=name;
INSERT INTO categories (name) VALUES ('Others') ON DUPLICATE KEY UPDATE name=name;
INSERT INTO categories (name) VALUES ('State Office') ON DUPLICATE KEY UPDATE name=name;

ALTER TABLE documents 
ADD COLUMN created_date DATE NULL 
AFTER upload_date;

ALTER TABLE categories CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE documents CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;