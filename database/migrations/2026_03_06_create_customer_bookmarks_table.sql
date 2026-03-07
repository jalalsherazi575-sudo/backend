-- Migration for Customer Bookmarks Table
-- Run this SQL on your OVH database

CREATE TABLE IF NOT EXISTS `customer_bookmarks` (
  `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `cust_id` INT(11) NOT NULL,
  `question_id` INT(11) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_customer_question` (`cust_id`, `question_id`),
  INDEX `idx_cust_id` (`cust_id`),
  INDEX `idx_question_id` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
