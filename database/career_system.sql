-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 21, 2026 at 06:18 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `career_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `qualification` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `experience` int(11) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'faculty',
  `profile_image` varchar(255) DEFAULT 'default_avatar.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`id`, `name`, `email`, `password`, `qualification`, `department`, `experience`, `role`, `profile_image`) VALUES
(6, 'Leo', 'co2021.lenhard.dsouza@ves.ac.in', '67890', '4', 'comps', 34, 'faculty', ''),
(7, 'admin', 'admin@gmail.com', '12345', '4', 'comps', 5, 'admin', '1765352355_photo.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `password_requests`
--

CREATE TABLE `password_requests` (
  `id` int(11) NOT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `new_password` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_requests`
--

INSERT INTO `password_requests` (`id`, `faculty_id`, `new_password`, `status`, `request_date`) VALUES
(5, 6, '67890', 'Approved', '2025-12-10 07:40:40');

-- --------------------------------------------------------

--
-- Table structure for table `promotion_applications`
--

CREATE TABLE `promotion_applications` (
  `id` int(11) NOT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `api_score` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `applied_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `cat1` int(11) DEFAULT NULL,
  `cat2` int(11) DEFAULT NULL,
  `cat3` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotion_requests`
--

CREATE TABLE `promotion_requests` (
  `id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `current_position` varchar(100) DEFAULT NULL,
  `promotion_to` varchar(100) DEFAULT NULL,
  `document` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promotion_requests`
--

INSERT INTO `promotion_requests` (`id`, `faculty_id`, `current_position`, `promotion_to`, `document`, `remarks`, `status`, `created_at`) VALUES
(1, 6, 'Assistant Professor', 'Associate Professor', '1765175755_CSL502_ Activity report (grade) _ ELearnDBIT.pdf', '', 'Approved', '2025-12-08 06:35:55');

-- --------------------------------------------------------

--
-- Table structure for table `research_uploads`
--

CREATE TABLE `research_uploads` (
  `id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `research_uploads`
--

INSERT INTO `research_uploads` (`id`, `faculty_id`, `title`, `category`, `filename`, `uploaded_at`) VALUES
(10, 7, 'tt', 'Unknown Document Type', '1767595834_Faculty_Resume.pdf', '2026-01-05 06:50:34'),
(19, 6, 'dcfe', 'Unknown Document Type', '1768755156_Faculty_Resume.pdf', '2026-01-18 16:52:36'),
(20, 6, 'dc', 'Patent', '1768755190_career_system.pdf', '2026-01-18 16:53:10');

-- --------------------------------------------------------

--
-- Table structure for table `teaching_activities`
--

CREATE TABLE `teaching_activities` (
  `id` int(11) NOT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `teaching_hours` int(11) DEFAULT NULL,
  `feedback_score` int(11) DEFAULT NULL,
  `mentorship` int(11) DEFAULT NULL,
  `cat1_score` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teaching_activities`
--

INSERT INTO `teaching_activities` (`id`, `faculty_id`, `teaching_hours`, `feedback_score`, `mentorship`, `cat1_score`, `created_at`) VALUES
(1, 6, 1, 4, 12, 58, '2026-01-18 16:07:14'),
(2, 6, 28, 10, 26, 184, '2026-01-18 16:08:27'),
(3, 6, 0, 0, 0, 0, '2026-01-18 16:09:18'),
(4, 6, 24, 9, 4, 105, '2026-01-18 16:10:21'),
(5, 6, 10000, 9, 1000, 23045, '2026-01-18 16:11:45'),
(6, 6, 112, 8, 400, 1464, '2026-01-18 16:15:08'),
(7, 6, 10, 8, 3, 69, '2026-01-18 16:16:44'),
(8, 6, 1, 3, 3, 26, '2026-01-18 16:42:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_requests`
--
ALTER TABLE `password_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `promotion_applications`
--
ALTER TABLE `promotion_applications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `promotion_requests`
--
ALTER TABLE `promotion_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `research_uploads`
--
ALTER TABLE `research_uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `teaching_activities`
--
ALTER TABLE `teaching_activities`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `password_requests`
--
ALTER TABLE `password_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `promotion_applications`
--
ALTER TABLE `promotion_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `promotion_requests`
--
ALTER TABLE `promotion_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `research_uploads`
--
ALTER TABLE `research_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `teaching_activities`
--
ALTER TABLE `teaching_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `promotion_requests`
--
ALTER TABLE `promotion_requests`
  ADD CONSTRAINT `promotion_requests_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`);

--
-- Constraints for table `research_uploads`
--
ALTER TABLE `research_uploads`
  ADD CONSTRAINT `research_uploads_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
