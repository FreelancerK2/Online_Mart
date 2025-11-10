-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 07, 2025 at 04:00 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mini-mart`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '827ccb0eea8a706c4c34a16891f84e7b');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image_path` varchar(500) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `icon`, `created_at`, `updated_at`, `image_path`, `vendor_id`) VALUES
(1, 'Cake and Milk', 'asdfasdf', 'fas fa-tag', '2025-11-05 18:07:22', '2025-11-06 18:50:10', 'image/categories/category_1762455010_690cede27c6ef.jpg', 1),
(7, 'Fresh Fruits', 'Fresh Fruits', NULL, '2025-11-06 18:52:18', '2025-11-06 18:54:51', 'image/categories/category_1762455291_690ceefbd9d1c.jpg', 1),
(8, 'Coffees & Teas', 'Coffees/Tease', NULL, '2025-11-06 19:01:04', '2025-11-06 19:01:04', 'image/categories/category_1762455664_690cf0706a4d1.jpg', 3),
(9, 'Pet Foods', 'Pet\'s Foods', NULL, '2025-11-06 20:04:21', '2025-11-06 20:04:21', 'image/categories/category_1762459461_690cff45b12b6.webp', 1),
(10, 'Vegetables', 'Green vegetables', NULL, '2025-11-06 20:06:04', '2025-11-06 20:06:04', 'image/categories/category_1762459564_690cffac2531e.jpg', 3),
(11, 'Milks & Dairies', 'Milks and Dairies', NULL, '2025-11-06 20:09:49', '2025-11-06 20:09:49', 'image/categories/category_1762459789_690d008d6af44.jpg', 2),
(12, 'Meats', 'Meats', NULL, '2025-11-06 20:13:59', '2025-11-06 20:13:59', 'image/categories/category_1762460039_690d0187e175a.jpg', 2),
(13, 'Beverage', 'Beverage', NULL, '2025-11-06 20:17:15', '2025-11-06 20:17:15', 'image/categories/category_1762460235_690d024bc7c7b.webp', 2);

-- --------------------------------------------------------

--
-- Table structure for table `hero_section`
--

CREATE TABLE `hero_section` (
  `id` int(11) NOT NULL,
  `title_line1` varchar(255) NOT NULL,
  `title_line2` varchar(255) NOT NULL,
  `title_color` varchar(50) DEFAULT 'text-green-600',
  `subtitle` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `button_text` varchar(100) DEFAULT 'Shop Now',
  `button_link` varchar(255) DEFAULT '#',
  `background_color` varchar(50) DEFAULT 'from-green-50 to-green-100',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hero_section`
--

INSERT INTO `hero_section` (`id`, `title_line1`, `title_line2`, `title_color`, `subtitle`, `description`, `image_path`, `button_text`, `button_link`, `background_color`, `created_at`, `updated_at`) VALUES
(1, 'Fresh Vegetables', 'Big discount', 'text-blue-700', 'Save up to 50% off on your first order', '', 'image/hero/hero_1762332041_690b0d899771b.webp', 'Shop Now', '#', 'from-indigo-400 to-indigo-600', '2025-11-05 08:00:11', '2025-11-05 16:38:18');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
  `shipping_address` text DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `total_amount`, `status`, `payment_status`, `shipping_address`, `billing_address`, `created_at`, `updated_at`) VALUES
(1, 'ORD-690DAA32-722', 1, 12.16, 'shipped', 'paid', '{\"firstName\":\"John\",\"lastName\":\"Doe\",\"email\":\"user1@example.com\",\"phone\":\"+85569595976\",\"address\":\"TOUL PONG RO 2\",\"city\":\"Phnom Penh\",\"state\":\"as\",\"zip\":\"010705\"}', '{\"firstName\":\"John\",\"lastName\":\"Doe\",\"email\":\"user1@example.com\",\"phone\":\"+85569595976\",\"address\":\"TOUL PONG RO 2\",\"city\":\"Phnom Penh\",\"state\":\"as\",\"zip\":\"010705\"}', '2025-11-07 08:13:38', '2025-11-07 08:30:46'),
(2, 'ORD-690DB231-996', 1, 14.32, 'shipped', 'paid', '{\"firstName\":\"John\",\"lastName\":\"Doe\",\"email\":\"user1@example.com\",\"phone\":\"+85569595976\",\"address\":\"TOUL PONG RO 2\",\"city\":\"Phnom Penh\",\"state\":\"as\",\"zip\":\"010705\"}', '{\"firstName\":\"John\",\"lastName\":\"Doe\",\"email\":\"user1@example.com\",\"phone\":\"+85569595976\",\"address\":\"TOUL PONG RO 2\",\"city\":\"Phnom Penh\",\"state\":\"as\",\"zip\":\"010705\"}', '2025-11-07 08:47:45', '2025-11-07 08:50:11'),
(3, 'ORD-690DCC88-920', NULL, 135.71, 'pending', 'paid', '{\"firstName\":\"Guest\",\"lastName\":\"User\",\"email\":\"guest@example.com\",\"phone\":\"+85569595976\",\"address\":\"TOUL PONG RO 2\",\"city\":\"Phnom Penh\",\"state\":\"ad\",\"zip\":\"010705\"}', '{\"firstName\":\"Guest\",\"lastName\":\"User\",\"email\":\"guest@example.com\",\"phone\":\"+85569595976\",\"address\":\"TOUL PONG RO 2\",\"city\":\"Phnom Penh\",\"state\":\"ad\",\"zip\":\"010705\"}', '2025-11-07 10:40:08', '2025-11-07 10:40:08');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) GENERATED ALWAYS AS (`quantity` * `price`) STORED,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
(1, 1, 18, 1, 2.00, '2025-11-07 08:13:38', '2025-11-07 08:13:38'),
(2, 2, 18, 2, 2.00, '2025-11-07 08:47:45', '2025-11-07 08:47:45'),
(3, 3, 17, 3, 14.10, '2025-11-07 10:40:08', '2025-11-07 10:40:08'),
(4, 3, 16, 2, 37.05, '2025-11-07 10:40:08', '2025-11-07 10:40:08');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `discount_percentage` decimal(5,2) DEFAULT 0.00,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `vendor_name` varchar(255) DEFAULT NULL,
  `promotion_status` varchar(50) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 0.00,
  `vendor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `quantity`, `created_at`, `category_id`, `description`, `image_path`, `discount_percentage`, `discount_price`, `vendor_name`, `promotion_status`, `rating`, `vendor_id`) VALUES
(3, 'phone', 10000.00, 1, '2025-11-02 07:22:36', NULL, NULL, NULL, 0.00, NULL, NULL, NULL, 0.00, NULL),
(5, 'House', 110000.00, 1, '2025-11-02 14:58:23', NULL, NULL, NULL, 0.00, NULL, NULL, NULL, 0.00, NULL),
(6, 'Book', 10000.00, 1, '2025-11-02 14:59:49', NULL, NULL, NULL, 0.00, NULL, NULL, NULL, 0.00, NULL),
(11, 'Car', 10000.00, 1, '2025-11-02 15:06:07', NULL, NULL, NULL, 0.00, NULL, NULL, NULL, 0.00, NULL),
(15, 'Cake', 23.00, 3, '2025-11-06 06:16:30', 1, 'Cake', 'image/products/product_1762460620_690d03cce025a.jpg', 0.00, NULL, 'Kruy', 'hot_deals,popular,new_arrival,best_sellers', 0.00, 3),
(16, 'Pet Foods', 39.00, 3, '2025-11-06 06:25:50', 9, 'Foods', 'image/products/product_1762460889_690d04d966188.webp', 5.00, 37.05, 'Tybo', 'hot_deals,popular,new_arrival', 0.00, 2),
(17, 'Meats', 15.00, 3, '2025-11-06 07:20:50', 12, 'Meats', 'image/products/product_1762460808_690d04885b31f.jpg', 6.00, 14.10, 'Tybo', 'hot_deals,popular,new_arrival', 0.00, 2),
(18, 'Coffees', 2.00, 0, '2025-11-06 20:25:41', 8, 'Coffees', 'image/products/product_1762460741_690d0445ad744.jpg', 0.00, NULL, 'Kruy', 'hot_deals,popular,new_arrival', 0.00, 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `phone`, `created_at`) VALUES
(1, 'user1', 'user1@example.com', '6ad14ba9986e3615423dfca256d04e3f', 'John', 'Doe', NULL, '2025-11-03 20:16:59');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(50) DEFAULT NULL,
  `type_of_product` varchar(255) DEFAULT NULL,
  `location` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`id`, `name`, `email`, `phone_number`, `type_of_product`, `location`, `created_at`, `updated_at`) VALUES
(1, 'Vibol', 'vibol@example.com', '123456789', 'Beverage', 'Siem Reap', '2025-11-06 10:05:31', '2025-11-06 10:05:31'),
(2, 'Tybo', 'Tybo@example.com', '123456789', 'Meats', 'Phnom Penh', '2025-11-06 18:43:27', '2025-11-06 18:43:27'),
(3, 'Kruy', 'Kruy@example.com', '123456789', 'Coffees & Teas', 'Siem Reap', '2025-11-06 18:44:10', '2025-11-06 18:45:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hero_section`
--
ALTER TABLE `hero_section`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_orders_user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_items_product` (`product_id`),
  ADD KEY `idx_order_items_order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `hero_section`
--
ALTER TABLE `hero_section`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
