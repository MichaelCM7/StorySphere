-- storysphere_db Migration File (Schema with Status FK - Soft Delete)
-- Generation Time: Oct 11, 2025

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `storysphere`
--
CREATE DATABASE IF NOT EXISTS `storysphere`;
USE `storysphere`;

-- --------------------------------------------------------

--
-- Table structure for table `book_statuses` (New Lookup Table)
--
CREATE TABLE `book_statuses` (
  `book_status_id` int NOT NULL,
  `status_name` varchar(50) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT '0' -- Soft Delete Field
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Stored Procedures (No change needed)
--
DELIMITER $$

-- Status IDs assumption for procedures:
-- 1: Issued/Currently Borrowed
-- 2: Returned
-- 3: Overdue
-- 4: Lost

CREATE DEFINER=`root`@`localhost` PROCEDURE `IssueBook` (IN `p_user_id` INT, IN `p_book_id` INT)
  BEGIN
    DECLARE v_available_copies INT;
    DECLARE v_borrowing_days INT DEFAULT 14;
    DECLARE v_issued_status_id INT DEFAULT 1; -- Assuming 'Currently Borrowed' is ID 1
    
    SELECT available_copies INTO v_available_copies 
    FROM books WHERE book_id = p_book_id;
    
    IF v_available_copies > 0 THEN
        INSERT INTO borrowing_records (user_id, book_id, issue_date, due_date, book_status_id)
        VALUES (p_user_id, p_book_id, NOW(), DATE_ADD(CURDATE(), INTERVAL v_borrowing_days DAY), v_issued_status_id);
        
        UPDATE books 
        SET available_copies = available_copies - 1 
        WHERE book_id = p_book_id;
        
        SELECT 'Book issued successfully' AS message;
    ELSE
        SELECT 'Book not available' AS message;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ReturnBook` (IN `p_borrowing_id` INT)
  BEGIN
    DECLARE v_book_id INT;
    DECLARE v_due_date DATE;
    DECLARE v_fine_amount DECIMAL(10,2);
    DECLARE v_user_id INT;
    DECLARE v_fine_per_day DECIMAL(10,2) DEFAULT 10.00;
    DECLARE v_returned_status_id INT DEFAULT 2; -- Assuming 'Returned' is ID 2
    
    SELECT book_id, due_date, user_id INTO v_book_id, v_due_date, v_user_id
    FROM borrowing_records 
    WHERE borrowing_id = p_borrowing_id;
    
    UPDATE borrowing_records 
    SET return_date = CURDATE(), book_status_id = v_returned_status_id
    WHERE borrowing_id = p_borrowing_id;
    
    UPDATE books 
    SET available_copies = available_copies + 1 
    WHERE book_id = v_book_id;
    
    IF CURDATE() > v_due_date THEN
        SET v_fine_amount = DATEDIFF(CURDATE(), v_due_date) * v_fine_per_day;
        
        INSERT INTO fines (user_id, borrowing_id, fine_amount, fine_reason)
        VALUES (v_user_id, p_borrowing_id, v_fine_amount, 'Overdue return');
    END IF;
    
    SELECT 'Book returned successfully' AS message;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `roles` (Simplified)
--
CREATE TABLE `roles` (
  `role_id` int NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT '0' -- Soft Delete Field
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users` (Modified)
--
CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `combined_username` varchar(100) GENERATED ALWAYS AS (CONCAT(`first_name`, ' ', `last_name`)) STORED,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `role_id` int DEFAULT '3',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) DEFAULT '0' -- Soft Delete Field
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `authors`
--
CREATE TABLE `authors` (
  `author_id` int NOT NULL,
  `author_name` varchar(100) NOT NULL,
  `biography` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) DEFAULT '0' -- Soft Delete Field
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--
CREATE TABLE `categories` (
  `category_id` int NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `description` text,
  `is_deleted` tinyint(1) DEFAULT '0' -- Soft Delete Field
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `books`
--
CREATE TABLE `books` (
  `book_id` int NOT NULL,
  `isbn` varchar(13) DEFAULT NULL,
  `google_books_id` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `author_id` int DEFAULT NULL,
  `publisher` varchar(100) DEFAULT NULL,
  `published_date` date DEFAULT NULL,
  `page_count` int DEFAULT NULL,
  `description` text,
  `cover_image_url` varchar(500) DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `language` varchar(10) DEFAULT 'en',
  `total_copies` int DEFAULT '1',
  `available_copies` int DEFAULT '1',
  `shelf_location` varchar(50) DEFAULT NULL,
  `book_condition` enum('excellent','good','fair','poor') DEFAULT 'good',
  `is_deleted` tinyint(1) DEFAULT '0' -- Soft Delete Field
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `borrowing_records`
--
CREATE TABLE `borrowing_records` (
  `borrowing_id` int NOT NULL,
  `user_id` int NOT NULL,
  `book_id` int NOT NULL,
  `issue_date` datetime NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `book_status_id` int DEFAULT 1, -- Foreign Key to book_statuses
  `is_deleted` tinyint(1) DEFAULT '0' -- Soft Delete Field
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ratings` (Simplified for Google Books API Rating)
--
CREATE TABLE `ratings` (
  `book_id` int NOT NULL,
  `rating` decimal(2,1) DEFAULT NULL,
  `rating_count` int DEFAULT 0,
  `is_deleted` tinyint(1) DEFAULT '0' -- Soft Delete Field
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fines`
--
CREATE TABLE `fines` (
  `fine_id` int NOT NULL,
  `user_id` int NOT NULL,
  `borrowing_id` int DEFAULT NULL,
  `fine_amount` decimal(10,2) NOT NULL,
  `fine_reason` varchar(255) DEFAULT NULL,
  `payment_status` enum('unpaid','paid','waived') DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) DEFAULT '0' -- Soft Delete Field
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--
CREATE TABLE `reservations` (
  `reservation_id` int NOT NULL,
  `user_id` int NOT NULL,
  `book_id` int NOT NULL,
  `reservation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expiry_date` timestamp NULL DEFAULT NULL,
  `status` enum('active','fulfilled','cancelled','expired') DEFAULT 'active',
  `is_deleted` tinyint(1) DEFAULT '0' -- Soft Delete Field
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Indexes for tables (No change needed for new columns)
--
ALTER TABLE `book_statuses`
  ADD PRIMARY KEY (`book_status_id`),
  ADD UNIQUE KEY `status_name` (`status_name`);

ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role_id` (`role_id`),
  ADD KEY `idx_full_name` (`combined_username`);

ALTER TABLE `authors`
  ADD PRIMARY KEY (`author_id`),
  ADD KEY `idx_author_name` (`author_name`);

ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `idx_title` (`title`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_author` (`author_id`),
  ADD KEY `idx_book_category_available` (`category_id`,`available_copies`);

ALTER TABLE `borrowing_records`
  ADD PRIMARY KEY (`borrowing_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_book` (`book_id`),
  ADD KEY `idx_book_status_id` (`book_status_id`),
  ADD KEY `idx_due_date` (`due_date`),
  ADD KEY `idx_borrowing_user_status` (`user_id`,`book_status_id`);

ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

ALTER TABLE `fines`
  ADD PRIMARY KEY (`fine_id`),
  ADD KEY `borrowing_id` (`borrowing_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`payment_status`);

ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_book` (`book_id`),
  ADD KEY `idx_status` (`status`);

ALTER TABLE `ratings`
  ADD PRIMARY KEY (`book_id`),
  ADD KEY `idx_rating` (`rating`);


--
-- AUTO_INCREMENT for tables
--
ALTER TABLE `book_statuses`
  MODIFY `book_status_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `roles`
  MODIFY `role_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `authors`
  MODIFY `author_id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `books`
  MODIFY `book_id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `borrowing_records`
  MODIFY `borrowing_id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `categories`
  MODIFY `category_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

ALTER TABLE `fines`
  MODIFY `fine_id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `reservations`
  MODIFY `reservation_id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;


--
-- Constraints for tables
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE RESTRICT;

ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `authors` (`author_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `books_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

ALTER TABLE `borrowing_records`
  ADD CONSTRAINT `borrowing_records_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrowing_records_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrowing_records_ibfk_3` FOREIGN KEY (`book_status_id`) REFERENCES `book_statuses` (`book_status_id`) ON DELETE RESTRICT;

ALTER TABLE `fines`
  ADD CONSTRAINT `fines_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fines_ibfk_2` FOREIGN KEY (`borrowing_id`) REFERENCES `borrowing_records` (`borrowing_id`) ON DELETE SET NULL;

ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE;

ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

--
-- TRUNCATE all tables to ensure clean state for explicit ID inserts
-- (Requires disabling foreign key checks temporarily)
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `roles`;
TRUNCATE TABLE `book_statuses`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `authors`;
TRUNCATE TABLE `categories`;
TRUNCATE TABLE `books`;
TRUNCATE TABLE `borrowing_records`;
TRUNCATE TABLE `fines`;
TRUNCATE TABLE `reservations`;
TRUNCATE TABLE `ratings`;
SET FOREIGN_KEY_CHECKS = 1;


--
-- Table data for `roles`
--
INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'Administrator'),
(2, 'Librarian'),
(3, 'Member');

--
-- Table data for `book_statuses`
--
INSERT INTO `book_statuses` (`book_status_id`, `status_name`) VALUES
(1, 'Currently Borrowed'),
(2, 'Returned'),
(3, 'Overdue'),
(4, 'Lost');


--
-- Table data for `users` (22 Users)
-- (Password is: firstname@123)
--
-- INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone_number`, `role_id`) VALUES
-- (1, 'Alice', 'Smith', 'alice.s@10minutemail.com', 'Alice@123', '0715550101', 3),
-- (2, 'Bob', 'Johnson', 'bob.j@example.com', 'Bob@123', '555-0102', 3),
-- (3, 'Charlie', 'Brown', 'charlie.b@example.com', 'Charlie@123', '555-0103', 3),
-- (4, 'Diana', 'Prince', 'diana.p@example.com', 'Diana@123', '555-0104', 3),
-- (5, 'Eve', 'Adams', 'eve.a@example.com', 'Eve@123', '555-0105', 3),
-- (6, 'Frank', 'Miller', 'frank.m@example.com', 'Frank@123', '555-0106', 3),
-- (7, 'Grace', 'Lee', 'grace.l@example.com', 'Grace@123', '555-0107', 3),
-- (8, 'Henry', 'Wilson', 'henry.w@example.com', 'Henry@123', '555-0108', 3),
-- (9, 'Ivy', 'King', 'ivy.k@example.com', 'Ivy@123', '555-0109', 3),
-- (10, 'Jack', 'Harris', 'jack.h@example.com', 'Jack@123', '555-0110', 3),
-- (11, 'Kara', 'Danvers', 'kara.d@example.com', 'Kara@123', '555-0111', 3),
-- (12, 'Liam', 'Nielsen', 'liam.n@example.com', 'Liam@123', '555-0112', 3),
-- (13, 'Mia', 'Clark', 'mia.c@example.com', 'Mia@123', '555-0113', 3),
-- (14, 'Noah', 'Taylor', 'noah.t@example.com', 'Noah@123', '555-0114', 3),
-- (15, 'Olivia', 'Baker', 'olivia.b@example.com', 'Olivia@123', '555-0115', 3),
-- (16, 'Peter', 'Scott', 'peter.s@example.com', 'Peter@123', '555-0116', 3),
-- (17, 'Quinn', 'Allen', 'quinn.a@example.com', 'Quinn@123', '555-0117', 3),
-- (18, 'Ryan', 'Hall', 'ryan.h@example.com', 'Ryan@123', '555-0118', 3),
-- (19, 'Susan', 'Wright', 'susan.w@example.com', 'Susan@123', '555-0119', 3),
-- (20, 'Tom', 'Young', 'tom.y@example.com', 'Tom@123', '555-0120', 3),
-- (21, 'Admin', 'User', 'admin@library.com', 'Admin@123', '555-0901', 1),
-- (22, 'Lilly', 'Mason', 'lilly.m@10minutemail.com', 'Lilly@123', '0725550902', 2);


--
-- Table data for `authors` (10 Authors)
--
INSERT INTO `authors` (`author_id`, `author_name`, `biography`) VALUES
(1, 'Isaac Asimov', 'A prolific writer of science fiction, popularized the Three Laws of Robotics.'),
(2, 'Jane Austen', 'Known primarily for her six major novels, which interpret, critique, and comment upon the British landed gentry at the end of the 18th century.'),
(3, 'Gabriel Garcia Marquez', 'A Colombian novelist, journalist, short-story writer, screenwriter, and literary provocateur.'),
(4, 'Toni Morrison', 'An American novelist, essayist, book editor, and college professor. Her first novel, The Bluest Eye, was published in 1970.'),
(5, 'Neil Gaiman', 'An English author of short fiction, novels, comic books, graphic novels, nonfiction, audio theatre, and film.'),
(6, 'J.R.R. Tolkien', 'An English writer, poet, philologist, and academic. Author of The Hobbit and The Lord of the Rings.'),
(7, 'Agatha Christie', 'An English writer, known for her 66 detective novels and 14 short story collections.'),
(8, 'George Orwell', 'An English novelist, essayist, journalist, and critic. He wrote the allegorical novella Animal Farm.'),
(9, 'Virginia Woolf', 'Considered one of the most important modernist 20th-century authors.'),
(10, 'Stephen King', 'An American author of horror, supernatural fiction, suspense, crime, science-fiction, and fantasy novels.');


--
-- Table data for `categories` (8 Categories)
--
INSERT INTO `categories` (`category_id`, `category_name`, `description`) VALUES
(1, 'Science Fiction', 'Books exploring imaginative and futuristic concepts.'),
(2, 'Classic Literature', 'Time-tested works of historical and artistic value.'),
(3, 'Fantasy', 'Stories set in imaginary worlds, often involving magic or supernatural events.'),
(4, 'Mystery', 'Fiction dealing with the solution of a mystery or crime.'),
(5, 'Non-Fiction', 'Factual and informative writing.'),
(6, 'Thriller', 'Fiction designed to elicit strong feelings of excitement, anxiety, or suspense.'),
(7, 'Historical Fiction', 'Fiction based on historical events or figures.'),
(8, 'Poetry', 'Verse and rhythmic writing.');


--
-- Table data for `books` (40 Books)
--
INSERT INTO `books` (`book_id`, `isbn`, `title`, `author_id`, `publisher`, `published_date`, `page_count`, `category_id`, `language`, `total_copies`, `available_copies`, `shelf_location`, `book_condition`) VALUES
(1, '978-0553213993', 'Foundation', 1, 'Bantam Books', '1951-05-01', 255, 1, 'en', 5, 4, 'SF-A1', 'excellent'),
(2, '978-0141439518', 'Pride and Prejudice', 2, 'Penguin Classics', '1813-01-28', 279, 2, 'en', 3, 2, 'CL-B2', 'good'),
(3, '978-0061120084', 'One Hundred Years of Solitude', 3, 'Harper Perennial', '1967-05-30', 417, 7, 'es', 4, 3, 'HF-C3', 'good'),
(4, '978-1400033423', 'Beloved', 4, 'Vintage', '1987-09-02', 324, 2, 'en', 2, 1, 'CL-D4', 'excellent'),
(5, '978-0380802778', 'American Gods', 5, 'William Morrow', '2001-06-19', 592, 3, 'en', 3, 3, 'FN-E5', 'excellent'),
(6, '978-0618260271', 'The Fellowship of the Ring', 6, 'Houghton Mifflin', '1954-07-29', 423, 3, 'en', 5, 5, 'FN-A6', 'excellent'),
(7, '978-0007119318', 'And Then There Were None', 7, 'HarperCollins', '1939-11-06', 264, 4, 'en', 4, 3, 'MY-B7', 'good'),
(8, '978-0451524935', '1984', 8, 'Signet Classic', '1949-06-08', 328, 1, 'en', 6, 5, 'SF-C8', 'excellent'),
(9, '978-0156030431', 'Mrs. Dalloway', 9, 'Harvest Book', '1925-05-14', 216, 2, 'en', 2, 2, 'CL-D9', 'fair'),
(10, '978-1501142970', 'It', 10, 'Scribner', '1986-09-15', 1138, 6, 'en', 3, 2, 'TR-E1', 'good'),
(11, '978-0553382569', 'The Hitchhiker\'s Guide to the Galaxy', 1, 'Del Rey', '1979-10-12', 216, 1, 'en', 5, 5, 'SF-A2', 'excellent'),
(12, '978-0486280453', 'Sense and Sensibility', 2, 'Dover Publications', '1811-10-30', 360, 2, 'en', 3, 3, 'CL-B3', 'good'),
(13, '978-0140182651', 'Chronicle of a Death Foretold', 3, 'Penguin', '1981-01-01', 120, 7, 'es', 2, 1, 'HF-C4', 'good'),
(14, '978-0679764031', 'Sula', 4, 'Vintage', '1973-01-01', 174, 2, 'en', 2, 2, 'CL-D5', 'excellent'),
(15, '978-0062464169', 'Norse Mythology', 5, 'W. W. Norton & Company', '2017-02-07', 304, 5, 'en', 4, 4, 'NF-E6', 'excellent'),
(16, '978-0345339683', 'The Two Towers', 6, 'Del Rey', '1954-11-11', 352, 3, 'en', 5, 4, 'FN-A7', 'good'),
(17, '978-0062073488', 'The ABC Murders', 7, 'William Morrow', '1936-01-06', 256, 4, 'en', 3, 3, 'MY-B8', 'excellent'),
(18, '978-0451526342', 'Animal Farm', 8, 'Signet Classic', '1945-08-17', 144, 2, 'en', 6, 5, 'CL-C9', 'excellent'),
(19, '978-0156029053', 'A Room of One\'s Own', 9, 'Harvest Book', '1929-10-24', 112, 5, 'en', 2, 1, 'NF-D1', 'fair'),
(20, '978-0451169528', 'The Shining', 10, 'Anchor', '1977-01-28', 447, 6, 'en', 4, 3, 'TR-E2', 'good'),
(21, '978-0553293353', 'I, Robot', 1, 'Bantam Books', '1950-12-02', 253, 1, 'en', 3, 2, 'SF-A3', 'excellent'),
(22, '978-0141439587', 'Emma', 2, 'Penguin Classics', '1815-12-01', 475, 2, 'en', 3, 3, 'CL-B4', 'excellent'),
(23, '978-0307474474', 'Love in the Time of Cholera', 3, 'Vintage', '1985-01-01', 348, 7, 'es', 3, 2, 'HF-C5', 'good'),
(24, '978-0307278278', 'Song of Solomon', 4, 'Vintage', '1977-01-01', 337, 2, 'en', 2, 1, 'CL-D6', 'excellent'),
(25, '978-0743201199', 'Stardust', 5, 'Avon', '1999-07-28', 272, 3, 'en', 4, 4, 'FN-E7', 'excellent'),
(26, '978-0618260301', 'The Return of the King', 6, 'Houghton Mifflin', '1955-10-20', 416, 3, 'en', 5, 5, 'FN-A8', 'excellent'),
(27, '978-0062073556', 'The Murder of Roger Ackroyd', 7, 'William Morrow', '1926-06-01', 288, 4, 'en', 3, 2, 'MY-B9', 'good'),
(28, '978-0451524676', 'Burmese Days', 8, 'Signet Classic', '1934-01-01', 288, 7, 'en', 2, 2, 'HF-C1', 'fair'),
(29, '978-0156030271', 'To the Lighthouse', 9, 'Harvest Book', '1927-05-05', 209, 2, 'en', 2, 2, 'CL-D2', 'good'),
(30, '978-0345370792', 'The Stand', 10, 'Anchor', '1978-01-01', 1152, 6, 'en', 3, 2, 'TR-E3', 'excellent'),
(31, '978-0553801477', 'The Martian Chronicles', 1, 'Bantam Books', '1950-01-01', 181, 1, 'en', 2, 1, 'SF-A4', 'good'),
(32, '978-0141439709', 'Mansfield Park', 2, 'Penguin Classics', '1814-01-01', 480, 2, 'en', 3, 3, 'CL-B5', 'excellent'),
(33, '978-0307474450', 'No One Writes to the Colonel', 3, 'Vintage', '1961-01-01', 148, 7, 'es', 2, 2, 'HF-C6', 'fair'),
(34, '978-0345476395', 'Tar Baby', 4, 'Vintage', '1981-01-01', 305, 2, 'en', 2, 2, 'CL-D7', 'good'),
(35, '978-0060951185', 'Neverwhere', 5, 'Avon', '1996-01-01', 370, 3, 'en', 3, 3, 'FN-E8', 'excellent'),
(36, '978-0345340429', 'The Hobbit', 6, 'Del Rey', '1937-09-21', 310, 3, 'en', 5, 4, 'FN-A9', 'excellent'),
(37, '978-0062073501', 'Death on the Nile', 7, 'William Morrow', '1937-11-01', 373, 4, 'en', 3, 3, 'MY-B1', 'good'),
(38, '978-0140449141', 'Homage to Catalonia', 8, 'Penguin Classics', '1938-04-25', 248, 5, 'en', 2, 1, 'NF-C2', 'excellent'),
(39, '978-0156029060', 'Orlando', 9, 'Harvest Book', '1928-10-11', 333, 2, 'en', 2, 2, 'CL-D3', 'fair'),
(40, '978-0451419736', 'Misery', 10, 'Pocket Books', '1987-06-08', 370, 6, 'en', 4, 3, 'TR-E4', 'good');


--
-- Table data for `borrowing_records`
--
INSERT INTO `borrowing_records` (`borrowing_id`, `user_id`, `book_id`, `issue_date`, `due_date`, `return_date`, `book_status_id`) VALUES
(1, 1, 1, '2025-09-01 10:00:00', '2025-09-15', NULL, 1),
(2, 2, 2, '2025-10-01 14:30:00', '2025-10-15', NULL, 1),
(3, 3, 4, '2025-09-20 11:00:00', '2025-10-04', NULL, 1),
(4, 4, 10, '2025-09-25 12:00:00', '2025-10-09', NULL, 1),
(5, 5, 13, '2025-10-10 16:00:00', '2025-10-24', NULL, 1),
(6, 6, 19, '2025-10-10 09:00:00', '2025-10-24', NULL, 1),
(7, 7, 21, '2025-08-20 13:00:00', '2025-09-03', NULL, 1),
(8, 8, 24, '2025-08-01 11:00:00', '2025-08-15', NULL, 3), -- Overdue
(9, 9, 27, '2025-09-10 15:00:00', '2025-09-24', NULL, 3), -- Overdue
(10, 10, 6, '2025-07-01 09:00:00', '2025-07-15', '2025-07-14', 2),
(11, 11, 7, '2025-07-05 10:00:00', '2025-07-19', '2025-07-25', 2), -- Late, Fine ID 1
(12, 12, 11, '2025-08-15 11:00:00', '2025-08-29', '2025-08-30', 2), -- Late, Fine ID 2
(13, 13, 16, '2025-09-05 12:00:00', '2025-09-19', '2025-09-19', 2),
(14, 14, 20, '2025-09-10 13:00:00', '2025-09-24', '2025-10-01', 2), -- Late, Fine ID 3
(15, 15, 30, '2025-06-01 14:00:00', '2025-06-15', '2025-06-15', 2);


--
-- Table data for `fines`
--
INSERT INTO `fines` (`fine_id`, `user_id`, `borrowing_id`, `fine_amount`, `fine_reason`, `payment_status`) VALUES
(1, 11, 11, 60.00, 'Overdue return (6 days)', 'paid'),
(2, 12, 12, 10.00, 'Overdue return (1 day)', 'unpaid'),
(3, 14, 14, 70.00, 'Overdue return (7 days)', 'unpaid'),
(4, 8, 8, 610.00, 'Overdue borrowing (Calculated at 61 days)', 'unpaid'), -- 61 days overdue as of 2025-10-15
(5, 9, 9, 210.00, 'Overdue borrowing (Calculated at 21 days)', 'unpaid'); -- 21 days overdue as of 2025-10-15


--
-- Table data for `reservations`
--
INSERT INTO `reservations` (`reservation_id`, `user_id`, `book_id`, `reservation_date`, `expiry_date`, `status`) VALUES
(1, 16, 1, '2025-10-10 10:00:00', '2025-10-17 10:00:00', 'active'),
(2, 17, 4, '2025-10-05 11:00:00', '2025-10-12 11:00:00', 'expired'),
(3, 18, 38, '2025-10-01 12:00:00', '2025-10-08 12:00:00', 'fulfilled'),
(4, 19, 31, '2025-09-20 13:00:00', '2025-09-27 13:00:00', 'cancelled'),
(5, 20, 2, '2025-10-14 14:00:00', '2025-10-21 14:00:00', 'active');


--
-- Table data for `ratings`
--
INSERT INTO `ratings` (`book_id`, `rating`, `rating_count`) VALUES
(1, 4.5, 1500),
(2, 4.2, 2100),
(3, 4.7, 950),
(6, 4.8, 5000),
(8, 4.6, 3200),
(10, 4.0, 1800),
(15, 4.3, 500),
(18, 4.4, 1200),
(20, 4.1, 900),
(26, 4.8, 4800);
