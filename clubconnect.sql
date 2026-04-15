-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 15, 2026 at 10:59 AM
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
-- Database: `clubconnect`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `club_id` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `club_id`, `created_at`) VALUES
(1, 'City Wide Camping', 'updates on club home page', 3, '2026-04-13 15:03:53'),
(2, 'Enrollment is now ongoing ', 'check your portals', 0, '2026-04-13 15:04:58');

-- --------------------------------------------------------

--
-- Table structure for table `clubs`
--

CREATE TABLE `clubs` (
  `id` int(11) NOT NULL,
  `club_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `hex_color` varchar(7) DEFAULT '#b31217'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clubs`
--

INSERT INTO `clubs` (`id`, `club_name`, `description`, `logo`, `hex_color`) VALUES
(1, 'ADT Dancing', 'The premier dance troupe of ACLC.', '/clubconnect/assetimages/ADT.jpg', '#fd2be1'),
(2, 'SCO Leaders', 'The Student Council Organization of ACLC.', '/clubconnect/assetimages/SCO.jpg', '#b31217'),
(3, 'Rover Scouts', 'ACLC Rover Circle 16 - Building character.', '/clubconnect/assetimages/RoverLogo.png', '#0040ff'),
(4, 'SAMAFIL Culture', 'Promoting Filipino cultural heritage.', '/clubconnect/assetimages/SAMAFIL.jpg', '#ffea00'),
(5, 'Red Cross Volunteer', 'Dedicated to life-saving and service.', '/clubconnect/assetimages/ACLCRC.jpg', '#00ff1e'),
(6, 'Hawks Sports', 'The official athletic organization.', '/clubconnect/assetimages/ACLCHawks.jpg', '#ffffff'),
(8, 'The Pixel', 'school pub', 'assetimages/1776088593_Gemini_Generated_Image_7ddgt07ddgt07ddg.png', '#ed4c07');

-- --------------------------------------------------------

--
-- Table structure for table `club_members`
--

CREATE TABLE `club_members` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `club_id` int(11) DEFAULT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `club_memberships`
--

CREATE TABLE `club_memberships` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `club_memberships`
--

INSERT INTO `club_memberships` (`id`, `user_id`, `club_id`, `joined_at`) VALUES
(2, 5, 3, '2026-02-28 07:21:01'),
(3, 6, 3, '2026-02-28 07:23:45'),
(4, 4, 3, '2026-03-08 08:46:17');

-- --------------------------------------------------------

--
-- Table structure for table `club_posts`
--

CREATE TABLE `club_posts` (
  `id` int(11) NOT NULL,
  `club_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `location_address` varchar(255) DEFAULT NULL,
  `event_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `club_posts`
--

INSERT INTO `club_posts` (`id`, `club_id`, `title`, `content`, `image_url`, `location_address`, `event_date`, `created_at`) VALUES
(5, 3, 'Camping City Wide', 'encampment', '', 'San Agustin Stand Alone Senior High School, San Agustin, Iriga City', '2026-04-23 07:31:00', '2026-04-13 14:31:39');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `club_id` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_notifications`
--

CREATE TABLE `event_notifications` (
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `frequency` enum('daily','weekly') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_notifications`
--

INSERT INTO `event_notifications` (`user_id`, `post_id`, `frequency`) VALUES
(3, 2, 'weekly');

-- --------------------------------------------------------

--
-- Table structure for table `event_reminders`
--

CREATE TABLE `event_reminders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `post_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_responses`
--

CREATE TABLE `event_responses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `status` enum('joining','not_joining') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `membership_requests`
--

CREATE TABLE `membership_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `club_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `membership_requests`
--

INSERT INTO `membership_requests` (`id`, `user_id`, `club_id`, `status`, `created_at`) VALUES
(8, 5, 3, 'approved', '2026-02-28 07:20:44'),
(9, 6, 3, 'approved', '2026-02-28 07:23:08'),
(10, 4, 3, 'approved', '2026-03-08 08:43:46');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `club_id`, `message_text`, `is_read`, `created_at`) VALUES
(1, 4, 3, 0, 'hello moderator', 1, '2026-03-07 20:57:02'),
(2, 3, 4, 0, 'hello there, how may I help you??', 1, '2026-03-07 20:57:35'),
(3, 4, 3, 0, 'would you mind helping me', 1, '2026-03-07 22:32:25'),
(4, 4, 3, 0, '??', 1, '2026-03-07 22:32:27'),
(5, 3, 4, 0, 'sure what do you need helping with?', 1, '2026-03-07 22:54:44'),
(6, 3, 4, 0, 'just tell me', 1, '2026-03-07 23:55:46'),
(7, 4, 3, 0, 'thank you so much', 1, '2026-03-07 23:57:02'),
(8, 3, 4, 0, 'tell me what you need', 1, '2026-03-07 23:57:25'),
(9, 4, 3, 0, 'Hi pogi, can you help me?', 1, '2026-03-08 02:10:06'),
(10, 3, 4, 0, 'sure', 1, '2026-03-08 02:10:32'),
(11, 4, 3, 0, 'could you help me with our meeting', 1, '2026-03-08 04:10:25'),
(12, 3, 4, 0, 'sure no problem', 1, '2026-03-08 04:10:53'),
(13, 4, 3, 0, 'thank you so much', 1, '2026-03-08 04:13:09'),
(14, 3, 4, 0, 'you\'re welcome', 1, '2026-03-08 04:14:42'),
(15, 4, 3, 0, 'thanks Idol', 1, '2026-03-08 05:31:52'),
(16, 3, 4, 0, 'welcome', 1, '2026-03-08 05:32:17'),
(17, 4, 3, 0, 'idol, is it real? is it true?', 1, '2026-03-08 05:34:05'),
(18, 3, 4, 0, 'yes', 1, '2026-03-08 05:34:26'),
(19, 4, 3, 0, 'thanks Idol', 1, '2026-03-08 05:34:39'),
(20, 5, 3, 0, 'unta padi', 1, '2026-03-08 05:36:46'),
(21, 3, 5, 0, 'oh?', 1, '2026-03-08 05:37:01'),
(22, 5, 3, 0, 'miss mo nnman ako', 1, '2026-03-08 05:37:09'),
(23, 3, 5, 0, 'ika gayud', 1, '2026-03-08 05:37:13'),
(24, 6, 3, 0, 'kuys, may tanong po ako', 1, '2026-03-08 05:37:59'),
(25, 3, 6, 0, 'bakit?', 1, '2026-03-08 05:38:08'),
(26, 6, 3, 0, 'wala lang', 1, '2026-03-08 05:38:12'),
(27, 6, 3, 0, 'hehe', 1, '2026-03-08 05:38:14'),
(28, 6, 3, 0, 'good night po', 1, '2026-03-08 05:38:16'),
(29, 3, 6, 0, 'hahahaha', 1, '2026-03-08 05:38:20'),
(30, 4, 3, 0, 'can I ask for help with an activity?', 1, '2026-03-08 09:04:11'),
(31, 3, 4, 0, 'sure', 1, '2026-03-08 09:04:42');

-- --------------------------------------------------------

--
-- Table structure for table `moderator_messages`
--

CREATE TABLE `moderator_messages` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'alert',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 3, 'Profanity blocked from Carl Sivan Tibi: \"****ng ina\"', 'flagged_comment', 1, '2026-02-27 02:54:53'),
(2, 3, 'Profanity blocked from Carl Sivan Tibi: \"**** off\"', 'flagged_comment', 1, '2026-02-27 02:55:00'),
(3, 3, 'Norman Grant has requested to join Rover Scouts', 'join_request', 1, '2026-02-27 03:36:09'),
(4, 4, 'Your request to join Rover Scouts was rejected.', 'request_status', 0, '2026-02-27 03:36:28'),
(5, 3, 'Norman Grant has requested to join Rover Scouts', 'join_request', 1, '2026-02-27 03:45:49'),
(6, 4, 'Your request to join Rover Scouts was rejected.', 'request_status', 0, '2026-02-27 03:46:04'),
(7, 3, 'Norman Grant has requested to join Rover Scouts', 'join_request', 1, '2026-02-27 03:58:37'),
(8, 4, 'Your request to join Rover Scouts was declined.', 'status', 0, '2026-02-27 03:58:51'),
(9, 3, 'Norman Grant has requested to join Rover Scouts', 'join_request', 1, '2026-02-27 04:03:02'),
(10, 4, 'Your request to join Rover Scouts was declined.', 'status', 0, '2026-02-27 04:03:13'),
(11, 3, 'Norman Grant has requested to join Rover Scouts', 'join_request', 1, '2026-02-27 04:05:34'),
(12, 4, 'Your request to join Rover Scouts was declined.', 'status', 0, '2026-02-27 04:05:50'),
(13, 3, 'Norman Grant has requested to join Rover Scouts', 'join_request', 1, '2026-02-27 09:13:04'),
(14, 4, 'Your request to join Rover Scouts was declined.', 'status', 0, '2026-02-27 09:19:03'),
(15, 3, 'Profanity blocked from Carl Sivan Tibi: \"**** you\"', 'flagged_comment', 1, '2026-02-28 01:11:50'),
(16, 3, 'Profanity blocked from Carl Sivan Tibi: \"****\"', 'flagged_comment', 1, '2026-02-28 01:11:57'),
(17, 3, 'Norman Grant has requested to join Rover Scouts', 'join_request', 1, '2026-02-28 02:00:19'),
(18, 4, 'Your request to join Rover Scouts has been approved! Welcome!', 'status', 0, '2026-02-28 02:00:38'),
(19, 3, 'Profanity blocked from Norman Grant: \"****\"', 'flagged_comment', 1, '2026-02-28 07:14:04'),
(20, 3, 'Angelo Concepcion has requested to join Rover Scouts', 'join_request', 1, '2026-02-28 07:20:44'),
(21, 5, 'Your request to join Rover Scouts has been approved! Welcome!', 'status', 0, '2026-02-28 07:21:01'),
(22, 3, 'John Razel Villar has requested to join Rover Scouts', 'join_request', 1, '2026-02-28 07:23:08'),
(23, 6, 'Your request to join Rover Scouts has been approved! Welcome!', 'status', 0, '2026-02-28 07:23:45'),
(24, 3, 'Profanity blocked from Angelo Concepcion: \"**** amp\"', 'flagged_comment', 1, '2026-02-28 08:08:39'),
(25, 3, 'Profanity blocked from Norman Grant: \"****\"', 'flagged_comment', 1, '2026-03-01 04:04:29'),
(26, 3, 'Blocked comment from Norman Grant: \"bobo\"', 'flagged_comment', 1, '2026-03-03 02:24:58'),
(27, 3, 'Profanity blocked from Carl Sivan Tibi: \"****\"', 'flagged_comment', 1, '2026-03-03 02:48:19'),
(28, 3, 'Blocked comment from Carl Sivan Tibi: \"fuck\"', 'flagged_comment', 1, '2026-03-03 02:53:38'),
(29, 3, 'Blocked comment from Carl Sivan Tibi: \"fuck\"', 'flagged_comment', 1, '2026-03-03 02:55:00'),
(30, 3, 'Blocked comment from Carl Sivan Tibi: \"puta\"', 'flagged_comment', 1, '2026-03-04 07:18:01'),
(31, 3, 'Blocked comment from Angelo Concepcion: \"this is dumb\"', 'flagged_comment', 1, '2026-03-07 21:52:40'),
(32, 3, 'Blocked comment from Angelo Concepcion: \"this is dumb\"', 'flagged_comment', 1, '2026-03-07 22:03:03'),
(33, 3, 'Blocked comment from Angelo Concepcion: \"bobo\"', 'flagged_comment', 1, '2026-03-07 22:15:19'),
(34, 3, 'Blocked comment from Carl Sivan Tibi: \"bobo\"', 'flagged_comment', 1, '2026-03-08 00:54:21'),
(35, 3, 'Blocked comment from Carl Sivan Tibi: \"bobo\"', 'flagged_comment', 1, '2026-03-08 00:54:31'),
(36, 3, 'Blocked comment from Carl Sivan Tibi: \"bobo\"', 'flagged_comment', 1, '2026-03-08 02:11:00'),
(37, 3, 'Norman Grant has requested to join Rover Scouts', 'join_request', 1, '2026-03-08 08:43:46'),
(38, 4, 'Your request to join Rover Scouts has been approved! Welcome!', 'status', 0, '2026-03-08 08:46:17'),
(39, 3, 'Blocked comment from Carl Sivan Tibi: \"bobo\"', 'flagged_comment', 1, '2026-03-08 08:47:10');

-- --------------------------------------------------------

--
-- Table structure for table `post_comments`
--

CREATE TABLE `post_comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_comments`
--

INSERT INTO `post_comments` (`id`, `post_id`, `user_id`, `comment_text`, `created_at`) VALUES
(35, 3, 5, 'amazing', '2026-03-07 21:52:48'),
(36, 3, 5, 'yess', '2026-03-07 22:03:00'),
(37, 3, 3, 'Niceee', '2026-03-07 22:22:44'),
(38, 3, 3, 'nice', '2026-03-08 00:54:18');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `usn` varchar(50) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Moderator','Student') NOT NULL DEFAULT 'Student',
  `profile_pic` varchar(255) DEFAULT 'assetimages/default-user.png',
  `managed_club_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `usn`, `fullname`, `email`, `password`, `role`, `profile_pic`, `managed_club_id`) VALUES
(3, '22000646400', 'Carl Sivan Tibi', 'tibi.carl1999@gmail.com', '$2y$10$N6jFRXRdd4UHuHZkXIkfeuC8wCNAZySAzdxr8tAUob0gGHbgc3dbG', 'Moderator', 'uploads/profile_3.jpg', 3),
(4, '22000546211', 'Norman Grant', 'carlsivan.19@gmail.com', '$2y$10$N6jFRXRdd4UHuHZkXIkfeuC8wCNAZySAzdxr8tAUob0gGHbgc3dbG', 'Student', 'uploads/profile_4.jpg', NULL),
(5, '21000974900', 'Angelo Concepcion', 'borromeoangelo91@gmail.com', '$2y$10$N6jFRXRdd4UHuHZkXIkfeuC8wCNAZySAzdxr8tAUob0gGHbgc3dbG', 'Student', 'uploads/profile_5.png', NULL),
(6, '20001999500', 'John Razel Villar', 'villarjohnrazel@gmail.com', '$2y$10$N6jFRXRdd4UHuHZkXIkfeuC8wCNAZySAzdxr8tAUob0gGHbgc3dbG', 'Student', 'assetimages/default-user.png', NULL),
(7, '200019899500', 'Alexander Norman', 'normangrant@gmail.com', '$2y$10$N6jFRXRdd4UHuHZkXIkfeuC8wCNAZySAzdxr8tAUob0gGHbgc3dbG', 'Admin', 'assetimages/default-user.png', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clubs`
--
ALTER TABLE `clubs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `club_members`
--
ALTER TABLE `club_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `club_id` (`club_id`);

--
-- Indexes for table `club_memberships`
--
ALTER TABLE `club_memberships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `club_id` (`club_id`);

--
-- Indexes for table `club_posts`
--
ALTER TABLE `club_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `club_id` (`club_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `club_id` (`club_id`);

--
-- Indexes for table `event_notifications`
--
ALTER TABLE `event_notifications`
  ADD PRIMARY KEY (`user_id`,`post_id`);

--
-- Indexes for table `event_reminders`
--
ALTER TABLE `event_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `event_responses`
--
ALTER TABLE `event_responses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`post_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `membership_requests`
--
ALTER TABLE `membership_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `moderator_messages`
--
ALTER TABLE `moderator_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `post_comments`
--
ALTER TABLE `post_comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usn` (`usn`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `clubs`
--
ALTER TABLE `clubs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `club_members`
--
ALTER TABLE `club_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `club_memberships`
--
ALTER TABLE `club_memberships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `club_posts`
--
ALTER TABLE `club_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_reminders`
--
ALTER TABLE `event_reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `event_responses`
--
ALTER TABLE `event_responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `membership_requests`
--
ALTER TABLE `membership_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `moderator_messages`
--
ALTER TABLE `moderator_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `post_comments`
--
ALTER TABLE `post_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `club_members`
--
ALTER TABLE `club_members`
  ADD CONSTRAINT `club_members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `club_members_ibfk_2` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `club_memberships`
--
ALTER TABLE `club_memberships`
  ADD CONSTRAINT `club_memberships_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `club_memberships_ibfk_2` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `club_posts`
--
ALTER TABLE `club_posts`
  ADD CONSTRAINT `club_posts_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_reminders`
--
ALTER TABLE `event_reminders`
  ADD CONSTRAINT `event_reminders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_reminders_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `club_posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_responses`
--
ALTER TABLE `event_responses`
  ADD CONSTRAINT `event_responses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_responses_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `club_posts` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
