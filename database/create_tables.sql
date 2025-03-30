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

-- Create backup tables
CREATE TABLE categories_backup LIKE categories;
INSERT INTO categories_backup SELECT * FROM categories;

CREATE TABLE documents_backup LIKE documents;
INSERT INTO documents_backup SELECT * FROM documents;

-- Create temporary mapping table for migration
CREATE TABLE category_migration (
    old_name VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    new_id INT NOT NULL
);

-- First drop any existing foreign keys to ensure clean modification
ALTER TABLE categories DROP INDEX name;

-- Modify the categories table to support hierarchy
ALTER TABLE categories 
ADD COLUMN parent_id INT NULL,
ADD COLUMN path VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
ADD COLUMN level INT NOT NULL DEFAULT 0,
ADD COLUMN is_active BOOLEAN NOT NULL DEFAULT TRUE,
ADD COLUMN display_order INT NOT NULL DEFAULT 0,
ADD CONSTRAINT fk_category_parent FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE,
ADD UNIQUE KEY unique_name_parent (name, parent_id);

-- Update existing categories to be root level categories
UPDATE categories SET 
    parent_id = NULL,
    path = CONCAT(id),
    level = 0,
    is_active = TRUE,
    display_order = id;

-- Populate the migration mapping table
INSERT INTO category_migration (old_name, new_id)
SELECT name, id FROM categories;

-- Add new category_id column to documents table
ALTER TABLE documents
ADD COLUMN category_id INT NULL;

-- Update documents with appropriate category_id based on current category name
UPDATE documents d
JOIN category_migration cm ON d.category = cm.old_name
SET d.category_id = cm.new_id;

-- Handle any documents with category names that don't match exactly
-- This is a fallback for any documents with categories that might not have exact matches
UPDATE documents d
LEFT JOIN category_migration cm ON d.category = cm.old_name
SET d.category_id = (SELECT id FROM categories WHERE name = 'Others')
WHERE d.category_id IS NULL;

-- Add foreign key constraint and make category_id NOT NULL
ALTER TABLE documents
MODIFY category_id INT NOT NULL,
ADD CONSTRAINT fk_document_category FOREIGN KEY (category_id) REFERENCES categories(id);

-- Keep the category name column temporarily for reference, but rename it
ALTER TABLE documents 
CHANGE COLUMN category old_category VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;

-- Example: Add subcategories under "Personal"
INSERT INTO categories (name, parent_id, level, is_active, display_order)
SELECT 'Documents', id, 1, TRUE, 1 FROM categories WHERE name = 'Personal';

INSERT INTO categories (name, parent_id, level, is_active, display_order)
SELECT 'Photos', id, 1, TRUE, 2 FROM categories WHERE name = 'Personal';

-- Example: Add subcategories under "Work"
INSERT INTO categories (name, parent_id, level, is_active, display_order)
SELECT 'Contracts', id, 1, TRUE, 1 FROM categories WHERE name = 'Work';

INSERT INTO categories (name, parent_id, level, is_active, display_order)
SELECT 'Projects', id, 1, TRUE, 2 FROM categories WHERE name = 'Work';

-- Example: Add a third level under "Work > Projects"
INSERT INTO categories (name, parent_id, level, is_active, display_order)
SELECT 'Active', id, 2, TRUE, 1 FROM categories WHERE name = 'Projects' AND parent_id IS NOT NULL;

INSERT INTO categories (name, parent_id, level, is_active, display_order)
SELECT 'Archived', id, 2, TRUE, 2 FROM categories WHERE name = 'Projects' AND parent_id IS NOT NULL;

-- Update paths for all categories (recursive approach - would typically use a stored procedure)
-- First level categories (direct children of root)
UPDATE categories c
JOIN categories p ON c.parent_id = p.id
SET c.path = CONCAT(p.path, '/', c.id)
WHERE c.level = 1;

-- Second level categories
UPDATE categories c
JOIN categories p ON c.parent_id = p.id
SET c.path = CONCAT(p.path, '/', c.id)
WHERE c.level = 2;

-- Continue for deeper levels if needed

O categories_backup SELECT * FROM categories;

CREATE TABLE documents_backup LIKE documents;
INSERT INTO documents_backup SELECT * FROM documents;

-- Add indexes for faster hierarchical queries
CREATE INDEX idx_category_parent ON categories(parent_id);
CREATE INDEX idx_category_path ON categories(path);
CREATE INDEX idx_category_level ON categories(level);

