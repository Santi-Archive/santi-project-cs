-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 14, 2024 at 01:21 AM
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
-- Database: `votesusg`
--

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `candidate_id` int(200) NOT NULL,
  `candidate_name` varchar(50) NOT NULL,
  `college_id` int(20) DEFAULT NULL,
  `position_id` int(3) DEFAULT NULL,
  `qualified` tinyint(1) NOT NULL DEFAULT 0,
  `remarks` varchar(255) DEFAULT NULL,
  `candidate_image` varchar(255) DEFAULT NULL,
  `party_id` int(50) DEFAULT NULL,
  `election_id` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`candidate_id`, `candidate_name`, `college_id`, `position_id`, `qualified`, `remarks`, `candidate_image`, `party_id`, `election_id`) VALUES
(486, 'Valtteri Bottas', 7, 1, 1, '', 'candidate_images/24cce07d738a470f0ab0127c2bf0a229.jpg', 1, 20),
(487, 'Lewis Hamilton', 6, 1, 1, '', 'candidate_images/46b8cceb20db85300542e9c8afffba15.jpg', 2, 20),
(488, 'Max Verstappen', 1, 2, 1, '', 'candidate_images/501843c8bfcefaf5f173654be1da5676.jpg', 1, 20),
(489, 'George Russell', 12, 2, 1, '', 'candidate_images/b8a2fdfb5da12d77a34c80b23e42a600.jpg', 2, 20),
(490, 'Carlos Sainz', 1, 3, 1, '', 'candidate_images/8415208d01a8399f3489327e903db015.jpg', 1, 20),
(491, 'Charles Leclerc', 1, 3, 1, '', 'candidate_images/0f5893bb89faccc0a5f581caefdafe54.jpg', 2, 20),
(492, 'Yuki Tsunoda', 1, 3, 1, '', 'candidate_images/0e9ecf522af8aae24cf78072c47677bd.jpg', 1, 20),
(493, 'Sergio Perez', 1, 3, 1, '', 'candidate_images/61ba3174bda88cdac23bc7a76681221b.jpg', 2, 20),
(494, 'Fernando Alonso', 7, 1, 1, '', 'candidate_images/af5d1cea25762c7de2c5c23e5424164c.jpg', 3, 20),
(495, 'Wonderwoman', 11, 3, 1, '', 'candidate_images/d90eb71a04813bfa5125a83a8ed057d6.jpg', 1, 20),
(496, 'Batman', 11, 3, 1, '', 'candidate_images/ed00943176e1f2212ad89f9fb6660379.jpg', 2, 20),
(497, 'Flash', 11, 3, 1, '', 'candidate_images/772bcd90280788769103055023c969d9.jpg', 3, 20),
(498, 'Cyborg', 11, 3, 1, '', 'candidate_images/793f1df613f23c851ecc48452448df5d.png', 2, 20),
(499, 'Abstain', 0, 1, 1, NULL, NULL, NULL, 20),
(500, 'Abstain', 0, 2, 1, NULL, NULL, NULL, 20),
(501, 'Abstain', 0, 3, 1, NULL, NULL, NULL, 20);

-- --------------------------------------------------------

--
-- Table structure for table `colleges`
--

CREATE TABLE `colleges` (
  `college_id` int(20) NOT NULL,
  `college_name` varchar(50) NOT NULL,
  `max_representatives` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `colleges`
--

INSERT INTO `colleges` (`college_id`, `college_name`, `max_representatives`) VALUES
(0, 'Abstain', 0),
(1, 'College of Computer Studies', 2),
(2, 'College of Agriculture', 2),
(3, 'College of Arts and Science', 7),
(4, 'College of Business Administration', 6),
(5, 'College of Education', 2),
(6, 'College of Engineering and Design', 5),
(7, 'Law School', 2),
(8, 'College of Mass Communication', 2),
(9, 'College of Nursing', 2),
(10, 'College of Performing and Visual Arts', 2),
(11, 'Institute of Clinical Laboratory Sciences', 3),
(12, 'Institute of Environmental and Marine Sciences', 2),
(13, 'Institute of Rehabilitative Science', 2),
(14, 'Junior High School', 5),
(15, 'Medical School', 2),
(16, 'School of Public Affairs and Governance', 2),
(17, 'Senior High School', 8);

-- --------------------------------------------------------

--
-- Table structure for table `comelec`
--

CREATE TABLE `comelec` (
  `comelec_id` int(11) NOT NULL,
  `comelec_name` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comelec`
--

INSERT INTO `comelec` (`comelec_id`, `comelec_name`, `password`) VALUES
(1, 'comelec2025', '2025comelec');

-- --------------------------------------------------------

--
-- Table structure for table `elections`
--

CREATE TABLE `elections` (
  `election_id` int(50) NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `status` enum('Scheduled','Ongoing','Completed','') NOT NULL DEFAULT 'Scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `election_name` varchar(100) NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `elections`
--

INSERT INTO `elections` (`election_id`, `start_datetime`, `end_datetime`, `status`, `created_at`, `updated_at`, `election_name`, `is_current`) VALUES
(17, '2024-11-12 21:25:00', '2024-11-30 21:25:00', 'Completed', '2024-11-30 00:29:05', '2024-11-30 00:29:05', 'Election 1', 0),
(19, '2024-12-17 11:09:00', '2024-12-18 11:10:00', 'Scheduled', '2024-12-12 20:38:08', '2024-12-13 03:49:38', 'Election 2                                    ', 0),
(20, '2024-12-13 02:00:00', '2024-12-15 02:00:00', 'Ongoing', '2024-12-12 20:53:54', '2024-12-13 22:54:57', 'Election 3', 1);

-- --------------------------------------------------------

--
-- Table structure for table `feedbacks`
--

CREATE TABLE `feedbacks` (
  `feedback_id` int(11) NOT NULL,
  `student_id` varchar(11) NOT NULL,
  `experience` int(5) NOT NULL,
  `suggestion` varchar(300) NOT NULL,
  `feedback_timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `election_id` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedbacks`
--

INSERT INTO `feedbacks` (`feedback_id`, `student_id`, `experience`, `suggestion`, `feedback_timestamp`, `election_id`) VALUES
(44, '21-1-01417', 5, 'The system is working the best, it is very responsive.', '2024-12-13 14:30:57', 20),
(45, '21-1-01417', 1, 'This system works so well, the best voting system I have used so far.', '2024-12-13 19:44:22', 20),
(46, '21-1-01417', 5, 'The system is just bad, so slow, it took me around 20 seconds just to submit the vote.', '2024-12-13 19:45:08', 20),
(47, '21-1-01417', 3, 'Hello', '2024-12-13 19:45:27', 20);

-- --------------------------------------------------------

--
-- Table structure for table `parties`
--

CREATE TABLE `parties` (
  `party_id` int(11) NOT NULL,
  `party_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parties`
--

INSERT INTO `parties` (`party_id`, `party_name`) VALUES
(1, 'CAUSE'),
(2, 'SURE'),
(3, 'INDEPENDENT');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `position_id` int(3) NOT NULL,
  `position_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`position_id`, `position_name`) VALUES
(1, 'President'),
(2, 'Vice President'),
(3, 'Representative');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` varchar(10) NOT NULL,
  `student_name` varchar(50) NOT NULL,
  `college_id` int(20) NOT NULL,
  `password` varchar(50) NOT NULL,
  `has_voted` tinyint(1) NOT NULL DEFAULT 0,
  `election_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `student_name`, `college_id`, `password`, `has_voted`, `election_id`) VALUES
('21-1-01417', 'Westen Dasig', 1, 'westen', 0, 20),
('21-1-01444', 'Alice Brown', 1, 'alice123$', 0, 17),
('21-1-01445', 'Bob Johnson', 2, 'bobSecure1!', 0, 19),
('21-1-01446', 'Charlie Smith', 3, 'charlie!Pass', 0, 20),
('21-1-01447', 'Diana Green', 4, 'dianaSecure$', 0, 17),
('21-1-01448', 'Evan White', 5, 'evanStrong#1', 0, 19),
('21-1-01449', 'Fiona Black', 6, 'fionaSafe!', 0, 20),
('21-1-01450', 'George King', 7, 'kingGeorge#', 0, 17),
('21-1-01451', 'Hannah Lee', 8, 'leeHannah$', 0, 19),
('21-1-01452', 'Isaac Hall', 9, 'isaacSecure$', 0, 20),
('21-1-01453', 'Julia Scott', 10, 'juliaPass1!', 0, 17),
('21-1-01454', 'Kevin Clark', 11, 'kevin1234$', 0, 19),
('21-1-01455', 'Laura Adams', 12, 'adamsLaura@', 0, 20),
('21-1-01456', 'Michael Harris', 13, 'michaelStrong!', 0, 17),
('21-1-01457', 'Nancy Cooper', 14, 'nancy#Secure', 0, 19),
('21-1-01458', 'Oliver Baker', 15, 'oliverSafe@', 0, 20),
('21-1-01459', 'Patrick Turner', 16, 'patrickSecure$', 0, 17),
('21-1-01460', 'Queen Wilson', 17, 'queenBuzz1!', 0, 19),
('21-1-01461', 'Rachel Hill', 1, 'rachelSecure$', 0, 20),
('21-1-01462', 'Steven Young', 2, 'stevenPass@', 0, 17),
('21-1-01463', 'Tina Allen', 3, 'tina#123', 0, 19),
('21-1-01464', 'Victor Martin', 4, 'victorSecure!', 0, 20),
('21-1-01465', 'Wendy Thompson', 5, 'wendySafe$', 0, 17),
('21-1-01466', 'Xander Moore', 6, 'xander1234!', 0, 19),
('21-1-01467', 'Yasmine Taylor', 7, 'yasmine!Pass', 0, 20),
('21-1-01468', 'Zachary Jackson', 8, 'zacharySecure$', 0, 17),
('21-1-01469', 'Angela Clark', 9, 'angelaStrong!', 0, 19),
('21-1-01470', 'Brian Walker', 10, 'brian123$', 0, 20),
('21-1-01471', 'Catherine Hall', 11, 'catherinePass$', 0, 17),
('21-1-01472', 'David Lewis', 12, 'davidSafe#', 0, 19),
('21-1-01473', 'Emily Harris', 13, 'emilySecure!', 0, 20),
('21-1-01474', 'Frank Lopez', 14, 'frankPass@', 0, 17),
('21-1-01475', 'Grace Nelson', 15, 'grace123$', 0, 19),
('21-1-01476', 'Harry Carter', 16, 'harryStrong$', 0, 20),
('21-1-01477', 'Ivy Davis', 17, 'ivySafe1!', 0, 17),
('21-1-01478', 'Jack King', 1, 'jackSecure$', 0, 19),
('21-1-01479', 'Kimberly Wright', 2, 'kimPass!', 0, 20),
('21-1-01480', 'Leo Mitchell', 3, 'leoSafe$', 0, 17),
('21-1-01481', 'Molly Adams', 4, 'molly123!', 0, 19),
('21-1-01482', 'Nathan Turner', 5, 'nathanStrong#', 0, 20),
('21-1-01483', 'Olivia Wilson', 6, 'oliviaSecure$', 0, 17),
('21-1-01484', 'Paul White', 7, 'paulSafe!', 0, 19),
('21-1-01485', 'Quinn Harris', 8, 'quinnPass@', 0, 20),
('21-1-01486', 'Rachel Young', 9, 'rachel123$', 0, 17),
('21-1-01487', 'Sam Walker', 10, 'samSecure$', 0, 19),
('21-1-01488', 'Tara Taylor', 11, 'taraSafe#', 0, 20),
('21-1-01489', 'Uma Baker', 12, 'umaPass!', 0, 17),
('21-1-01490', 'Victor Johnson', 13, 'victorSecure@', 0, 19),
('21-1-01491', 'Wendy Lewis', 14, 'wendy123$', 0, 20),
('21-1-01492', 'Xander Carter', 15, 'xanderStrong!', 0, 17),
('21-1-01493', 'Yasmine King', 16, 'yasminePass$', 0, 19),
('21-1-01494', 'Zachary Green', 17, 'zachSecure$', 0, 20);

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `vote_id` int(11) NOT NULL,
  `student_id` varchar(11) NOT NULL,
  `candidate_id` int(100) NOT NULL,
  `position_id` int(3) NOT NULL,
  `vote_timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `election_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`candidate_id`),
  ADD KEY `college_id` (`college_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `election_id` (`election_id`),
  ADD KEY `party_id` (`party_id`);

--
-- Indexes for table `colleges`
--
ALTER TABLE `colleges`
  ADD PRIMARY KEY (`college_id`);

--
-- Indexes for table `comelec`
--
ALTER TABLE `comelec`
  ADD PRIMARY KEY (`comelec_id`);

--
-- Indexes for table `elections`
--
ALTER TABLE `elections`
  ADD PRIMARY KEY (`election_id`);

--
-- Indexes for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `feedbacks_ibfk_1` (`student_id`),
  ADD KEY `election_id` (`election_id`);

--
-- Indexes for table `parties`
--
ALTER TABLE `parties`
  ADD PRIMARY KEY (`party_id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`position_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `students_ibfk_1` (`college_id`),
  ADD KEY `students_ibfk_2` (`election_id`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`vote_id`),
  ADD KEY `candidate_id` (`candidate_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `election_id` (`election_id`),
  ADD KEY `student_election_idx` (`student_id`,`election_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `candidate_id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=503;

--
-- AUTO_INCREMENT for table `colleges`
--
ALTER TABLE `colleges`
  MODIFY `college_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `comelec`
--
ALTER TABLE `comelec`
  MODIFY `comelec_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `elections`
--
ALTER TABLE `elections`
  MODIFY `election_id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `parties`
--
ALTER TABLE `parties`
  MODIFY `party_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `position_id` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `vote_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`college_id`),
  ADD CONSTRAINT `candidates_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`),
  ADD CONSTRAINT `candidates_ibfk_3` FOREIGN KEY (`election_id`) REFERENCES `elections` (`election_id`),
  ADD CONSTRAINT `candidates_ibfk_4` FOREIGN KEY (`party_id`) REFERENCES `parties` (`party_id`);

--
-- Constraints for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD CONSTRAINT `feedbacks_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `feedbacks_ibfk_2` FOREIGN KEY (`election_id`) REFERENCES `elections` (`election_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`college_id`),
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`election_id`) REFERENCES `elections` (`election_id`) ON DELETE CASCADE;

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`candidate_id`),
  ADD CONSTRAINT `votes_ibfk_3` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`),
  ADD CONSTRAINT `votes_ibfk_4` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `votes_ibfk_5` FOREIGN KEY (`election_id`) REFERENCES `elections` (`election_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
