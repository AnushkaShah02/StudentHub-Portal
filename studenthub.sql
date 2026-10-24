-- ========================================================
-- StudentHub Portal - Database Dump (Practical 15 Final)
-- Student: Anushka Shah (25CS097) | CHARUSAT CSE
-- Database: studenthub
-- ========================================================

CREATE DATABASE IF NOT EXISTS `studenthub` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `studenthub`;

-- 1. Table structure for table `users` (Auth & RBAC - Practicals 9, 10)
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('student', 'admin') DEFAULT 'student',
  `remember_token` VARCHAR(64) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Table structure for table `students` (Student CRUD - Practical 11)
CREATE TABLE `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `student_id_num` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `department` VARCHAR(50) DEFAULT 'CSE',
  `semester` VARCHAR(20) DEFAULT '3rd Sem',
  `cgpa` DECIMAL(3,2) DEFAULT 0.00,
  `attendance` DECIMAL(4,1) DEFAULT 0.0,
  `points` INT DEFAULT 0,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 3. Table structure for table `events` (Event CRUD & Posters - Practical 12)
CREATE TABLE `events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `type` ENUM('HACKATHON', 'WORKSHOP', 'SEMINAR', 'SYMPOSIUM') NOT NULL,
  `event_date` DATE NOT NULL,
  `country` VARCHAR(80) DEFAULT 'India',
  `state` VARCHAR(80) DEFAULT 'Gujarat',
  `city` VARCHAR(80) DEFAULT 'Anand',
  `venue` VARCHAR(150) NOT NULL,
  `registrations` INT DEFAULT 0,
  `seats` VARCHAR(50) DEFAULT '100',
  `fee` VARCHAR(50) DEFAULT 'Free Entry',
  `description` TEXT NOT NULL,
  `poster_image` VARCHAR(255) DEFAULT 'default_event.png',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. Table structure for table `registrations` (Relational Mapping - Practical 8)
CREATE TABLE `registrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `event_id` INT NOT NULL,
  `registration_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_reg_student` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reg_event` FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Table structure for table `audit_logs` (Security & Traceability - Practical 14)
CREATE TABLE `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ========================================================
-- SEED DATA INSERTION
-- Password for all accounts below is: Admin@1234
-- ========================================================
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'System Administrator', 'admin@charusat.edu.in', '$2y$10$78LvhcKz2Fj01M2w7g0JveW2VshR2iJdI3JjP4E4kYkO6xM3C3Hgy', 'admin'),
(2, 'Anushka Shah', 'anushka.shah@student.edu', '$2y$10$78LvhcKz2Fj01M2w7g0JveW2VshR2iJdI3JjP4E4kYkO6xM3C3Hgy', 'student');

INSERT INTO `students` (`id`, `user_id`, `student_id_num`, `name`, `email`, `department`, `semester`, `cgpa`, `attendance`, `points`, `status`) VALUES
(1, 2, '25CS097', 'Anushka Shah', 'anushka.shah@student.edu', 'CSE', '3rd Sem', 9.20, 88.5, 980, 'active'),
(2, NULL, '25CS042', 'Shrusti Chavada', 'shrusti@charusat.edu.in', 'CSE', '3rd Sem', 8.95, 82.0, 920, 'active'),
(3, NULL, '25CS019', 'Bhavyaa Patel', 'bhavyaa@charusat.edu.in', 'CSE', '3rd Sem', 8.70, 78.5, 850, 'active'),
(4, NULL, '25IT011', 'Pooja Patel', 'pooja@charusat.edu.in', 'IT', '3rd Sem', 9.12, 91.0, 810, 'active'),
(5, NULL, '25CS088', 'Kabir Mehta', 'kabir@charusat.edu.in', 'CSE', '3rd Sem', 8.40, 76.0, 790, 'active');

INSERT INTO `events` (`id`, `title`, `type`, `event_date`, `country`, `state`, `city`, `venue`, `registrations`, `seats`, `fee`, `description`) VALUES
(1, 'Algorithmic Duel CSE', 'HACKATHON', '2026-10-03', 'India', 'Gujarat', 'Anand', 'Room 302, Block B', 120, '150', 'Free Entry', 'Optimize tree structures, write competitive syntax, and win cash prizes.'),
(2, 'Modern CSS & Flex Layouts', 'WORKSHOP', '2026-10-14', 'USA', 'California', 'San Francisco', 'Lab 3, CSE Floor', 45, '50', 'Reserved', 'Specialized masterclass explaining organic responsive designs.'),
(3, 'Innovate CSE Expo', 'SYMPOSIUM', '2026-11-02', 'India', 'Gujarat', 'Anand', 'Campus Dome', 350, 'All CSE', 'Mandatory', 'Annual physical project showcase evaluated by industrial panels.');

INSERT INTO `audit_logs` (`user_id`, `action`, `details`, `ip_address`) VALUES
(1, 'SYSTEM_INITIALIZATION', 'Database seeded with core institutional entities', '127.0.0.1');