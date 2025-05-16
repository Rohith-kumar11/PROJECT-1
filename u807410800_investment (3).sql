-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 23, 2024 at 02:22 AM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u807410800_investment`
--

-- --------------------------------------------------------

--
-- Table structure for table `investments`
--

DROP TABLE IF EXISTS `investments`;
CREATE TABLE IF NOT EXISTS `investments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `investor_id` int NOT NULL,
  `startup_id` int NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` enum('pending','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_investor` (`investor_id`),
  KEY `idx_startup` (`startup_id`),
  KEY `idx_status_date` (`status`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `investor_unlocks`
--

DROP TABLE IF EXISTS `investor_unlocks`;
CREATE TABLE IF NOT EXISTS `investor_unlocks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `entrepreneur_id` int NOT NULL,
  `page_number` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','completed','failed') DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_unlock` (`entrepreneur_id`,`page_number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `message` text NOT NULL,
  `read_status` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `startup_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sender` (`sender_id`),
  KEY `idx_receiver` (`receiver_id`),
  KEY `idx_startup` (`startup_id`)
) ENGINE=MyISAM AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `read_status`, `created_at`, `startup_id`) VALUES
(1, 1, 2, 'Hello, I\'m interested in your startup.', 0, '2024-11-20 01:48:03', 0),
(2, 2, 1, 'Thank you for your interest! What would you like to know?', 0, '2024-11-20 01:48:03', 0),
(3, 1, 2, 'Could you tell me more about your revenue model?', 0, '2024-11-20 01:48:03', 0),
(4, 3, 9, 'Hi, I\'m interested in your startup. Let\'s discuss potential investment opportunities.', 0, '2024-11-20 02:31:49', 13),
(5, 3, 9, 'hi', 0, '2024-11-20 02:31:59', 0),
(6, 3, 2, 'Hi, I\'m interested in your startup. Let\'s discuss potential investment opportunities.', 0, '2024-11-20 15:26:17', 8),
(7, 3, 7, 'Hi, I\'m interested in your startup. Let\'s discuss potential investment opportunities.', 0, '2024-12-21 17:10:34', 11),
(8, 3, 9, 'yo wassup', 0, '2024-12-21 17:11:04', 0),
(9, 3, 9, 'can u replay to em', 0, '2024-12-21 17:11:17', 0),
(10, 3, 9, '????????????? yooo', 0, '2024-12-21 17:11:21', 0),
(11, 3, 9, 'df', 0, '2024-12-21 17:11:22', 0),
(12, 3, 9, 'dfs', 0, '2024-12-21 17:11:22', 0),
(13, 3, 9, 'sdf', 0, '2024-12-21 17:11:22', 0),
(14, 3, 9, 'sdf', 0, '2024-12-21 17:11:22', 0),
(15, 3, 9, 'sdf', 0, '2024-12-21 17:11:22', 0),
(16, 3, 9, 'sdf', 0, '2024-12-21 17:11:23', 0),
(17, 3, 9, 'dsf', 0, '2024-12-21 17:11:23', 0),
(18, 3, 9, 'sdds', 0, '2024-12-21 17:11:24', 0),
(19, 3, 9, 'ds', 0, '2024-12-21 17:11:24', 0),
(20, 3, 9, 'sssdss', 0, '2024-12-21 17:11:25', 0),
(21, 3, 9, 'heyt there', 0, '2024-12-22 06:47:21', 0),
(22, 3, 9, 'hi', 0, '2024-12-22 06:57:34', 0),
(23, 1, 2, 'hello there', 0, '2024-12-22 10:22:17', 1),
(24, 3, 6, 'Hi, I\'m interested in your startup. Let\'s discuss potential investment opportunities.', 0, '2024-12-22 10:33:25', 10),
(25, 3, 2, 'hey there', 0, '2024-12-22 10:43:45', 0),
(26, 3, 9, 'not working', 0, '2024-12-22 10:43:56', 0),
(27, 3, 9, 'upp what', 0, '2024-12-22 10:47:43', 0),
(28, 3, 9, 'asdasda', 0, '2024-12-22 10:51:23', 0),
(29, 3, 11, 'Hi, I\'m interested in your startup. Let\'s discuss potential investment opportunities.', 1, '2024-12-22 10:55:47', 15),
(30, 3, 11, 'hey there whats up', 1, '2024-12-22 10:55:58', 0),
(31, 11, 3, 'nice to meet you sir whats up kita rokhada milega', 1, '2024-12-22 10:58:29', 0),
(32, 3, 11, 'sadasd', 1, '2024-12-22 11:03:34', 0),
(33, 3, 11, 'hey ther', 1, '2024-12-22 11:34:00', 0),
(34, 11, 3, 'nice meeting you sir', 1, '2024-12-22 11:34:25', 0),
(35, 11, 3, 'jvbAfasdhnlkASd', 1, '2024-12-22 11:34:27', 0),
(36, 3, 11, 'so whats up', 1, '2024-12-22 11:35:45', 0),
(37, 3, 11, 'nice meeting you broo', 1, '2024-12-22 11:35:53', 0),
(38, 3, 11, 'take 100000000000000 rs moni', 1, '2024-12-22 11:35:59', 0),
(39, 3, 11, 'real time?', 1, '2024-12-22 11:38:50', 0),
(40, 11, 3, 'sounds like not', 1, '2024-12-22 11:39:04', 0),
(41, 3, 11, 'lets check now', 1, '2024-12-22 11:40:07', 0),
(42, 3, 11, 'still the same i guess', 1, '2024-12-22 11:40:16', 0),
(43, 3, 11, 'ets check now', 1, '2024-12-22 11:42:53', 0),
(44, 3, 11, 'umm still the same i guess', 1, '2024-12-22 11:43:03', 0),
(45, 3, 11, 'entrar', 1, '2024-12-22 11:44:41', 0),
(46, 3, 11, 'sucks', 1, '2024-12-22 11:44:43', 0),
(47, 11, 3, 'entrrar sucks pro max', 1, '2024-12-22 11:44:55', 0),
(48, 3, 11, 'so what bnow', 1, '2024-12-22 11:46:51', 0),
(49, 11, 3, 'you tell me', 1, '2024-12-22 11:47:01', 0),
(50, 3, 11, 'ok', 1, '2024-12-22 11:47:08', 0),
(51, 3, 11, 'now this feel like realtimeee', 1, '2024-12-22 11:47:14', 0),
(52, 3, 11, 'yaaaaaaaaaaaay', 1, '2024-12-22 11:47:18', 0),
(53, 11, 3, 'oh my gayyyyyyyydd', 1, '2024-12-22 11:47:30', 0),
(54, 11, 3, 'this works real time wit', 1, '2024-12-22 11:47:43', 0),
(55, 11, 3, 'finally we enabled real time chat s fuck this was titing', 1, '2024-12-22 11:48:44', 0),
(56, 11, 3, 'ehehehe', 1, '2024-12-22 11:48:51', 0),
(57, 3, 11, 'rupesss 10000000000000', 1, '2024-12-22 11:49:02', 0),
(58, 3, 11, 'wonnnnnnnn crazyy investment u got brooo', 1, '2024-12-22 11:49:11', 0),
(59, 3, 11, 'i paying u rn', 1, '2024-12-22 11:49:14', 0),
(60, 3, 11, 'moniuii', 1, '2024-12-22 11:49:17', 0),
(61, 3, 11, 'ehehe', 1, '2024-12-22 11:49:18', 0),
(62, 11, 3, 'api is too good', 1, '2024-12-22 11:49:44', 0),
(63, 11, 3, 'lesss goooooooooooooooooo!', 1, '2024-12-22 11:49:48', 0),
(64, 11, 3, 'make this bs real timw without websockets 📈📈📈📈📈', 1, '2024-12-22 11:50:04', 0),
(65, 3, 11, 'a little more mods in profile we gotta do', 1, '2024-12-22 11:51:54', 0),
(66, 11, 3, 'lessgooooooo', 1, '2024-12-22 11:51:59', 0),
(67, 3, 11, '#nowebsocketssssssss', 1, '2024-12-22 11:52:19', 0),
(68, 11, 3, '#nowebsocketssssssss🔌🔌🔌🔌', 1, '2024-12-22 11:52:33', 0),
(69, 11, 3, 'yo', 1, '2024-12-23 02:21:10', 0),
(70, 11, 3, 'lessogooo', 1, '2024-12-23 02:21:17', 0),
(71, 3, 11, 'nice work', 1, '2024-12-23 02:21:38', 0),
(72, 11, 3, 'claude is the best!', 1, '2024-12-23 02:21:44', 0);

-- --------------------------------------------------------

--
-- Table structure for table `messages_backup`
--

DROP TABLE IF EXISTS `messages_backup`;
CREATE TABLE IF NOT EXISTS `messages_backup` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `read_status` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `startup_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sender` (`sender_id`),
  KEY `idx_receiver` (`receiver_id`),
  KEY `idx_conversation` (`sender_id`,`receiver_id`),
  KEY `idx_read_status` (`read_status`),
  KEY `startup_id` (`startup_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saved_startups`
--

DROP TABLE IF EXISTS `saved_startups`;
CREATE TABLE IF NOT EXISTS `saved_startups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `investor_id` int NOT NULL,
  `startup_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_save` (`investor_id`,`startup_id`),
  KEY `startup_id` (`startup_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `session_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_session_token` (`session_token`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `startups`
--

DROP TABLE IF EXISTS `startups`;
CREATE TABLE IF NOT EXISTS `startups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `entrepreneur_id` int NOT NULL,
  `company_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `funding_stage` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `raised_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `categories` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `team_size` int DEFAULT NULL,
  `founded_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `entrepreneur_id` (`entrepreneur_id`),
  KEY `idx_funding_stage` (`funding_stage`),
  KEY `idx_categories` (`categories`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `startups`
--

INSERT INTO `startups` (`id`, `entrepreneur_id`, `company_name`, `short_description`, `full_description`, `funding_stage`, `target_amount`, `raised_amount`, `categories`, `location`, `website`, `team_size`, `founded_date`, `created_at`, `updated_at`) VALUES
(4, 3, 'EcoTech Solutions', 'Revolutionizing waste management through AI-powered recycling solutions', 'Revolutionizing waste management through AI-powered recycling solutionsRevolutionizing waste management through AI-powered recycling solutions', 'Seed', 5000000.00, 2000000.00, 'CleanTech,AI,Sustainability', 'Bengalore', 'logiclaunch.in', 12, '2024-11-11', '2024-11-17 19:15:44', '2024-11-17 19:17:24'),
(15, 11, 'Websoft Technologies', 'Making it solutions and webapps ', 'Making it solutions and webapps Making it solutions and webapps Making it solutions and webapps Making it solutions and webapps Making it solutions and webapps Making it solutions and webapps ', 'Series B', 78923454.00, 0.00, 'ai, research, machinelearning, tech', 'Bangalore/Bengaluru', 'www.logiclaunch.in', 234, NULL, '2024-12-22 10:55:23', '2024-12-22 10:55:23');

-- --------------------------------------------------------

--
-- Table structure for table `startup_images`
--

DROP TABLE IF EXISTS `startup_images`;
CREATE TABLE IF NOT EXISTS `startup_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `startup_id` int NOT NULL,
  `image_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `startup_id` (`startup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `entrepreneur_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `entrepreneur_id` (`entrepreneur_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_type` enum('investor','entrepreneur') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `profile_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bio` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `location` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `linkedin_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `investment_focus` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `investment_range_min` decimal(15,2) DEFAULT NULL,
  `investment_range_max` decimal(15,2) DEFAULT NULL,
  `investment_stage_preference` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `industry_preference` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_user_type` (`user_type`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `email`, `password`, `user_type`, `profile_image`, `bio`, `created_at`, `updated_at`, `phone`, `location`, `linkedin_url`, `investment_focus`, `investment_range_min`, `investment_range_max`, `investment_stage_preference`, `industry_preference`) VALUES
(3, 'Siddhant Sundar', 'investor', 'siiidddexe@gmail.com', '$2y$10$Xo7E2AHccb7wecnZBkBnAO.vF5uhqgeVvZ5Zq25nWQ3s1pbWHVHTK', 'investor', 'uploads/67680a98bf884.jpg', 'Tech entrepreneur with 10 years experience', '2024-11-18 08:13:22', '2024-12-23 01:27:49', '85490131144', 'Bengaluru', 'google.com', 'Tech and ai', 123432.00, 5342783.00, 'Seed', 'All'),
(11, 'Siddhant Sundar', 'userloginsure', 'userloginsure@gmail.com', '$2y$10$56co1oxpLEci4IQ5Wn8Btua9E1yhKLqimVxheCLmQqIufOPumrvsO', 'entrepreneur', 'uploads/6767fd05c2a26.jpg', 'userloginsure@gmail.comuserloginsure@gmail.comtechtechtechBangalore/BengaluruBangalore/Bengalururesearch', '2024-12-22 10:54:48', '2024-12-23 01:28:27', '08549013115', 'Bangalore/Bengaluru', 'https://google.com', 'research', 24554.00, 43234345.00, 'Early Traction', 'tech');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `startups`
--
ALTER TABLE `startups` ADD FULLTEXT KEY `idx_search` (`company_name`,`short_description`,`categories`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `investments`
--
ALTER TABLE `investments`
  ADD CONSTRAINT `investments_ibfk_1` FOREIGN KEY (`investor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `investments_ibfk_2` FOREIGN KEY (`startup_id`) REFERENCES `startups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages_backup`
--
ALTER TABLE `messages_backup`
  ADD CONSTRAINT `messages_backup_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_backup_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_backup_ibfk_3` FOREIGN KEY (`startup_id`) REFERENCES `startups` (`id`);

--
-- Constraints for table `saved_startups`
--
ALTER TABLE `saved_startups`
  ADD CONSTRAINT `saved_startups_ibfk_1` FOREIGN KEY (`investor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_startups_ibfk_2` FOREIGN KEY (`startup_id`) REFERENCES `startups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `startup_images`
--
ALTER TABLE `startup_images`
  ADD CONSTRAINT `startup_images_ibfk_1` FOREIGN KEY (`startup_id`) REFERENCES `startups` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
