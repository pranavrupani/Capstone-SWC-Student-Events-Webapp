-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Nov 03, 2026 at 05:06 AM
-- Server version: 5.7.24
-- PHP Version: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `swc_events`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1, 'Wellness'),
(2, 'Creative'),
(3, 'Workshop'),
(4, 'Social');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `location` varchar(150) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `flyer_image_name` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `title`, `description`, `event_date`, `event_time`, `location`, `category_id`, `flyer_image_name`) VALUES
(1, 'Follow and Spin', 'Follow Instagram and spin the wheel to win prizes.', '2026-09-23', '12:00:00', 'E Concourse', 4, 'Follow & Spin Instagram Post.png'),
(2, '5 Senses Reset Workshop', 'Workshop focused on grounding using the 5 senses technique.', '2026-09-29', '12:00:00', 'Lakeshore G Building', 3, NULL),
(3, 'Rock and Art', 'Rock painting and DJ with snacks.', '2026-10-01', '12:00:00', 'E Concourse', 2, 'ROCK N’ ART 2026.png'),
(4, 'Friday Fun and Chill', 'Relax, grab some food, watch a movie, and enjoy a chill afternoon.', '2026-10-02', '13:00:00', 'LRC 2030', 4, 'FRIDAY FLIX N CHILL.png'),
(5, 'Rainbowland Art Workshop', 'A creative art workshop for students.', '2026-10-06', '12:00:00', 'NX 111', 2, NULL),
(6, 'SMART Goals Workshop', 'Interactive workshop focused on creating clear and achievable SMART goals.', '2026-10-15', '13:00:00', 'LRC 2112', 3, 'SMART Goals Flyer 2026.png'),
(7, 'Cawfee Chat', 'A casual space for students to connect, relax, and chat.', '2026-10-22', '13:00:00', 'LRC 2112', 4, 'Cawfee chat.jpg'),
(8, 'Halloween Fun Friday', 'Halloween movie, treats, and a relaxed social afternoon.', '2026-10-23', '12:00:00', 'LRC 2112', 4, 'Halloween Fun Friday Flyer.png'),
(9, 'Threads of Thought DIY Yarn Painting', 'A mindful creative activity using yarn to create artwork.', '2026-11-02', '13:00:00', 'LRC 2112', 2, 'Threads of Thought.jpg'),
(10, 'Diwali', 'A campus celebration of Diwali.', '2026-11-06', '12:00:00', 'IGNITE Student Centre', 4, 'Diwali.jpg'),
(11, 'Tote Bag Painting', 'Decorate and personalize your own tote bag.', '2026-11-09', '13:00:00', 'LRC 2112', 2, 'Mindful Monday 2 - Tote Bag Flyer.png'),
(12, 'Sounds and Rhythm of Poetry', 'A poetry and spoken word open mic experience.', '2026-11-10', '13:00:00', 'LRC 2112', 2, 'Sounds and Rhythm.jpg'),
(13, 'Game and Chill', 'Play games, connect with students, and relax.', '2026-11-13', '13:00:00', 'LRC 2112', 4, 'Game & Chill HSSS IG Post.png'),
(14, 'Dear Me DIY Master Poster Painting', 'A mindful poster painting activity focused on creativity and self-reflection.', '2026-11-16', '13:00:00', 'LRC 2112', 2, NULL),
(15, 'Trans Day of Remembrance', 'An event recognizing and remembering members of the transgender community.', '2026-11-20', '12:00:00', 'Humber Arboretum', 1, NULL),
(16, 'Cawfee Chat', 'A casual space for students to connect, relax, and chat.', '2026-11-23', '13:00:00', 'LRC 2112', 4, 'Cawfee chat.jpg'),
(17, 'Charms and Chimes', 'A creative activity where students can make charms and decorative pieces.', '2026-11-27', '12:00:00', 'LRC 2112', 2, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `saved_events`
--

CREATE TABLE `saved_events` (
  `saved_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `saved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` varchar(50) NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin', 'admin@swc.com', 'admin123', 'admin', '2026-09-21 14:30:00'),
(2, 'Student One', 'student1@swc.com', 'student123', 'student', '2026-09-21 15:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `fk_event_category` (`category_id`);

--
-- Indexes for table `saved_events`
--
ALTER TABLE `saved_events`
  ADD PRIMARY KEY (`saved_id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`event_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `saved_events`
--
ALTER TABLE `saved_events`
  MODIFY `saved_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_event_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `saved_events`
--
ALTER TABLE `saved_events`
  ADD CONSTRAINT `saved_events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `saved_events_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
