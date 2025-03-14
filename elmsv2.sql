-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 14, 2025 at 11:23 AM
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
-- Database: `elms`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `morning_time_in` time DEFAULT NULL,
  `morning_time_out` time DEFAULT NULL,
  `afternoon_time_in` time DEFAULT NULL,
  `afternoon_time_out` time DEFAULT NULL,
  `total_hours` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `user_id`, `date`, `morning_time_in`, `morning_time_out`, `afternoon_time_in`, `afternoon_time_out`, `total_hours`) VALUES
(39, 10, '2025-03-12', NULL, NULL, '09:04:39', '09:05:15', '00:00:36'),
(40, 10, '2025-03-13', '08:31:07', '08:31:20', '06:26:41', '06:26:45', '00:00:17');

-- --------------------------------------------------------

--
-- Table structure for table `deductions`
--

CREATE TABLE `deductions` (
  `id` int(11) NOT NULL,
  `deduction_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deductions`
--

INSERT INTO `deductions` (`id`, `deduction_name`, `description`, `created_at`) VALUES
(4, 'Income Tax', 'Government tax deduction', '2025-03-13 14:26:00'),
(5, 'Health Insurance', 'Monthly health plan contribution', '2025-03-13 14:26:00'),
(6, 'Retirement Fund', 'Retirement plan contribution', '2025-03-13 14:26:00'),
(7, 'Loan Repayment', 'Salary loan repayment', '2025-03-13 14:26:00'),
(8, 'Tardiness Deduction', 'Deductions for being late', '2025-03-13 14:26:00');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `department_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `department_name`, `department_description`, `created_at`) VALUES
(2, 'HR Department', 'Manages employee records and hiring', '2025-02-26 08:04:37'),
(4, 'IT', 'Handles technology and other related concerns.', '2025-02-27 02:04:24'),
(5, 'ICT', 'Information and Communications Technology', '2025-02-27 02:06:10');

-- --------------------------------------------------------

--
-- Table structure for table `earnings`
--

CREATE TABLE `earnings` (
  `id` int(11) NOT NULL,
  `earning_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `earnings`
--

INSERT INTO `earnings` (`id`, `earning_name`, `description`, `created_at`) VALUES
(6, 'Performance Bonus', 'Bonus for excellent performance', '2025-03-13 14:25:42'),
(7, 'Sales Commission', 'Commission for sales achievements', '2025-03-13 14:25:42'),
(8, 'Transport Allowance', 'Allowance for daily commute', '2025-03-13 14:25:42');

-- --------------------------------------------------------

--
-- Table structure for table `leave_request`
--

CREATE TABLE `leave_request` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `leave_type` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days` int(11) NOT NULL,
  `status` enum('Approved','Pending') NOT NULL DEFAULT 'Approved'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `earnings` decimal(10,2) DEFAULT NULL,
  `deductions` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','paid') DEFAULT NULL,
  `pay_date` date DEFAULT NULL,
  `net_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_deductions`
--

CREATE TABLE `payroll_deductions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `deduction_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll_deductions`
--

INSERT INTO `payroll_deductions` (`id`, `user_id`, `deduction_id`, `amount`, `created_at`) VALUES
(30, 10, 5, 0.00, '2025-03-14 02:32:06');

-- --------------------------------------------------------

--
-- Table structure for table `payroll_earnings`
--

CREATE TABLE `payroll_earnings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `earning_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll_earnings`
--

INSERT INTO `payroll_earnings` (`id`, `user_id`, `earning_id`, `amount`, `created_at`) VALUES
(63, 10, 6, 9.00, '2025-03-14 02:32:06'),
(64, 10, 7, 0.00, '2025-03-14 02:32:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Employee') NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `employment_start_date` date DEFAULT NULL,
  `employment_end_date` date DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL,
  `employee_type` varchar(50) NOT NULL,
  `salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `leave_balance` int(11) NOT NULL DEFAULT 0,
  `reset_token` varchar(64) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `middle_name`, `last_name`, `email`, `password`, `role`, `gender`, `phone`, `address`, `birthdate`, `employment_start_date`, `department_id`, `created_at`, `profile_picture`, `employee_type`, `salary`, `leave_balance`, `reset_token`, `token_expiry`, `employment_end_date`) VALUES
(1, 'Admin', '', 'User', 'admin@gmail.com', '$2y$10$4gkMOXDytIkBqM9pSt6FL.cCxn5KxFT3UpFiJ8Wfguto8DOmdvKca', 'Admin', 'Male', '09561595988', 'Brgy. Daga Cadiz City', '2003-05-31', '2025-02-21', 2, '2025-02-24 12:17:39', '1741746435_elms.jpg', 'Full-time', 0.00, 0, '8b5ff27dc492794e6c33d2ae4f6481a34e2439c2e906c0b34acd22af669aac9a', '2025-03-12 09:38:10', '0000-00-00'),
(10, 'Employee', '', 'User', 'employee@gmail.com', '$2y$10$drDpBCNjtSXPDuzkr.j4FehNFwfoC6EGkfdMSgP730VRm2segVavm', 'Employee', 'Male', '09561595988', 'Prk. Malipayun Brgy. Daga Cadiz City Negros Occidental', '2003-05-31', '2025-02-11', 2, '2025-02-27 22:29:30', '1741665249_1740695126_cedar2.png', 'Full-time', 2000.00, 20, '694737ec0eac693c659879b49303ccde2e1fc43a5f2de8bab680d12545034fe4', '2025-03-12 09:39:26', '2025-03-11');
--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `after_user_insert` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    INSERT INTO payroll (user_id, salary, status, pay_date)
    VALUES (NEW.user_id, NEW.salary, 'pending', 'N/A');
END
$$
DELIMITER ;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `deductions`
--
ALTER TABLE `deductions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `earnings`
--
ALTER TABLE `earnings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `leave_request`
--
ALTER TABLE `leave_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `payroll_deductions`
--
ALTER TABLE `payroll_deductions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `payroll_earnings`
--
ALTER TABLE `payroll_earnings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
