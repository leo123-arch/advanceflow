
<<<<<<< HEAD
CREATE TABLE faculty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    password VARCHAR(255),
    qualification VARCHAR(100),
    experience INT,
    role VARCHAR(20) DEFAULT 'faculty'
);

CREATE TABLE promotion_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT,
    api_score INT,
    document VARCHAR(255),
    status VARCHAR(20) DEFAULT 'Pending',
    applied_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
=======
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
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `biography` text DEFAULT NULL,
  `role` varchar(20) DEFAULT 'faculty',
  `profile_image` varchar(255) DEFAULT 'default_avatar.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

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
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
>>>>>>> 90e527b (Initial commit)
