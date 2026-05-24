-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 20, 2026 at 12:24 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `flashpoint`
--

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `source` varchar(50) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `verification_status` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `title`, `content`, `url`, `source`, `created_by`, `status`, `verified_by`, `verification_status`, `created_at`, `updated_at`) VALUES
(48, 'Mdina invaded by Hamrun', 'The hamrunizi have taken the sudden pastizzi conflict towards mdina as the mdina people deemed the pastizzi tal pizzelli the worse pastizz and the people of hamrun did not let that stand', NULL, NULL, 1, 'pending', NULL, 'unverified', '2026-05-04 21:47:47', '2026-05-04 21:47:47'),
(49, '', '', NULL, NULL, 1, 'pending', NULL, 'unverified', '2026-05-04 22:25:28', '2026-05-04 22:25:28'),
(50, 'Birkirkara get taken over by zrieraq', 'ASJDGFSIEUGFUIWEHFUK', NULL, NULL, 1, 'pending', NULL, 'unverified', '2026-05-05 12:39:42', '2026-05-05 12:39:42'),
(51, 'ASDFGHYUJIKO', 'ASDFGHJKL;KJHGFDSJKL', NULL, NULL, 1, 'pending', NULL, 'unverified', '2026-05-05 12:41:09', '2026-05-05 12:41:09');

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

CREATE TABLE `bookmarks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `article_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookmarks`
--

INSERT INTO `bookmarks` (`id`, `user_id`, `article_id`, `created_at`) VALUES
(1, 4, 48, '2026-05-19 15:42:01'),
(4, 4, 50, '2026-05-19 15:42:53'),
(6, 5, 48, '2026-05-19 17:23:05');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `importance` varchar(20) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `event_time` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `article_id` int(11) DEFAULT NULL,
  `sponsor_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `in_app_purchases`
--

CREATE TABLE `in_app_purchases` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `membership_id` int(11) DEFAULT NULL,
  `purchase_date` datetime DEFAULT current_timestamp(),
  `expiry_date` datetime DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `media_type` varchar(20) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp(),
  `verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `memberships`
--

CREATE TABLE `memberships` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tier_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `memberships`
--

INSERT INTO `memberships` (`id`, `user_id`, `tier_id`) VALUES
(2, 4, 2),
(3, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `membershiptiers`
--

CREATE TABLE `membershiptiers` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `price_eur` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `can_remove_ads` tinyint(1) DEFAULT NULL,
  `can_upload_media` tinyint(1) DEFAULT NULL,
  `can_post_news` tinyint(1) DEFAULT NULL,
  `can_access_vacancies` tinyint(1) DEFAULT NULL,
  `can_receive_fast_notifications` tinyint(1) DEFAULT NULL,
  `can_get_discounts` tinyint(1) DEFAULT NULL,
  `can_bookmark` tinyint(1) DEFAULT NULL,
  `can_comment` tinyint(1) DEFAULT NULL,
  `can_react` tinyint(1) DEFAULT NULL,
  `can_view_videos_early` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `membershiptiers`
--

INSERT INTO `membershiptiers` (`id`, `name`, `price_eur`, `description`, `can_remove_ads`, `can_upload_media`, `can_post_news`, `can_access_vacancies`, `can_receive_fast_notifications`, `can_get_discounts`, `can_bookmark`, `can_comment`, `can_react`, `can_view_videos_early`) VALUES
(1, 'Basic', 0.00, NULL, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(2, 'Premium', 5.99, 'Unlimited comments & media · No ads · Save articles', 1, 1, 0, 1, 0, 1, 1, 1, 1, 0),
(3, 'Journalism', 12.99, 'Everything in Premium · Post articles · Custom journalist profile · Verified badge', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `qualifications`
--

CREATE TABLE `qualifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `document_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `submitted_at` datetime DEFAULT current_timestamp(),
  `verified_by` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reactions`
--

CREATE TABLE `reactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `article_id` int(11) DEFAULT NULL,
  `reaction_type` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sponsors`
--

CREATE TABLE `sponsors` (
  `id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `website_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `contact_email` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `display_name` varchar(150) DEFAULT NULL,
  `profile_photo_url` varchar(255) DEFAULT NULL,
  `user_type` varchar(20) DEFAULT NULL,
  `membership_id` int(11) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `display_name`, `profile_photo_url`, `user_type`, `membership_id`, `is_verified`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 'yan_cesare', 'cesarey2004@gmail.com', 'yan345678', 'Yan', NULL, NULL, 3, 0, '2026-05-04 19:46:20', '2026-05-05 16:22:04', 0),
(4, 'jason_cesare', 'jsn@flashpoint.mt', '$2y$10$ogsdDuD3l1Vbk.BBTB/u5OiKb.7VVtc6/Yeq5RJi37iHJgTOziIne', 'Jason Cesare', NULL, 'general', 2, 0, '2026-05-19 13:27:12', '2026-05-19 22:10:35', 0),
(5, 'kayden_cesare', 'kyn@flashpoint.mt', '$2y$10$xjEtoVLMevVGiyyWioF1.eWCF.G3LwosEZgT8puO6vwCkGHzMbB06', 'Kayden Cesare', NULL, 'general', 3, 0, '2026-05-19 13:56:32', '2026-05-19 13:57:21', 0);

-- --------------------------------------------------------

--
-- Table structure for table `vacancies`
--

CREATE TABLE `vacancies` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `company` varchar(150) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `posted_by` int(11) DEFAULT NULL,
  `posted_at` datetime DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verifications`
--

CREATE TABLE `verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `verifier_id` int(11) DEFAULT NULL,
  `verification_type` varchar(20) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `verified_by` (`verified_by`);

--
-- Indexes for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`article_id`) USING BTREE,
  ADD KEY `article_id` (`article_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `sponsor_id` (`sponsor_id`);

--
-- Indexes for table `in_app_purchases`
--
ALTER TABLE `in_app_purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `membership_id` (`membership_id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `verified_by` (`verified_by`);

--
-- Indexes for table `memberships`
--
ALTER TABLE `memberships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tier_id` (`tier_id`);

--
-- Indexes for table `membershiptiers`
--
ALTER TABLE `membershiptiers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qualifications`
--
ALTER TABLE `qualifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `verified_by` (`verified_by`);

--
-- Indexes for table `reactions`
--
ALTER TABLE `reactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Indexes for table `sponsors`
--
ALTER TABLE `sponsors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vacancies`
--
ALTER TABLE `vacancies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `posted_by` (`posted_by`);

--
-- Indexes for table `verifications`
--
ALTER TABLE `verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `verifier_id` (`verifier_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `bookmarks`
--
ALTER TABLE `bookmarks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `in_app_purchases`
--
ALTER TABLE `in_app_purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `memberships`
--
ALTER TABLE `memberships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `membershiptiers`
--
ALTER TABLE `membershiptiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `qualifications`
--
ALTER TABLE `qualifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reactions`
--
ALTER TABLE `reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sponsors`
--
ALTER TABLE `sponsors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `vacancies`
--
ALTER TABLE `vacancies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `verifications`
--
ALTER TABLE `verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `articles_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookmarks_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`);

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`),
  ADD CONSTRAINT `events_ibfk_3` FOREIGN KEY (`sponsor_id`) REFERENCES `sponsors` (`id`);

--
-- Constraints for table `in_app_purchases`
--
ALTER TABLE `in_app_purchases`
  ADD CONSTRAINT `in_app_purchases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `in_app_purchases_ibfk_2` FOREIGN KEY (`membership_id`) REFERENCES `memberships` (`id`);

--
-- Constraints for table `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`),
  ADD CONSTRAINT `media_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `media_ibfk_3` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `memberships`
--
ALTER TABLE `memberships`
  ADD CONSTRAINT `memberships_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `memberships_ibfk_2` FOREIGN KEY (`tier_id`) REFERENCES `membershiptiers` (`id`);

--
-- Constraints for table `qualifications`
--
ALTER TABLE `qualifications`
  ADD CONSTRAINT `qualifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `qualifications_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `reactions`
--
ALTER TABLE `reactions`
  ADD CONSTRAINT `reactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reactions_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`);

--
-- Constraints for table `vacancies`
--
ALTER TABLE `vacancies`
  ADD CONSTRAINT `vacancies_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `verifications`
--
ALTER TABLE `verifications`
  ADD CONSTRAINT `verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `verifications_ibfk_2` FOREIGN KEY (`verifier_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
