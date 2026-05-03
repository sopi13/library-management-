-- ============================================================
--  LibraX - Library Management System
--  Database Setup Script
--  File: database/librax_db.sql
--
--  Run this in phpMyAdmin or MySQL CLI:
--    mysql -u root -p < librax_db.sql
-- ============================================================

-- Create and select the database
CREATE DATABASE IF NOT EXISTS `librax_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `librax_db`;

-- ============================================================
-- TABLE: admins
-- Stores admin login credentials
-- ============================================================
CREATE TABLE IF NOT EXISTS `admins` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `username`   VARCHAR(50) NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL COMMENT 'bcrypt hashed password',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin account: username=admin, password=admin123
-- Password hash generated with: password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO `admins` (`username`, `password`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ============================================================
-- TABLE: books
-- Stores all library books
-- ============================================================
CREATE TABLE IF NOT EXISTS `books` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `title`      VARCHAR(200) NOT NULL,
    `author`     VARCHAR(150) NOT NULL,
    `category`   VARCHAR(80)  NOT NULL,
    `quantity`   INT NOT NULL DEFAULT 1 COMMENT 'Total copies owned',
    `available`  INT NOT NULL DEFAULT 1 COMMENT 'Copies currently available',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CHECK (`available` >= 0),
    CHECK (`quantity` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample books data
INSERT INTO `books` (`title`, `author`, `category`, `quantity`, `available`) VALUES
('The Great Gatsby',           'F. Scott Fitzgerald',  'Fiction',       3, 3),
('To Kill a Mockingbird',      'Harper Lee',           'Fiction',       2, 2),
('1984',                       'George Orwell',        'Fiction',       4, 4),
('A Brief History of Time',    'Stephen Hawking',      'Science',       2, 2),
('Clean Code',                 'Robert C. Martin',     'Technology',    3, 3),
('The Pragmatic Programmer',   'David Thomas',         'Technology',    2, 2),
('Sapiens',                    'Yuval Noah Harari',    'History',       3, 3),
('Thinking, Fast and Slow',    'Daniel Kahneman',      'Non-Fiction',   2, 2),
('The Art of War',             'Sun Tzu',              'Philosophy',    5, 5),
('Introduction to Algorithms', 'CLRS',                 'Mathematics',   2, 2);

-- ============================================================
-- TABLE: students
-- Stores registered library members
-- ============================================================
CREATE TABLE IF NOT EXISTS `students` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(120) NOT NULL,
    `student_id` VARCHAR(50)  NOT NULL UNIQUE COMMENT 'College/University roll number',
    `department` VARCHAR(100) NOT NULL,
    `email`      VARCHAR(150) DEFAULT NULL,
    `phone`      VARCHAR(20)  DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample students data
INSERT INTO `students` (`name`, `student_id`, `department`, `email`, `phone`) VALUES
('Alice Johnson',  'CS2024001', 'Computer Science',       'alice@example.com',  '+1234567890'),
('Bob Smith',      'IT2024002', 'Information Technology', 'bob@example.com',    '+1234567891'),
('Carol Williams', 'EC2024003', 'Electronics',            'carol@example.com',  '+1234567892'),
('David Brown',    'ME2024004', 'Mechanical',             'david@example.com',  '+1234567893'),
('Eva Martinez',   'CS2024005', 'Computer Science',       'eva@example.com',    '+1234567894');

-- ============================================================
-- TABLE: transactions
-- Records all book issue and return events
-- ============================================================
CREATE TABLE IF NOT EXISTS `transactions` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `book_id`     INT NOT NULL,
    `student_id`  INT NOT NULL,
    `issue_date`  DATE NOT NULL,
    `return_date` DATE DEFAULT NULL COMMENT 'NULL means book is still issued',
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`book_id`)    REFERENCES `books`(`id`)    ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    INDEX `idx_book_id`    (`book_id`),
    INDEX `idx_student_id` (`student_id`),
    INDEX `idx_return`     (`return_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- USEFUL VIEWS (optional, for reporting)
-- ============================================================

-- View: Active Issues
CREATE OR REPLACE VIEW `v_active_issues` AS
    SELECT t.id, b.title, b.author, s.name AS student_name,
           s.student_id, t.issue_date,
           DATEDIFF(CURDATE(), t.issue_date) AS days_out
    FROM transactions t
    JOIN books b ON t.book_id = b.id
    JOIN students s ON t.student_id = s.id
    WHERE t.return_date IS NULL;

-- View: Book availability summary
CREATE OR REPLACE VIEW `v_book_availability` AS
    SELECT id, title, author, category, quantity, available,
           (quantity - available) AS issued_count,
           CASE WHEN available > 0 THEN 'Available' ELSE 'Out of Stock' END AS status
    FROM books;

-- ============================================================
-- Done! Your LibraX database is ready.
-- Default login: admin / admin123
-- ============================================================
