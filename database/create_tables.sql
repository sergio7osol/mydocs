-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    UNIQUE KEY (email)
);

-- Create documents table
CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    upload_date DATETIME NOT NULL,
    category VARCHAR(50) NOT NULL,
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

ALTER TABLE documents 
ADD COLUMN created_date DATE NULL 
AFTER upload_date;