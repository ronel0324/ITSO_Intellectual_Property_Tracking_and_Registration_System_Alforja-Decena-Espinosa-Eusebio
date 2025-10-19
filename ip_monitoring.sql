-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 19, 2025 at 05:04 AM
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
-- Database: `ip_monitoring`
--

-- --------------------------------------------------------

--
-- Table structure for table `campuses`
--

CREATE TABLE `campuses` (
  `campus_id` int(11) NOT NULL,
  `campus_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `campuses`
--

INSERT INTO `campuses` (`campus_id`, `campus_name`) VALUES
(1, 'Los Baños'),
(2, 'San Pablo'),
(3, 'Sta. Cruz'),
(4, 'Siniloan');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `campus_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `campus_id`, `department_name`) VALUES
(1, 3, 'College of Arts and Sciences'),
(2, 3, 'College of Business, Administration and Accountancy'),
(3, 3, 'College of Computer Studies'),
(4, 3, 'College of Criminal Justice Education'),
(5, 3, 'College of Engineering'),
(6, 3, 'College of Industrial Technology'),
(7, 3, 'College of International Hospitality and Tourism Management'),
(8, 3, 'College of Law'),
(9, 3, 'College of Nursing and Allied Health'),
(10, 3, 'College of Teacher Education'),
(11, 1, 'College of Arts and Sciences'),
(12, 1, 'College of Business, Administration and Accountancy'),
(13, 1, 'College of Computer Studies'),
(14, 1, 'College of Criminal Justice Education'),
(15, 1, 'College of Fisheries'),
(16, 1, 'College of Food Nutrition and Dietetics'),
(17, 1, 'College of International Hospitality and Tourism Management'),
(18, 1, 'College of Teacher Education'),
(19, 2, 'College of Arts and Sciences'),
(20, 2, 'College of Business, Administration and Accountancy'),
(21, 2, 'College of Computer Studies'),
(22, 2, 'College of Criminal Justice Education'),
(23, 2, 'College of Engineering'),
(24, 2, 'College of Industrial Technology'),
(25, 2, 'College of International Hospitality and Tourism Management'),
(26, 2, 'College of Teacher Education'),
(27, 4, 'College of Agriculture'),
(28, 4, 'College of Arts and Sciences'),
(29, 4, 'College of Business, Administration and Accountancy'),
(30, 4, 'College of Computer Studies'),
(31, 4, 'College of Criminal Justice Education'),
(32, 4, 'College of Engineering'),
(33, 4, 'College of Food Nutrition and Dietetics'),
(34, 4, 'College of International Hospitality and Tourism Management'),
(35, 4, 'College of Teacher Education');

-- --------------------------------------------------------

--
-- Table structure for table `intellectual_properties`
--

CREATE TABLE `intellectual_properties` (
  `ip_id` int(11) NOT NULL,
  `tracking_id` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `authors` varchar(255) DEFAULT NULL,
  `classification` enum('Copyright','Trademark','Patent','Utility Model','Industrial Design') NOT NULL,
  `endorsement_letter` varchar(255) DEFAULT NULL,
  `status` enum('Ongoing','Pending','Completed') NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `application_form` varchar(255) DEFAULT NULL,
  `submitted` tinyint(1) DEFAULT 0,
  `application_fee` varchar(255) DEFAULT NULL,
  `issued_certificate` varchar(255) DEFAULT NULL,
  `project_file` varchar(255) DEFAULT NULL,
  `authors_file` varchar(255) NOT NULL,
  `date_submitted_to_ipophil` date DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  `applicant_name` varchar(255) NOT NULL,
  `date_submitted_to_itso` datetime NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `campus_id` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `department_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `intellectual_properties`
--

INSERT INTO `intellectual_properties` (`ip_id`, `tracking_id`, `title`, `authors`, `classification`, `endorsement_letter`, `status`, `remarks`, `application_form`, `submitted`, `application_fee`, `issued_certificate`, `project_file`, `authors_file`, `date_submitted_to_ipophil`, `expiration_date`, `applicant_name`, `date_submitted_to_itso`, `email`, `campus_id`, `updated_at`, `department_id`) VALUES
(1, 'ITSO-2025-001', 'LSPU-LBC ITSO Intellectual Property Tracking and Registration System', 'Ronel Joshua D. Alforja, Merardo A. Camba Jr., Crisanto F. Gulay, Alejandro Matute Jr.', 'Copyright', 'TEST PDF.pdf', 'Completed', 'please follow up application fee tnx.', 'Copyright-Registry-Enrollment-Form-2025-1-ITSO-Alforja.pdf', 0, NULL, NULL, 'LSPU-LBC-ITSO-Intellectual-Property-Tracking-and-Registration-System.pdf', '', '0000-00-00', NULL, 'Laguna State Polytechnic University - LBC', '2025-07-07 10:25:34', 'ronelalforja@gmail.com', 1, '2025-10-16 10:41:55', 13),
(2, 'ITSO-2025-002', 'TEST A', 'test', 'Copyright', 'Certificate of Registration.pdf', 'Ongoing', '', 'Certificate of Registration.pdf', 0, NULL, NULL, 'Certificate of Registration.pdf', '', '1975-01-10', '2025-01-10', 'Laguna State Polytechnic University - LBC', '2025-10-16 10:51:11', 'test@gmail.com', 1, '2025-10-16 19:07:55', 13);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `department_name` varchar(255) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `campus_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `title`, `message`, `department_name`, `department_id`, `campus_id`, `created_at`, `is_read`) VALUES
(1, 'LSPU-LBC ITSO Intellectual Property Tracking and Registration System', 'has been updated to Ongoing.', NULL, 13, 1, '2025-10-16 02:34:47', 1),
(2, 'LSPU-LBC ITSO Intellectual Property Tracking and Registration System', 'has been updated to Completed. Remarks: please follow up application fee tnx.', NULL, 13, 1, '2025-10-16 02:41:55', 1),
(3, 'TEST A', 'has been updated to Ongoing.', NULL, 13, 1, '2025-10-16 02:51:25', 1),
(4, 'TEST A', 'has been updated to Pending. Remarks: test', NULL, 13, 1, '2025-10-16 02:52:53', 1),
(5, 'TEST A', 'has been updated to Ongoing.', NULL, 13, 1, '2025-10-16 02:55:33', 1),
(6, 'TEST A', 'has been updated to Pending. Remarks: test', NULL, 13, 1, '2025-10-16 03:06:38', 1),
(7, 'TEST A', 'has been updated to Ongoing.', NULL, 13, 1, '2025-10-16 03:07:47', 1),
(8, 'TEST A', 'has been updated to Pending. Remarks: test', NULL, 13, 1, '2025-10-16 03:09:25', 1),
(9, 'TEST A', 'has been updated to Ongoing.', NULL, 13, 1, '2025-10-16 03:14:11', 1),
(10, 'TEST A', 'has been updated to Pending. Remarks: testing', NULL, 13, 1, '2025-10-16 03:16:52', 1),
(11, 'TEST A', 'has been updated to Ongoing.', NULL, 13, 1, '2025-10-16 03:18:32', 1),
(12, 'TEST A', 'has been updated to Pending. Remarks: testing', NULL, 13, 1, '2025-10-16 03:19:00', 1),
(13, 'TEST A', 'has been updated to Ongoing.', NULL, 13, 1, '2025-10-16 03:20:08', 1),
(14, 'TEST A', 'has been updated to Pending. Remarks: task', NULL, 13, 1, '2025-10-16 03:20:28', 1),
(15, 'TEST A', 'has been updated to Ongoing.', NULL, 13, 1, '2025-10-16 03:22:50', 1),
(16, 'TEST A', 'has been updated to Pending. Remarks: tuwest', NULL, 13, 1, '2025-10-16 03:23:11', 1),
(17, 'TEST A', 'has been updated to Ongoing.', NULL, 13, 1, '2025-10-16 03:25:46', 1),
(18, 'TEST A', 'has been updated to Pending. Remarks: ahys', NULL, 13, 1, '2025-10-16 03:26:14', 1),
(19, 'TEST A', 'has been updated to Ongoing.', NULL, 13, 1, '2025-10-16 03:35:10', 1),
(20, 'TEST A', 'has been updated to Pending. Remarks: bohayss', NULL, 13, 1, '2025-10-16 03:35:31', 1),
(21, 'TEST A', 'has been updated to Ongoing.', NULL, 13, 1, '2025-10-16 03:39:49', 1),
(22, 'TEST A', 'has been updated to Pending. Remarks: last', NULL, 13, 1, '2025-10-16 03:46:02', 1),
(23, 'TEST A', 'has been updated to Ongoing.', NULL, 13, 1, '2025-10-16 03:49:55', 1),
(24, 'TEST A', 'has been updated to Pending. Remarks: ayaw', NULL, 13, 1, '2025-10-16 03:56:56', 1),
(25, 'TEST A', 'has been updated to Ongoing.', NULL, 13, 1, '2025-10-16 03:58:00', 1),
(26, 'TEST A', 'has been updated to Pending. Remarks: gays', NULL, 13, 1, '2025-10-16 03:59:06', 1),
(27, 'TEST A', 'has been updated to Ongoing.', NULL, 13, 1, '2025-10-16 03:59:29', 1),
(28, 'TEST A', 'has been updated to Pending. Remarks: remarlkss', NULL, 13, 1, '2025-10-16 04:00:05', 1),
(29, 'TEST A', 'has been updated to Ongoing.', NULL, 13, 1, '2025-10-16 04:02:12', 1),
(30, 'TEST A', 'has been updated to Pending. Remarks: test?', NULL, 13, 1, '2025-10-16 04:02:35', 1),
(31, 'TEST A', 'has been updated to Ongoing.', NULL, 13, 1, '2025-10-16 04:04:30', 1),
(32, 'TEST A', 'has been updated to Pending. Remarks: wrong details...', NULL, 13, 1, '2025-10-16 04:04:59', 1),
(33, 'TEST A', 'has been updated to Ongoing.', NULL, 13, 1, '2025-10-16 04:05:41', 1),
(34, 'TEST A', 'has been updated to Pending. Remarks: test a', NULL, 13, 1, '2025-10-16 11:01:37', 1),
(35, 'TEST A', 'has been updated to Ongoing.', NULL, 13, 1, '2025-10-16 11:07:55', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `campus_id` int(11) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Director','Chairperson','Coordinator') NOT NULL,
  `department_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `campus_id`, `password`, `role`, `department_id`, `status`, `created_at`) VALUES
(1, 'superadmin', NULL, '$2y$10$l2MqAwp2tv3w63WOFSi0duYPF2A.Fij0FI/UTQfDvEhBMJDtyg0bi', 'Director', 0, 'approved', '2025-10-16 09:51:39'),
(3, 'directorlbc', 1, '$2y$10$.e7EyQhhlcpu9pwkbtQXzOtzBfjhXxj1ZKgJDYX0OjT62RURjC/dO', 'Director', 0, 'approved', '2025-10-16 10:13:13'),
(4, 'chairpersonlbc', 1, '$2y$10$Oauyr33GJbTJFeOJXHaLVOhuxnI3SoE2Yb8JFupF.uQAiMq78Iy2.', 'Chairperson', 0, 'approved', '2025-10-16 10:17:54'),
(5, 'ccslbc', 1, '$2y$10$qzJLT.XcxxLzTHafWWP9/OlZTz5nJkhxeY4Ij78xIZ/vg2T77cXui', 'Coordinator', 13, 'approved', '2025-10-16 10:19:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `campuses`
--
ALTER TABLE `campuses`
  ADD PRIMARY KEY (`campus_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD KEY `campus_id` (`campus_id`);

--
-- Indexes for table `intellectual_properties`
--
ALTER TABLE `intellectual_properties`
  ADD PRIMARY KEY (`ip_id`),
  ADD UNIQUE KEY `tracking_id` (`tracking_id`),
  ADD KEY `fk_campus` (`campus_id`),
  ADD KEY `fk_department` (`department_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `campuses`
--
ALTER TABLE `campuses`
  MODIFY `campus_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `intellectual_properties`
--
ALTER TABLE `intellectual_properties`
  MODIFY `ip_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`campus_id`);

--
-- Constraints for table `intellectual_properties`
--
ALTER TABLE `intellectual_properties`
  ADD CONSTRAINT `fk_campus` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`campus_id`),
  ADD CONSTRAINT `fk_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
