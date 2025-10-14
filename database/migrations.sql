-- storysphere_db Migration File (Schema with Status FK - Final)
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
  `status_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Stored Procedures (Modified to use Status IDs)
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
  `role_name` varchar(50) NOT NULL
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
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `authors`
--
CREATE TABLE `authors` (
  `author_id` int NOT NULL,
  `author_name` varchar(100) NOT NULL,
  `biography` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--
CREATE TABLE `categories` (
  `category_id` int NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
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
  `book_condition` enum('excellent','good','fair','poor') DEFAULT 'good'
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
  `book_status_id` int DEFAULT 1 -- Foreign Key to book_statuses
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ratings` (Simplified for Google Books API Rating)
--
CREATE TABLE `ratings` (
  `book_id` int NOT NULL,
  `rating` decimal(2,1) DEFAULT NULL,
  `rating_count` int DEFAULT 0
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
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
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
  `status` enum('active','fulfilled','cancelled','expired') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Indexes for tables
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