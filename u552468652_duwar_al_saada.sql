-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 09, 2025 at 03:24 PM
-- Server version: 10.11.10-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u552468652_duwar_al_saada`
--

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(12,2) DEFAULT 0.00,
  `payer_name` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `type` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `payment_source` varchar(256) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `name`, `price`, `payer_name`, `image`, `type`, `quantity`, `payment_source`, `created_at`) VALUES
(4, 'tytrytry', 66.00, 'شركة', 'img_68b985770cb9e.png', 'rttr', 1, '', '2025-09-04 12:26:31');

-- --------------------------------------------------------

--
-- Table structure for table `custodies`
--

CREATE TABLE `custodies` (
  `id` int(11) NOT NULL,
  `person_name` varchar(255) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `taken_at` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `custodies`
--

INSERT INTO `custodies` (`id`, `person_name`, `amount`, `taken_at`, `notes`, `created_at`) VALUES
(1, 'بسام', 9000.00, '2025-09-27', 'gfdgfdgdfg', '2025-09-09 09:45:21');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `main_expense` varchar(100) NOT NULL,
  `sub_expense` varchar(100) NOT NULL,
  `expense_desc` varchar(255) DEFAULT NULL,
  `expense_amount` decimal(12,2) NOT NULL,
  `expense_file` varchar(255) DEFAULT NULL,
  `payer_name` varchar(256) NOT NULL,
  `payment_source` varchar(256) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `main_expense`, `sub_expense`, `expense_desc`, `expense_amount`, `expense_file`, `payer_name`, `payment_source`, `created_at`) VALUES
(17, 'مرافق وخدمات', 'هاتف وانترنت', 'ثقثصفقثفثق', 1000.00, 'img_68c032dde3677.jpg', 'بسام', 'عهدة', '2025-09-09 13:59:57');

-- --------------------------------------------------------

--
-- Table structure for table `gov_fees`
--

CREATE TABLE `gov_fees` (
  `id` int(11) NOT NULL,
  `fee_title` varchar(255) NOT NULL,
  `fee_type` varchar(255) DEFAULT NULL,
  `fee_amount` decimal(10,2) NOT NULL,
  `payer` enum('شركة','مؤسسة','فيصل المطيري','بسام') NOT NULL,
  `invoice_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gov_fees`
--

INSERT INTO `gov_fees` (`id`, `fee_title`, `fee_type`, `fee_amount`, `payer`, `invoice_image`, `created_at`) VALUES
(4, 'اقامات', 'اقامات', 600.00, 'مؤسسة', 'img_68bd45a82c2da.png', '2025-09-07 08:43:20'),
(5, 'رخصة عمل', 'الموارد البشرية', 9600.00, 'فيصل المطيري', 'img_68bd48278c927.png', '2025-09-07 08:53:59');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `qty` decimal(12,3) NOT NULL,
  `unit` enum('عدد','جرام','كيلو','لتر') NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `purchase_id`, `qty`, `unit`, `note`, `created_at`) VALUES
(4, 5, 44.000, 'جرام', 'gfdgdfg', '2025-09-04 15:42:37');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `unit` enum('عدد','جرام','كيلو','لتر') NOT NULL DEFAULT 'عدد',
  `quantity` decimal(12,3) NOT NULL DEFAULT 0.000,
  `price` decimal(12,2) DEFAULT 0.00,
  `product_image` varchar(255) DEFAULT NULL,
  `invoice_image` varchar(255) DEFAULT NULL,
  `payer_name` varchar(255) DEFAULT NULL,
  `payment_source` varchar(256) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`id`, `name`, `unit`, `quantity`, `price`, `product_image`, `invoice_image`, `payer_name`, `payment_source`, `created_at`) VALUES
(5, 'tttttttttttt88', 'جرام', 11.000, 66.00, 'img_68b98549050ec.jpg', 'img_68b9854905328.jpg', 'بسام', '', '2025-09-04 12:25:45'),
(6, 'بلح', 'لتر', 15.000, 50.00, 'img_68bc6826e3b18.png', 'img_68bd50ea6aee8.jpeg', 'فيصل المطيري', '', '2025-09-06 16:58:14'),
(7, 'trterterter', 'عدد', 66.000, 5000.00, 'img_68c00c25c187b.jpg', 'img_68c00c25c1b8c.png', 'بسام', 'عهدة', '2025-09-09 11:14:45'),
(14, 'برتقال', 'كيلو', 50.000, 5000.00, 'test1.jpg', 'test2.png', 'بسام', 'عهدة', '2025-09-09 12:43:49');

-- --------------------------------------------------------

--
-- Table structure for table `rentals`
--

CREATE TABLE `rentals` (
  `id` int(11) NOT NULL,
  `rental_name` varchar(255) NOT NULL,
  `payment_type` enum('شهري','سنوي') NOT NULL,
  `rental_price` decimal(10,2) NOT NULL,
  `rental_kind` varchar(255) DEFAULT NULL,
  `payer` enum('شركة','مؤسسة','فيصل المطيري','بسام') NOT NULL,
  `invoice_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rentals`
--

INSERT INTO `rentals` (`id`, `rental_name`, `payment_type`, `rental_price`, `rental_kind`, `payer`, `invoice_image`, `created_at`) VALUES
(10, 'المحل', 'سنوي', 15000.00, 'نصف سنوي', 'مؤسسة', 'img_68bd523de8509.png', '2025-09-07 09:37:01'),
(11, 'سكن العمال', 'سنوي', 15000.00, 'ييي', 'بسام', 'img_68bd52b08742c.jpeg', '2025-09-07 09:38:56');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `subscribers` text DEFAULT NULL,
  `subscription_type` enum('شهري','سنوي') NOT NULL,
  `service_price` decimal(10,2) NOT NULL,
  `payer` enum('شركة','مؤسسة','فيصل المطيري','بسام') NOT NULL,
  `invoice_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','manager','staff') NOT NULL DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$BxE7p6cSHKOw7ghwnp7kC.71u7mstG1jJ0Sskx2gK8U32TVaXr3zO', 'admin', '2025-08-28 09:45:00'),
(3, 'admin1', '$2y$10$3kwkcNA5TabzUeMf18Jj1u9vsJlELiZzcrUPmq1TK2O.uic/UR1kK', 'staff', '2025-08-30 15:40:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `custodies`
--
ALTER TABLE `custodies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gov_fees`
--
ALTER TABLE `gov_fees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_p` (`purchase_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
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
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `custodies`
--
ALTER TABLE `custodies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `gov_fees`
--
ALTER TABLE `gov_fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_p` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
