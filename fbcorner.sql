-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2022 at 03:44 PM
-- Server version: 10.4.13-MariaDB
-- PHP Version: 7.4.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fbcorner`
--

-- DELIMITER $$
-- --
-- -- Functions
-- --
-- CREATE DEFINER=`root`@`localhost` FUNCTION `today_opening_closing_time` (`details` TEXT, `index_name` TEXT) RETURNS TEXT CHARSET utf8mb4 BEGIN
--     DECLARE array_index, result TEXT;
--     SET array_index = CONCAT('$.',DAYNAME(NOW()),'.',index_name);
--     SET result = JSON_EXTRACT(details, array_index);
--     SET result = SUBSTRING_INDEX(result, '"', 2);
--     SET result = SUBSTRING(result, 2);
--     RETURN result;
-- END$$

-- DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `cus_id` varchar(5) NOT NULL,
  `rest_id` varchar(5) NOT NULL,
  `item_quantity` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`item_quantity`)),
  `placed` int(11) NOT NULL,
  `modified_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `cus_id`, `rest_id`, `item_quantity`, `placed`, `modified_date`) VALUES
(1, 'C1', 'R1', '{\"1\":\"1\"}', 1, '2022-06-15 12:57:12'),
(2, 'C1', 'R1', '{\"1\":\"1\"}', 1, '2022-06-24 11:33:04'),
(3, 'C1', 'R1', '{\"1\":\"1\"}', 1, '2022-06-24 11:41:04'),
(4, 'C1', 'R1', '{\"1\":\"1\"}', 1, '2022-06-24 15:42:00'),
(5, 'C9', 'R1', '{\"2\":\"2\"}', 1, '2022-06-24 13:57:14'),
(6, 'C1', 'R1', '{\"1\":\"1\"}', 1, '2022-06-24 21:56:58'),
(7, 'C1', 'R3', '{\"7\":\"1\"}', 0, '2022-06-24 21:45:21'),
(8, 'C1', 'R1', '{\"1\":\"1\"}', 0, '2022-06-25 12:57:14'),
(9, 'C10', 'R4', '{\"11\":\"1\"}', 0, '2022-07-01 16:07:07'),
(10, 'C10', 'R2', '{\"4\":\"1\"}', 0, '2022-07-01 16:07:20'),
(11, 'C2', 'R1', '{\"1\":\"2\"}', 0, '2022-07-01 16:10:37'),
(12, 'C3', 'R2', '{\"4\":\"1\"}', 0, '2022-07-01 16:11:58'),
(13, 'C4', 'R3', '{\"7\":\"1\"}', 0, '2022-07-01 16:12:53'),
(14, 'C5', 'R5', '{\"9\":\"1\"}', 0, '2022-07-01 16:13:55');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `section` varchar(100) NOT NULL,
  `category_name` varchar(300) NOT NULL,
  `code` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `section`, `category_name`, `code`) VALUES
(1, 'inventory', 'meat', 'M'),
(2, 'inventory', 'dairy', 'D'),
(3, 'inventory', 'dry goods', 'DG'),
(4, 'inventory', 'seafood', 'SF'),
(5, 'inventory', 'condiments', 'C'),
(6, 'inventory', 'fruits', 'F'),
(7, 'inventory', 'canned foods', 'CF'),
(8, 'inventory', 'vegetables', 'V'),
(9, 'inventory', 'frozen food', 'FF'),
(10, 'inventory', 'beer', 'B'),
(11, 'inventory', 'sauces', 'S'),
(12, 'inventory', 'wine', 'W'),
(13, 'inventory', 'snacks', 'SN'),
(14, 'inventory', 'liquor', 'L'),
(15, 'inventory', 'soft drinks', 'SD'),
(16, 'food_menu', 'western', 'W'),
(17, 'food_menu', 'chinese', 'C'),
(18, 'food_menu', 'breakfast', 'B'),
(19, 'food_menu', 'pasta', 'P'),
(20, 'food_menu', 'Noodles', 'N'),
(21, 'food_menu', 'Rice', 'R'),
(22, 'food_menu', 'Salad', 'S'),
(23, 'inventory', 'bread', 'BD'),
(24, 'food_menu', 'Pasta', 'PT'),
(25, 'food_menu', 'Fish', 'F'),
(26, 'food_menu', 'Chicken', 'CK'),
(27, 'food_menu', 'Sushi', 'SS');

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE `chat` (
  `chat_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` varchar(5) NOT NULL,
  `receiver_id` varchar(5) NOT NULL,
  `message` text DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `seen` tinyint(1) NOT NULL DEFAULT 0,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `chat`
--

INSERT INTO `chat` (`chat_id`, `conversation_id`, `sender_id`, `receiver_id`, `message`, `file`, `seen`, `sent_at`) VALUES
(1, 1, 'R1', 'C1', 'Good Afternoon', NULL, 1, '2022-06-23 19:50:12'),
(2, 1, 'R1', 'C1', NULL, 'R1-Food1.jpg', 1, '2022-06-23 19:52:30'),
(3, 1, 'R1', 'C1', 'Your food has already prepared', NULL, 1, '2022-06-23 19:52:30'),
(5, 1, 'C1', 'R1', 'Ok Thanks', NULL, 1, '2022-06-23 19:53:52'),
(7, 2, 'C1', 'R2', 'Hi', NULL, 1, '2022-06-24 14:44:55'),
(8, 2, 'C1', 'R2', NULL, 'C1-salad.png', 1, '2022-06-24 14:52:36'),
(9, 2, 'R2', 'C1', 'Hello', NULL, 1, '2022-06-24 15:00:17'),
(10, 3, 'R1', 'C6', 'Hello', NULL, 0, '2022-06-24 22:31:11'),
(11, 2, 'R2', 'C1', 'Hi Pei Ni, how can I help you?', NULL, 1, '2022-06-25 13:38:17'),
(12, 4, 'C1', 'R3', 'Hi', NULL, 1, '2022-07-01 15:14:23'),
(13, 4, 'R3', 'C1', 'Hi, Pei Ni. How can I help you?', NULL, 0, '2022-07-01 15:15:04');

-- --------------------------------------------------------

--
-- Table structure for table `conversation`
--

CREATE TABLE `conversation` (
  `conversation_id` int(11) NOT NULL,
  `cus_id` varchar(5) NOT NULL,
  `rest_id` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `conversation`
--

INSERT INTO `conversation` (`conversation_id`, `cus_id`, `rest_id`) VALUES
(1, 'C1', 'R1'),
(2, 'C1', 'R2'),
(3, 'C6', 'R1'),
(4, 'C1', 'R3');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `cus_id` varchar(5) NOT NULL,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `email` varchar(96) NOT NULL,
  `contact_num` varchar(12) NOT NULL,
  `password` varchar(1000) NOT NULL,
  `profile_pic` varchar(255) NOT NULL DEFAULT 'default_pp.png',
  `join_date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`cus_id`, `firstname`, `lastname`, `email`, `contact_num`, `password`, `profile_pic`, `join_date`) VALUES
('C1', 'Pei Ni', 'Lim', 'peinilim1211@gmail.com', '011-1111111', '$2y$10$qYAbpVtA57DTapcJP35A6ORIRVOv6poec2VBYqWuq9P213AYLoNom', 'C1.jpg', '2022-06-15'),
('C10', 'Janet', '', 'janet@gmail.com', '015-98753215', '$2y$10$p3kJYbiiZJuKPtRc52phLuuyyri6nMwVYYg6pwo.TWvasZvgNBhHi', 'C10.jpg', '2022-06-22'),
('C2', 'Jenny', 'Lim', 'jennylim@gmail.com', '012-2222222', '$2y$10$UCuILmuxh6u08dXWpn4l6.xww.2J.6zNZgDk1jZJK7xxmlQDOj9DG', 'C2.jpg', '2022-06-22'),
('C3', 'Kenny', '', 'kenny@gmail.com', '013-3333333', '$2y$10$loakEqIUPKMuFsCS5OjfMumrZ7Nrvm1vPaZWlIg.bLXyk0RE6HMc6', 'C3.png', '2022-06-22'),
('C4', 'Sharon', 'Tang', 'sharon@gmail.com', '014-4444444', '$2y$10$3FLmg1hmxFIwIDKY6RXQ..C.acBWfyQYL3fNyPfN.JIw/Q56kzc5.', 'C4.png', '2022-06-22'),
('C5', 'Kelly', 'Leong', 'kelly@gmail.com', '015-5555555', '$2y$10$EwJp7lxWbxqYXWF8JTSP3uqzhXseWR43c/Zh2GGivuSA5IOrFqjOe', 'default_pp.png', '2022-06-22'),
('C6', 'Daniel', 'Tan', 'daniel@gmail.com', '016-6666666', '$2y$10$JyIX.ZkoLcDyDebVQQJ85.ZS3kGpyY39kEfbHWGlHtg7d5u7MYNk.', 'default_pp.png', '2022-06-22'),
('C7', 'Michael', '', 'michael@gmail.com', '017-7777777', '$2y$10$LrmPkDpFCmjSFZsfB48QOeMOnVACCJbuViisd8JTyctrGS6UPunGi', 'C7.jpg', '2022-06-22'),
('C8', 'Karen', '', 'karen@gmail.com', '018-8888888', '$2y$10$83tvrog/lxKkAWXes0He3u0oK34sq8KtSKRCX11Rwsuv.8T8kjwMi', 'default_pp.png', '2022-06-22'),
('C9', 'Joey', '', 'joey@gmail.com', '019-9999999', '$2y$10$e3.LxJmA8rvD/jkzBCrDmu1lnpXTNqLsJGgdc7WRwBIT6wzzSmnKC', 'C9.jpg', '2022-06-22');

-- --------------------------------------------------------

--
-- Table structure for table `cus_points`
--

CREATE TABLE `cus_points` (
  `cus_points_id` int(11) NOT NULL,
  `cus_id` varchar(5) NOT NULL,
  `points` int(11) NOT NULL DEFAULT 0,
  `expired_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cus_points`
--

INSERT INTO `cus_points` (`cus_points_id`, `cus_id`, `points`, `expired_date`) VALUES
(1, 'C1', 476, '2022-09-15'),
(2, 'C2', 53, '2022-09-22'),
(3, 'C3', 0, '2022-09-22'),
(4, 'C4', 24, '2022-09-22'),
(5, 'C5', 42, '2022-09-22'),
(6, 'C6', 15, '2022-09-22'),
(7, 'C7', 33, '2022-09-22'),
(8, 'C8', 44, '2022-09-22'),
(9, 'C9', 106, '2022-09-22'),
(10, 'C10', 39, '2022-09-22');

-- --------------------------------------------------------

--
-- Table structure for table `cus_promo_redemption`
--

CREATE TABLE `cus_promo_redemption` (
  `cus_promo_redemption_id` int(11) NOT NULL,
  `cus_id` varchar(5) NOT NULL,
  `promotion_id` int(11) NOT NULL,
  `redemption_left` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cus_promo_redemption`
--

INSERT INTO `cus_promo_redemption` (`cus_promo_redemption_id`, `cus_id`, `promotion_id`, `redemption_left`) VALUES
(2, 'C9', 2, 0);

-- --------------------------------------------------------

--
-- Table structure for table `cus_rest_history`
--

CREATE TABLE `cus_rest_history` (
  `cus_rest_history_id` int(11) NOT NULL,
  `cus_id` varchar(5) NOT NULL,
  `rest_id` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cus_rest_history`
--

INSERT INTO `cus_rest_history` (`cus_rest_history_id`, `cus_id`, `rest_id`) VALUES
(1, 'C1', 'R1'),
(2, 'C6', 'R1'),
(3, 'C10', 'R1'),
(4, 'C2', 'R1'),
(5, 'C8', 'R1'),
(6, 'C9', 'R1'),
(7, 'C5', 'R1'),
(8, 'C7', 'R1'),
(9, 'C4', 'R1'),
(10, 'C6', 'R3'),
(11, 'C10', 'R3'),
(12, 'C2', 'R3'),
(13, 'C9', 'R3'),
(14, 'C8', 'R3'),
(15, 'C5', 'R3'),
(16, 'C3', 'R3'),
(17, 'C7', 'R3'),
(18, 'C1', 'R3'),
(19, 'C4', 'R3'),
(20, 'C2', 'R5'),
(21, 'C9', 'R5'),
(22, 'C1', 'R5'),
(23, 'C10', 'R5'),
(24, 'C8', 'R5'),
(25, 'C2', 'R2'),
(26, 'C9', 'R2'),
(27, 'C7', 'R2'),
(28, 'C5', 'R2'),
(29, 'C10', 'R2'),
(30, 'C2', 'R4'),
(31, 'C9', 'R4'),
(32, 'C1', 'R4'),
(33, 'C10', 'R4');

-- --------------------------------------------------------

--
-- Table structure for table `dining_style`
--

CREATE TABLE `dining_style` (
  `dining_style_id` int(11) NOT NULL,
  `dining_style` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `dining_style`
--

INSERT INTO `dining_style` (`dining_style_id`, `dining_style`) VALUES
(1, 'Casual Dining'),
(2, 'Elegant Dining'),
(3, 'Fine Dining'),
(4, 'Casual Elegant'),
(5, 'Fast Food');

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `event_id` int(11) NOT NULL,
  `rest_id` varchar(5) NOT NULL,
  `title` varchar(100) NOT NULL,
  `closed_type` tinyint(1) NOT NULL DEFAULT 0,
  `repeat_days` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`repeat_days`)),
  `all_day` tinyint(1) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `color` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`event_id`, `rest_id`, `title`, `closed_type`, `repeat_days`, `all_day`, `start_date`, `end_date`, `start_time`, `end_time`, `color`) VALUES
(1, 'R1', 'Restaurant Closed', 1, NULL, 1, '2022-06-07', '2022-06-09', NULL, NULL, '#bf94ff'),
(4, 'R3', 'Restaurant Closed', 1, NULL, 1, '2022-07-08', '2022-07-09', NULL, NULL, '#ff0033'),
(5, 'R5', 'Restaurant Closed', 1, '[\"6\"]', 1, '2022-07-02', '2022-07-30', NULL, NULL, '#d7bdff'),
(6, 'R2', 'Restaurant Closed', 1, NULL, 0, '2022-07-04', '2022-07-04', '17:00:00', '18:00:00', '#f5ae14'),
(7, 'R4', 'Restaurant Closed', 1, NULL, 1, '2022-07-01', '2022-07-01', NULL, NULL, '#563d7c');

-- --------------------------------------------------------

--
-- Table structure for table `favourite_restaurant`
--

CREATE TABLE `favourite_restaurant` (
  `cus_id` varchar(5) NOT NULL,
  `rest_id` varchar(5) NOT NULL,
  `added_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `favourite_restaurant`
--

INSERT INTO `favourite_restaurant` (`cus_id`, `rest_id`, `added_date`) VALUES
('C1', 'R4', '2022-06-24 19:04:01'),
('C1', 'R5', '2022-06-24 19:03:40'),
('C10', 'R1', '2022-07-01 16:07:33'),
('C10', 'R4', '2022-07-01 16:07:42'),
('C10', 'R5', '2022-07-01 16:07:45'),
('C3', 'R2', '2022-07-01 16:11:40'),
('C4', 'R3', '2022-07-01 16:12:44'),
('C5', 'R5', '2022-07-01 16:15:06'),
('C9', 'R5', '2022-06-24 19:04:38');

-- --------------------------------------------------------

--
-- Table structure for table `food_item`
--

CREATE TABLE `food_item` (
  `item_id` int(11) NOT NULL,
  `rest_id` varchar(5) NOT NULL,
  `image` varchar(255) NOT NULL,
  `item_code` varchar(7) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `category_id` int(11) NOT NULL,
  `price` double NOT NULL,
  `ingredients` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`ingredients`)),
  `status` int(11) NOT NULL,
  `unavailable_condition` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' CHECK (json_valid(`unavailable_condition`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `food_item`
--

INSERT INTO `food_item` (`item_id`, `rest_id`, `image`, `item_code`, `item_name`, `description`, `category_id`, `price`, `ingredients`, `status`, `unavailable_condition`) VALUES
(1, 'R1', 'R1(C-1).jpg', 'N-1', 'Dried Chili Pan Mee', 'The noodles are tossed in spicy umami sauce and topped with ground meat, poached eggs, and crispy anchovies. The spicy umami sauce is made with tons of dried chili, garlic, dried shrimp, and seasonings.', 20, 12.5, '{\"2\":\"1\",\"1\":\"3\"}', 1, '[]'),
(2, 'R1', 'R1(N-2).png', 'N-2', 'Pan Mee Soup', 'Black mushrooms, fried anchovy, potato leaves, boiled pork, soft-boiled egg and green onions are added on top of the pan mee.', 20, 11.3, '{\"3\":\"1\",\"2\":\"1\"}', 1, '[]'),
(3, 'R1', 'R1(R-1).jpg', 'R-1', 'Chicken Fried Rice', 'Our chicken fried rice is packed with colorful vegetables, chicken, and eggs.', 21, 10.8, '{\"2\":\"2\"}', 0, '{\"disabled\":\"true\"}'),
(4, 'R2', 'R2(S-1).png', 'S-1', 'Green Salads', 'A Healthy salad bowl with different lettuce, cheese and croutons.', 22, 18.5, '{\"5\":\"2\",\"4\":\"15\"}', 1, '[]'),
(5, 'R3', 'R3(PT-1).png', 'PT-1', 'Lasagna', 'A rich and creamy whole-wheat pasta dish filled layer by layer with refreshingly fresh onions and garlic, lathered in a succulent sauce and topped with imported, premium quality mozzarella.', 24, 20.7, '{\"8\":\"2\"}', 1, '[]'),
(6, 'R3', 'R3(F-1).jpg', 'F-1', 'Fish & Chips', 'Fish and chips consists of fried fish in crispy batter and is served with chips.', 25, 12.5, '{\"7\":\"1\"}', 1, '[]'),
(7, 'R3', 'R3(CK-1).png', 'CK-1', 'Black Pepper Chicken Chop', 'Delicious pan-fried marinated chicken covered in a rich, bold black pepper sauce.', 26, 15.2, '{\"6\":\"1\"}', 1, '[]'),
(8, 'R5', 'R5(SS-1).png', 'SS-1', 'Sushi Rolls', 'The japanese sushi food. Maki ands rolls with tuna, salmon, shrimp, crab and avocado. Top view of assorted sushi. Rainbow sushi roll, uramaki, hosomaki and nigiri.', 27, 23, '[]', 1, '[]'),
(9, 'R5', 'R5(SS-2).png', 'SS-2', 'Salmon sushi', 'Japanese cuisine. Salmon sushi nigiri on a black plate with chopsticks.', 27, 9, '[]', 1, '[]'),
(10, 'R5', 'R5(SS-3).png', 'SS-3', 'Tamagoyaki', 'Tamagoyaki nigiri sushi. Japanese sushi on a sushi wooden tray.', 27, 3, '[]', 1, '[]'),
(11, 'R4', 'R4(CK-1).png', 'CK-1', 'Baked Chicken', 'Baked whole chicken with mushrooms and potatoes close-up on a plate on a table. Horizontal top view from above', 26, 25, '[]', 1, '[]'),
(12, 'R1', 'R1(N-3).png', 'N-3', 'Ipoh Kway Teow Soup', 'Flat rice noodles in chicken soup, topped with chives, spring onions and bean sprouts.', 20, 12.5, '[]', 1, '[]');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `rest_id` varchar(5) NOT NULL,
  `item_code` varchar(7) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category_id` int(11) NOT NULL,
  `current_stock` double NOT NULL,
  `current_stock_2` int(11) NOT NULL DEFAULT 0,
  `reorder_level` double NOT NULL,
  `reorder_level_2` int(11) NOT NULL DEFAULT 0,
  `total_stock` double NOT NULL,
  `unit` varchar(10) NOT NULL,
  `unit_2` varchar(10) NOT NULL,
  `unit_convert_qty1` double NOT NULL,
  `unit_convert_qty2` double NOT NULL,
  `status` int(11) NOT NULL,
  `disabled` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `rest_id`, `item_code`, `item_name`, `category_id`, `current_stock`, `current_stock_2`, `reorder_level`, `reorder_level_2`, `total_stock`, `unit`, `unit_2`, `unit_convert_qty1`, `unit_convert_qty2`, `status`, `disabled`) VALUES
(1, 'R1', 'SF-1', 'Dry Shrimp', 4, 1, 367, 0, 10, 2, 'kg', 'pcs', 1, 400, 1, 0),
(2, 'R1', 'D-1', 'Egg', 2, 13, 25, 1, 0, 15, 'tray', 'pcs', 1, 30, 1, 0),
(3, 'R1', 'DG-1', 'Black Mushrooms', 3, 29, 59, 0, 30, 30, 'packet', 'pcs', 1, 60, 1, 0),
(4, 'R2', 'V-1', 'Lettuce', 8, 7, 15, 2, 0, 10, 'head', 'pcs', 1, 30, 1, 0),
(5, 'R2', 'D-1', 'Cheese', 2, 5, 0, 1, 0, 10, 'block', 'cup', 1, 2, 1, 0),
(6, 'R3', 'M-1', 'Chicken', 1, 19, 15, 1, 0, 20, 'packet', 'pcs', 1, 20, 1, 0),
(7, 'R3', 'SF-1', 'Fish', 4, 14, 8, 1, 0, 15, 'packet', 'pcs', 1, 10, 1, 0),
(8, 'R3', 'D-1', 'Cheese', 2, 9, 0, 1, 0, 10, 'block', 'cup', 1, 2, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `login_activity`
--

CREATE TABLE `login_activity` (
  `login_activity_id` int(11) NOT NULL,
  `user_id` varchar(5) NOT NULL,
  `login_time` datetime NOT NULL DEFAULT current_timestamp(),
  `logout_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `login_activity`
--

INSERT INTO `login_activity` (`login_activity_id`, `user_id`, `login_time`, `logout_time`) VALUES
(1, '1', '2022-06-14 22:11:26', '2022-06-14 22:59:42'),
(2, '1', '2022-06-14 23:04:58', '2022-06-14 23:05:39'),
(3, '1', '2022-06-14 23:05:58', '2022-06-14 23:06:19'),
(4, '1', '2022-06-14 23:07:39', '2022-06-14 23:08:00'),
(5, '1', '2022-06-14 23:08:49', '2022-06-14 23:09:00'),
(6, '1', '2022-06-14 23:09:28', '2022-06-14 23:09:48'),
(7, '1', '2022-06-15 00:52:07', '2022-06-15 03:48:56'),
(8, '1', '2022-06-15 11:03:34', '2022-06-15 11:17:22'),
(9, '1', '2022-06-15 11:19:28', '2022-06-15 11:19:57'),
(10, 'C1', '2022-06-15 11:33:57', '2022-06-15 11:36:13'),
(11, 'C1', '2022-06-15 11:37:02', '2022-06-15 11:37:37'),
(12, 'C1', '2022-06-15 12:25:21', '2022-06-15 12:46:16'),
(13, '1', '2022-06-15 12:25:32', '2022-06-15 12:46:16'),
(14, 'C1', '2022-06-15 12:49:23', '2022-06-15 13:18:16'),
(15, 'C1', '2022-06-19 04:13:07', '2022-06-19 04:41:14'),
(16, '1', '2022-06-19 04:40:07', '2022-06-19 04:41:10'),
(17, '1', '2022-06-20 23:20:27', '2022-06-20 23:24:24'),
(18, '1', '2022-06-20 23:33:42', '2022-06-20 23:33:57'),
(19, 'C1', '2022-06-20 23:34:35', '2022-06-20 23:34:49'),
(20, '1', '2022-06-21 04:01:45', '2022-06-21 04:47:28'),
(21, 'C10', '2022-06-22 03:36:49', '2022-06-22 03:37:02'),
(22, '2', '2022-06-22 03:46:28', '2022-06-22 04:39:08'),
(23, '3', '2022-06-22 09:46:34', '2022-06-22 10:20:55'),
(24, '3', '2022-06-22 11:02:33', '2022-06-22 11:12:36'),
(25, '4', '2022-06-22 11:18:26', '2022-06-22 11:19:28'),
(26, '5', '2022-06-22 11:21:20', '2022-06-22 11:23:57'),
(27, '1', '2022-06-23 13:53:03', '2022-06-23 16:31:00'),
(28, '1', '2022-06-23 16:34:04', '2022-06-23 20:36:27'),
(29, 'C1', '2022-06-23 18:35:46', '2022-06-23 18:40:42'),
(30, 'C2', '2022-06-23 18:40:52', '2022-06-23 18:45:47'),
(31, 'C1', '2022-06-23 18:46:08', '2022-06-23 20:36:27'),
(32, '1', '2022-06-23 20:42:15', '2022-06-23 22:47:54'),
(33, 'C9', '2022-06-23 22:09:44', '2022-06-23 22:30:30'),
(34, '1', '2022-06-23 22:51:34', '2022-06-24 01:00:26'),
(35, '1', '2022-06-24 01:01:21', '2022-06-24 02:00:36'),
(36, 'C1', '2022-06-24 02:49:20', '2022-06-24 04:34:02'),
(37, 'C1', '2022-06-24 10:30:25', '2022-06-24 12:37:01'),
(38, '1', '2022-06-24 11:13:03', '2022-06-24 11:33:46'),
(39, 'C9', '2022-06-24 13:54:45', '2022-06-24 14:13:07'),
(40, 'C1', '2022-06-24 14:13:18', '2022-06-24 16:24:52'),
(41, '2', '2022-06-24 14:59:37', '2022-06-24 15:16:53'),
(42, 'C4', '2022-06-24 16:56:17', '2022-06-24 18:29:50'),
(43, '1', '2022-06-24 17:38:59', '2022-06-24 18:03:50'),
(44, 'C1', '2022-06-24 19:03:08', '2022-06-24 19:04:25'),
(45, 'C9', '2022-06-24 19:04:33', '2022-06-24 19:30:09'),
(46, 'C9', '2022-06-24 21:04:22', '2022-06-24 21:07:54'),
(47, 'C1', '2022-06-24 21:08:04', '2022-06-24 22:06:44'),
(48, '1', '2022-06-24 22:25:21', '2022-06-24 23:02:53'),
(49, 'C1', '2022-06-25 12:03:33', '2022-06-25 12:36:46'),
(50, 'C1', '2022-06-25 12:56:20', '2022-06-25 14:16:00'),
(51, '1', '2022-06-25 13:09:52', '2022-06-25 13:31:46'),
(52, '2', '2022-06-25 13:37:04', '2022-06-25 14:09:33'),
(53, '4', '2022-06-25 14:09:48', '2022-06-25 14:16:08'),
(54, '1', '2022-06-25 19:25:29', '2022-06-25 20:05:58'),
(55, '1', '2022-06-25 22:17:31', '2022-06-25 22:37:44'),
(56, '1', '2022-06-25 22:45:47', '2022-06-25 23:08:13'),
(57, '1', '2022-06-30 19:10:35', '2022-06-30 19:24:13'),
(58, '1', '2022-07-01 11:49:25', '2022-07-01 12:57:17'),
(59, 'C1', '2022-07-01 11:49:53', '2022-07-01 12:25:29'),
(60, '2', '2022-07-01 14:17:15', '2022-07-01 14:17:55'),
(61, '3', '2022-07-01 14:18:10', '2022-07-01 15:15:58'),
(62, 'C1', '2022-07-01 15:10:43', '2022-07-01 15:17:12'),
(63, '5', '2022-07-01 15:16:34', '2022-07-01 15:34:17'),
(64, '2', '2022-07-01 15:34:28', '2022-07-01 15:42:10'),
(65, '4', '2022-07-01 15:42:26', '2022-07-01 15:52:27'),
(66, 'C10', '2022-07-01 16:06:34', '2022-07-01 16:09:54'),
(67, 'C2', '2022-07-01 16:10:17', '2022-07-01 16:11:22'),
(68, 'C3', '2022-07-01 16:11:35', '2022-07-01 16:12:09'),
(69, 'C4', '2022-07-01 16:12:38', '2022-07-01 16:13:15'),
(70, 'C5', '2022-07-01 16:13:23', '2022-07-01 16:15:12'),
(71, '1', '2022-07-01 16:17:01', '2022-07-01 16:19:33'),
(72, '1', '2022-07-01 17:09:34', '2022-07-01 17:12:14'),
(73, 'C1', '2022-07-01 18:01:43', '2022-07-01 18:03:19'),
(74, '1', '2022-07-01 18:03:29', '2022-07-01 18:05:45');

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `order_id` int(11) NOT NULL,
  `rest_id` varchar(5) NOT NULL,
  `cus_id` varchar(5) NOT NULL,
  `table_id` int(11) NOT NULL,
  `pickup_time` datetime DEFAULT NULL,
  `item_quantity` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`item_quantity`)),
  `voucher_id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `status` varchar(100) NOT NULL,
  `additional_notes` varchar(1000) NOT NULL,
  `remarks` varchar(1000) NOT NULL,
  `promotions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`promotions`)),
  `created_date` datetime NOT NULL DEFAULT current_timestamp(),
  `modified_date` datetime NOT NULL DEFAULT current_timestamp(),
  `deleted_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`order_id`, `rest_id`, `cus_id`, `table_id`, `pickup_time`, `item_quantity`, `voucher_id`, `reservation_id`, `status`, `additional_notes`, `remarks`, `promotions`, `created_date`, `modified_date`, `deleted_data`) VALUES
(1, 'R1', 'C1', 2, NULL, '{\"1\":\"1\",\"3\":\"1\"}', 0, 1, 'Cancelled', '', 'Reservation was cancelled', NULL, '2022-06-15 12:51:46', '2022-06-23 17:05:42', '[]'),
(2, 'R1', 'C1', 1, NULL, '{\"1\":\"2\"}', 0, 0, 'Completed', '', '', '{\"promo_code\":{\"id\":\"2\",\"promo_code_name\":\"ENJOY\",\"actual_discount\":\"5.00\"},\"0\":{\"type_code\":\"percent_off\",\"settings_data\":{\"percentage\":\"20\"},\"food_items\":[\"1\"]}}', '2022-06-15 12:57:12', '2022-06-19 04:40:27', '[]'),
(5, 'R1', 'C6', 1, NULL, '{\"3\":\"1\"}', 0, 3, 'Cancelled', '', 'Reservation was cancelled', NULL, '2022-06-23 17:15:26', '2022-06-23 17:17:09', '[]'),
(6, 'R1', 'C2', 3, NULL, '{\"1\":\"3\"}', 0, 5, 'Cancelled', '', '', NULL, '2022-06-23 17:41:40', '2022-06-23 17:45:43', '[]'),
(7, 'R1', 'C2', 4, NULL, '{\"3\":\"1\"}', 0, 0, 'Completed', '', '', '[{\"type_code\":\"percent_off\",\"settings_data\":{\"percentage\":\"20\"},\"food_items\":[\"3\"]}]', '2022-06-23 17:47:01', '2022-06-23 18:08:11', '[]'),
(10, 'R1', 'C8', 0, '2022-06-23 20:15:00', '{\"3\":\"1\"}', 0, 0, 'Cancelled', '', '', NULL, '2022-06-23 19:15:10', '2022-06-23 19:27:36', '[]'),
(11, 'R1', 'C8', 4, NULL, '{\"3\":\"3\"}', 0, 6, 'Completed', '', '', '[{\"type_code\":\"percent_off\",\"settings_data\":{\"percentage\":\"20\"},\"food_items\":[\"3\"]}]', '2022-06-23 19:22:04', '2022-06-23 19:28:04', '[]'),
(12, 'R1', 'C9', 3, NULL, '{\"3\":\"3\"}', 0, 0, 'Completed', '', '', '{\"promo_code\":{\"id\":\"2\",\"promo_code_name\":\"ENJOY\",\"actual_discount\":\"5.00\"},\"0\":{\"type_code\":\"percent_off\",\"settings_data\":{\"percentage\":\"20\"},\"food_items\":[\"3\"]}}', '2022-06-23 21:50:06', '2022-06-23 22:16:29', '[]'),
(13, 'R1', 'C6', 1, NULL, '{\"3\":\"1\"}', 0, 7, 'Disabled', '', 'Reservation has been disabled', NULL, '2022-06-23 23:38:17', '2022-06-23 23:38:53', '[]'),
(14, 'R1', 'C5', 3, NULL, '{\"3\":\"1\"}', 0, 8, 'Disabled', '', 'Reservation has been disabled', NULL, '2022-06-23 23:43:09', '2022-06-23 23:43:41', '[]'),
(15, 'R1', 'C7', 20, NULL, '{\"2\":\"1\"}', 0, 9, 'Disabled', '', 'Reservation has been disabled', NULL, '2022-06-23 23:53:23', '2022-06-24 00:03:46', '{\"table_num_name\":\"T07\"}'),
(16, 'R1', 'C4', 20, NULL, '{\"1\":\"1\"}', 0, 0, 'Completed', '', '', '[{\"type_code\":\"percent_off\",\"settings_data\":{\"percentage\":\"20\"},\"food_items\":[\"1\"]}]', '2022-06-23 23:54:11', '2022-06-23 23:54:11', '{\"table_num_name\":\"T07\"}'),
(17, 'R1', 'C6', 6, NULL, '{\"1\":\"1\"}', 0, 10, 'Disabled', '', 'Reservation has been disabled', NULL, '2022-06-24 00:19:22', '2022-06-24 00:20:30', '[]'),
(18, 'R1', 'C8', 5, NULL, '{\"1\":\"1\"}', 0, 11, 'Disabled', '', 'Reservation has been disabled', NULL, '2022-06-24 00:22:31', '2022-06-24 00:23:16', '[]'),
(19, 'R1', 'C8', 2, NULL, '{\"3\":\"1\"}', 0, 12, 'Disabled', '', 'Chicken Fried Rice is unavailable', NULL, '2022-06-24 01:24:05', '2022-06-24 01:53:33', '[]'),
(20, 'R1', 'C9', 5, NULL, '{\"3\":\"1\"}', 0, 0, 'Disabled', '', 'Chicken Fried Rice is unavailable', NULL, '2022-06-24 01:35:33', '2022-06-24 01:37:37', '[]'),
(21, 'R1', 'C10', 3, NULL, '{\"2\":\"1\"}', 0, 0, 'Disabled', '', 'Pan Mee Soup is unavailable', NULL, '2022-06-24 01:40:22', '2022-06-24 01:40:44', '[]'),
(22, 'R1', 'C2', 1, NULL, '{\"3\":\"1\"}', 0, 0, 'Disabled', '', 'Chicken Fried Rice is unavailable', NULL, '2022-06-24 01:52:24', '2022-06-24 01:53:26', '[]'),
(23, 'R1', 'C1', 1, NULL, '{\"1\":\"1\",\"2\":\"1\"}', 0, 13, 'Cancelled', '', 'Reservation was cancelled', NULL, '2022-06-24 03:31:23', '2022-06-24 10:48:31', '[]'),
(24, 'R1', 'C1', 1, NULL, '{\"1\":\"1\"}', 0, 14, 'Cancelled', '', '', NULL, '2022-06-24 11:33:04', '2022-06-24 11:36:34', '[]'),
(25, 'R1', 'C1', 1, NULL, '{\"2\":\"3\"}', 0, 14, 'Cancelled', '', '', NULL, '2022-06-24 11:41:04', '2022-06-24 14:15:34', '[]'),
(26, 'R1', 'C9', 2, NULL, '{\"1\":\"2\",\"2\":\"2\"}', 0, 0, 'Cancelled', '', '', NULL, '2022-06-24 13:57:14', '2022-06-24 14:12:45', '[]'),
(27, 'R1', 'C1', 1, NULL, '{\"2\":\"3\"}', 0, 14, 'Cancelled', '', 'Reservation was cancelled', NULL, '2022-06-24 15:42:00', '2022-06-24 21:56:25', '[]'),
(28, 'R1', 'C1', 5, NULL, '{\"1\":\"1\"}', 0, 15, 'Disabled', '', 'Dried Chili Pan Mee is unavailable', NULL, '2022-06-24 21:38:22', '2022-07-01 11:50:24', '[]'),
(29, 'R1', 'C1', 0, '2022-06-25 22:56:00', '{\"1\":\"1\"}', 0, 0, 'Completed', '', '', '[{\"type_code\":\"percent_off\",\"settings_data\":{\"percentage\":\"20\"},\"food_items\":[\"1\"]}]', '2022-06-24 21:56:58', '2022-06-25 13:10:04', '[]'),
(30, 'R1', 'C1', 0, '2022-06-25 15:10:00', '{\"1\":\"2\"}', 0, 0, 'Disabled', '', 'Dried Chili Pan Mee is unavailable', NULL, '2022-06-25 13:10:55', '2022-07-01 11:50:14', '[]'),
(31, 'R1', 'C1', 1, NULL, '{\"1\":\"3\"}', 2, 0, 'Completed', '', '', '[{\"type_code\":\"percent_off\",\"settings_data\":{\"percentage\":\"20\"},\"food_items\":[\"1\"]}]', '2022-07-01 12:03:54', '2022-07-01 12:20:27', '[]'),
(32, 'R1', 'C1', 5, NULL, '{\"1\":\"1\"}', 0, 15, 'Completed', '', '', '[{\"type_code\":\"percent_off\",\"settings_data\":{\"percentage\":\"20\"},\"food_items\":[\"1\"]}]', '2022-07-01 12:18:41', '2022-07-01 12:21:38', '[]'),
(33, 'R1', 'C1', 1, NULL, '{\"2\":\"2\"}', 0, 16, 'Cancelled', '', 'Reservation was cancelled', NULL, '2022-07-01 12:23:29', '2022-07-01 16:17:56', '[]'),
(34, 'R3', 'C6', 12, NULL, '{\"7\":\"1\"}', 0, 17, 'Completed', '', '', '[{\"type_code\":\"dollar_dis\",\"settings_data\":{\"discounted_price\":\"14.00\"},\"food_items\":[\"7\"]}]', '2022-07-01 14:45:31', '2022-07-01 14:45:47', '[]'),
(35, 'R3', 'C4', 13, NULL, '{\"7\":\"1\"}', 0, 26, 'Completed', '', '', '[{\"type_code\":\"dollar_dis\",\"settings_data\":{\"discounted_price\":\"14.00\"},\"food_items\":[\"7\"]}]', '2022-07-01 14:52:44', '2022-07-01 14:54:14', '[]'),
(36, 'R3', 'C5', 13, NULL, '{\"7\":\"3\"}', 0, 22, 'Completed', '', '', '[{\"type_code\":\"dollar_dis\",\"settings_data\":{\"discounted_price\":\"14.00\"},\"food_items\":[\"7\"]}]', '2022-07-01 14:53:02', '2022-07-01 14:54:03', '[]'),
(37, 'R3', 'C1', 15, NULL, '{\"6\":\"1\"}', 0, 25, 'Completed', '', '', '[{\"type_code\":\"dollar_dis\",\"settings_data\":{\"discounted_price\":\"12.00\"},\"food_items\":[\"6\"]}]', '2022-07-01 14:54:37', '2022-07-01 14:54:57', '[]'),
(38, 'R3', 'C9', 15, NULL, '{\"6\":\"1\",\"5\":\"1\"}', 0, 20, 'Completed', '', '', '[{\"type_code\":\"dollar_dis\",\"settings_data\":{\"discounted_price\":\"12.00\"},\"food_items\":[\"6\"]},{\"type_code\":\"dollar_dis\",\"settings_data\":{\"discounted_price\":\"18.00\"},\"food_items\":[\"5\"]}]', '2022-07-01 14:55:15', '2022-07-01 14:55:29', '[]'),
(39, 'R3', 'C10', 13, NULL, '{\"7\":\"1\"}', 0, 18, 'Disabled', '', 'Reservation has been disabled', NULL, '2022-07-01 14:55:51', '2022-07-01 14:57:16', '[]'),
(40, 'R3', 'C2', 17, NULL, '{\"6\":\"1\"}', 0, 19, 'Cancelled', '', 'Reservation was cancelled', NULL, '2022-07-01 14:56:11', '2022-07-01 14:56:34', '[]'),
(41, 'R3', 'C10', 12, NULL, '{\"6\":\"1\"}', 0, 0, 'Cancelled', '', '', NULL, '2022-07-01 14:57:50', '2022-07-01 14:57:50', '[]'),
(42, 'R3', 'C9', 0, '2022-07-01 16:00:00', '{\"6\":\"1\"}', 0, 0, 'Cancelled', '', '', NULL, '2022-07-01 14:58:28', '2022-07-01 14:58:28', '[]'),
(43, 'R5', 'C1', 22, NULL, '{\"8\":\"2\"}', 0, 29, 'Cancelled', '', 'Reservation was cancelled', NULL, '2022-07-01 15:29:31', '2022-07-01 15:29:49', '[]'),
(44, 'R5', 'C9', 23, NULL, '{\"8\":\"1\"}', 0, 28, 'Completed', '', '', NULL, '2022-07-01 15:32:03', '2022-07-01 15:32:16', '[]'),
(45, 'R5', 'C10', 0, '2022-07-01 16:30:00', '{\"8\":\"1\"}', 0, 0, 'Completed', '', '', NULL, '2022-07-01 15:32:51', '2022-07-01 15:32:51', '[]'),
(46, 'R5', 'C8', 21, NULL, '{\"10\":\"4\"}', 0, 0, 'Completed', '', '', NULL, '2022-07-01 15:33:14', '2022-07-01 15:33:14', '[]'),
(47, 'R2', 'C7', 8, NULL, '{\"4\":\"1\"}', 0, 32, 'Disabled', '', 'Reservation has been disabled', NULL, '2022-07-01 15:36:16', '2022-07-01 15:36:59', '[]'),
(48, 'R2', 'C9', 8, NULL, '{\"4\":\"2\"}', 0, 31, 'Completed', '', '', '[{\"type_code\":\"percent_off\",\"settings_data\":{\"percentage\":\"10\"},\"food_items\":[\"4\"]}]', '2022-07-01 15:36:36', '2022-07-01 15:36:46', '[]'),
(49, 'R2', 'C5', 0, '2022-07-01 18:00:00', '{\"4\":\"2\"}', 0, 0, 'Cancelled', '', '', NULL, '2022-07-01 15:37:29', '2022-07-01 15:37:29', '[]'),
(50, 'R2', 'C7', 10, NULL, '{\"4\":\"2\"}', 0, 0, 'Completed', '', '', '[{\"type_code\":\"percent_off\",\"settings_data\":{\"percentage\":\"10\"},\"food_items\":[\"4\"]}]', '2022-07-01 15:37:49', '2022-07-01 15:37:49', '[]'),
(51, 'R2', 'C10', 9, NULL, '{\"4\":\"1\"}', 0, 33, 'Cancelled', '', '', NULL, '2022-07-01 15:38:24', '2022-07-01 15:38:33', '[]'),
(52, 'R2', 'C10', 9, NULL, '{\"4\":\"1\"}', 0, 33, 'Completed', '', '', '[{\"type_code\":\"percent_off\",\"settings_data\":{\"percentage\":\"10\"},\"food_items\":[\"4\"]}]', '2022-07-01 15:39:07', '2022-07-01 15:39:15', '[]'),
(53, 'R4', 'C1', 24, NULL, '{\"11\":\"1\"}', 0, 36, 'Completed', '', '', NULL, '2022-07-01 15:50:10', '2022-07-01 15:50:46', '[]'),
(54, 'R4', 'C2', 26, NULL, '{\"11\":\"1\"}', 0, 0, 'Cancelled', '', '', NULL, '2022-07-01 15:51:05', '2022-07-01 15:51:05', '[]'),
(55, 'R4', 'C2', 24, NULL, '{\"11\":\"1\"}', 0, 0, 'Completed', '', '', NULL, '2022-07-01 15:51:19', '2022-07-01 15:51:19', '[]'),
(56, 'R4', 'C10', 24, NULL, '{\"11\":\"1\"}', 0, 37, 'Disabled', '', 'Reservation has been disabled', NULL, '2022-07-01 15:51:47', '2022-07-01 15:52:01', '[]');

-- --------------------------------------------------------

--
-- Table structure for table `permission`
--

CREATE TABLE `permission` (
  `permission_id` int(11) NOT NULL,
  `file_name` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `permission`
--

INSERT INTO `permission` (`permission_id`, `file_name`) VALUES
(1, 'dashboard/dashboard'),
(2, 'user/user_group'),
(3, 'user/user'),
(4, 'reservation/table'),
(5, 'reservation/time_slot'),
(6, 'reservation/reservation'),
(7, 'restaurant/restaurant'),
(8, 'inventory/inventory'),
(9, 'food_menu/food_menu'),
(10, 'order/order'),
(11, 'order/preorder'),
(12, 'promotion/promotion'),
(13, 'reviews/reviews'),
(14, 'message/message'),
(15, 'promotion/promotional_ads'),
(16, 'staff/staff'),
(17, 'customer/customer'),
(18, 'calendar/calendar');

-- --------------------------------------------------------

--
-- Table structure for table `promotion`
--

CREATE TABLE `promotion` (
  `promotion_id` int(11) NOT NULL,
  `rest_id` varchar(5) NOT NULL,
  `type_code` varchar(100) NOT NULL,
  `settings_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `food_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `description` varchar(2000) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_date` datetime NOT NULL DEFAULT current_timestamp(),
  `modified_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `promotion`
--

INSERT INTO `promotion` (`promotion_id`, `rest_id`, `type_code`, `settings_data`, `food_items`, `description`, `status`, `created_date`, `modified_date`) VALUES
(1, 'R1', 'percent_off', '{\"percentage\":\"20\"}', '[\"3\",\"1\",\"2\"]', NULL, 1, '2022-06-15 03:36:12', '2022-06-23 21:52:34'),
(2, 'R1', 'promo_code', '{\"promo_code_name\":\"ENJOY\",\"min_price\":\"20.00\",\"actual_discount\":\"5.00\",\"max_redemption\":\"3\"}', NULL, '- This offer entitles user for a RM 5 OFF on an order, with a minimum spend of RM20\r\n- Offer is limited to THREE (3) redemption per user', 1, '2022-06-15 03:38:43', '2022-06-23 21:52:39'),
(4, 'R2', 'percent_off', '{\"percentage\":\"10\"}', '[\"4\"]', NULL, 1, '2022-06-22 04:33:00', '2022-06-22 04:33:00'),
(5, 'R3', 'dollar_dis', '{\"discounted_price\":\"14.00\"}', '[\"7\"]', NULL, 1, '2022-06-22 11:05:17', '2022-06-22 11:05:17'),
(6, 'R3', 'dollar_dis', '{\"discounted_price\":\"12.00\"}', '[\"6\"]', NULL, 1, '2022-06-22 11:05:29', '2022-06-22 11:05:29'),
(7, 'R3', 'dollar_dis', '{\"discounted_price\":\"18.00\"}', '[\"5\"]', NULL, 1, '2022-06-22 11:05:36', '2022-06-22 11:05:36');

-- --------------------------------------------------------

--
-- Table structure for table `promotional_ads`
--

CREATE TABLE `promotional_ads` (
  `promotional_ads_id` int(11) NOT NULL,
  `rest_id` varchar(5) NOT NULL,
  `title` mediumtext NOT NULL,
  `content` longtext NOT NULL,
  `photos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp(),
  `modified_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `promotional_ads`
--

INSERT INTO `promotional_ads` (`promotional_ads_id`, `rest_id`, `title`, `content`, `photos`, `status`, `created_date`, `modified_date`) VALUES
(1, 'R1', 'OPENING DAY PROMOTION', '20% discount on all food items on the menu from 15 June to 17 June ! Try our delicious food before the promotion ends !', '[\"R1-1-1.png\"]', 1, '2022-06-15 11:14:24', '2022-06-15 11:15:22'),
(3, 'R2', 'Today Menu Special', '10% discount on today menu! Try our delicious food before the promotion ends !', '[\"R2-3-1.png\"]', 1, '2022-06-22 04:37:25', '2022-06-22 04:38:02'),
(5, 'R3', 'Dishes of the day', 'Hurry Up! Enjoy Our Special Price Today!', '[\"R3-5-1.png\"]', 1, '2022-06-22 11:12:08', '2022-06-22 11:12:08');

-- --------------------------------------------------------

--
-- Table structure for table `rating_review`
--

CREATE TABLE `rating_review` (
  `order_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `review` varchar(2000) DEFAULT NULL,
  `reply` varchar(2000) DEFAULT NULL,
  `review_modified_date` datetime NOT NULL DEFAULT current_timestamp(),
  `read_date` datetime DEFAULT NULL,
  `reply_modified_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `rating_review`
--

INSERT INTO `rating_review` (`order_id`, `rating`, `review`, `reply`, `review_modified_date`, `read_date`, `reply_modified_date`) VALUES
(12, 5, NULL, 'Thanks for your 5-star review! We sincerely appreciate your feedback.', '2022-06-24 19:05:16', '2022-07-01 12:27:10', '2022-07-01 12:27:10'),
(16, 5, 'Service is good', 'We are so happy to hear you had a great experience at our restaurant. Your review made our day! Our whole team works very hard to keep our customers happy, but we can only do it thanks to amazing customers like you. Thanks again, and we hope to serve you again soon.', '2022-06-24 16:58:54', '2022-07-01 12:27:57', '2022-07-01 12:27:57'),
(35, 4, NULL, NULL, '2022-07-01 16:12:59', NULL, NULL),
(37, 5, 'Food is nice', 'Thanks for your 5-star review! We sincerely appreciate your feedback.', '2022-07-01 15:12:43', '2022-07-01 15:13:59', '2022-07-01 15:13:59'),
(45, 5, NULL, NULL, '2022-07-01 16:08:37', NULL, NULL),
(52, 4, NULL, NULL, '2022-07-01 16:08:29', NULL, NULL),
(55, 3, NULL, NULL, '2022-07-01 16:10:50', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reservation`
--

CREATE TABLE `reservation` (
  `reservation_id` int(11) NOT NULL,
  `cus_id` varchar(5) NOT NULL,
  `rest_id` varchar(5) NOT NULL,
  `date` date NOT NULL,
  `time_slot_id` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`time_slot_id`)),
  `table_id` int(11) NOT NULL,
  `additional_notes` varchar(1000) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp(),
  `modified_date` datetime NOT NULL DEFAULT current_timestamp(),
  `status` int(1) NOT NULL,
  `remarks` varchar(1000) NOT NULL,
  `deleted_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `reservation`
--

INSERT INTO `reservation` (`reservation_id`, `cus_id`, `rest_id`, `date`, `time_slot_id`, `table_id`, `additional_notes`, `created_date`, `modified_date`, `status`, `remarks`, `deleted_data`) VALUES
(1, 'C1', 'R1', '2022-06-24', '[\"2\"]', 2, '', '2022-06-15 12:51:46', '2022-06-23 17:05:42', 0, '', '{\"startTime\":\"08:30:00\",\"endTime\":\"09:30:00\"}'),
(3, 'C6', 'R1', '2022-06-24', '[\"1\"]', 1, '', '2022-06-23 17:14:31', '2022-06-23 17:17:09', 0, '', '{\"startTime\":\"07:30:00\",\"endTime\":\"08:30:00\"}'),
(5, 'C2', 'R1', '2022-06-24', '[\"1\"]', 3, '', '2022-06-23 17:39:56', '2022-06-23 18:10:30', 3, '', '{\"startTime\":\"07:30:00\",\"endTime\":\"08:30:00\"}'),
(6, 'C8', 'R1', '2022-06-24', '[\"4\"]', 4, '', '2022-06-23 19:00:57', '2022-06-23 19:28:04', 3, '', '{\"startTime\":\"10:30:00\",\"endTime\":\"11:30:00\"}'),
(7, 'C6', 'R1', '2022-06-24', '[\"2\"]', 1, '', '2022-06-23 23:37:56', '2022-06-23 23:38:53', 2, 'Selected table is currently unavailable', '{\"startTime\":\"08:30:00\",\"endTime\":\"09:30:00\"}'),
(8, 'C5', 'R1', '2022-06-28', '[\"1\"]', 3, '', '2022-06-23 23:42:45', '2022-06-23 23:43:41', 2, 'Selected table is currently unavailable for the selected time slot', '{\"startTime\":\"07:30:00\",\"endTime\":\"08:30:00\"}'),
(9, 'C7', 'R1', '2022-06-24', '[\"2\"]', 20, '', '2022-06-23 23:52:48', '2022-06-24 00:03:46', 2, 'Selected table is no longer available', '{\"table_num_name\":\"T07\",\"startTime\":\"08:30:00\",\"endTime\":\"09:30:00\"}'),
(10, 'C6', 'R1', '2022-06-25', '[\"4\"]', 6, '', '2022-06-24 00:19:03', '2022-06-24 00:20:30', 2, 'Selected time slot is no longer available', '{\"startTime\":\"10:30:00\",\"endTime\":\"11:30:00\"}'),
(11, 'C8', 'R1', '2022-06-25', '[\"15\"]', 5, '', '2022-06-24 00:22:16', '2022-06-24 00:23:16', 2, 'Selected time slot(s) is currently unavailable', '{\"startTime\":\"21:30:00\",\"endTime\":\"22:30:00\"}'),
(12, 'C8', 'R1', '2022-06-25', '[\"54\"]', 2, '', '2022-06-24 01:23:30', '2022-07-01 16:18:44', 2, 'Selected table is currently unavailable', '[]'),
(13, 'C1', 'R1', '2022-06-25', '[\"60\"]', 2, 'No vege', '2022-06-24 03:31:23', '2022-06-24 10:48:31', 0, '', '[]'),
(14, 'C1', 'R1', '2022-06-25', '[\"68\"]', 1, '', '2022-06-24 11:07:35', '2022-06-24 21:56:25', 0, '', '[]'),
(15, 'C1', 'R1', '2022-07-01', '[\"63\"]', 5, '', '2022-06-24 21:38:22', '2022-07-01 12:21:38', 3, '', '[]'),
(16, 'C1', 'R1', '2022-07-02', '[\"57\"]', 1, '', '2022-07-01 12:23:29', '2022-07-01 16:17:56', 0, '', '[]'),
(17, 'C6', 'R3', '2022-07-01', '[\"49\"]', 12, '', '2022-07-01 14:44:54', '2022-07-01 14:45:47', 3, '', '[]'),
(18, 'C10', 'R3', '2022-07-06', '[\"40\"]', 13, '', '2022-07-01 14:46:27', '2022-07-01 14:57:16', 2, 'Selected table is currently unavailable', '[]'),
(19, 'C2', 'R3', '2022-07-02', '[\"41\"]', 17, '', '2022-07-01 14:46:59', '2022-07-01 14:56:34', 0, '', '[]'),
(20, 'C9', 'R3', '2022-07-02', '[\"43\"]', 15, '', '2022-07-01 14:47:24', '2022-07-01 14:55:29', 3, '', '[]'),
(21, 'C8', 'R3', '2022-07-05', '[\"39\"]', 15, '', '2022-07-01 14:47:44', '2022-07-01 14:52:21', 0, '', '[]'),
(22, 'C5', 'R3', '2022-07-09', '[\"40\"]', 13, '', '2022-07-01 14:48:05', '2022-07-01 14:54:03', 3, '', '[]'),
(23, 'C3', 'R3', '2022-07-01', '[\"47\"]', 16, '', '2022-07-01 14:48:36', '2022-07-01 14:50:14', 0, '', '[]'),
(24, 'C7', 'R3', '2022-07-02', '[\"41\"]', 14, '', '2022-07-01 14:48:58', '2022-07-01 14:51:57', 2, 'Selected table is currently unavailable', '[]'),
(25, 'C1', 'R3', '2022-07-06', '[\"40\"]', 15, '', '2022-07-01 14:49:16', '2022-07-01 14:54:57', 3, '', '[]'),
(26, 'C4', 'R3', '2022-07-04', '[\"39\"]', 13, '', '2022-07-01 14:49:41', '2022-07-01 14:54:14', 3, '', '[]'),
(27, 'C2', 'R5', '2022-07-04', '[\"70\"]', 22, '', '2022-07-01 15:27:37', '2022-07-01 15:31:45', 2, 'Selected table is currently unavailable', '[]'),
(28, 'C9', 'R5', '2022-07-01', '[\"83\"]', 23, '', '2022-07-01 15:27:59', '2022-07-01 15:32:16', 3, '', '[]'),
(29, 'C1', 'R5', '2022-07-04', '[\"71\"]', 22, '', '2022-07-01 15:28:18', '2022-07-01 15:29:49', 0, '', '[]'),
(30, 'C2', 'R2', '2022-07-06', '[\"19\",\"20\"]', 9, '', '2022-07-01 15:35:19', '2022-07-01 15:35:19', 0, '', '[]'),
(31, 'C9', 'R2', '2022-07-03', '[\"20\"]', 8, '', '2022-07-01 15:35:40', '2022-07-01 15:36:46', 3, '', '[]'),
(32, 'C7', 'R2', '2022-07-06', '[\"18\",\"19\"]', 8, '', '2022-07-01 15:36:03', '2022-07-01 15:36:59', 2, 'Selected table is currently unavailable', '[]'),
(33, 'C10', 'R2', '2022-07-01', '[\"36\"]', 9, '', '2022-07-01 15:38:12', '2022-07-01 15:39:15', 3, '', '[]'),
(34, 'C2', 'R4', '2022-07-04', '[\"91\"]', 25, '', '2022-07-01 15:49:17', '2022-07-01 15:49:17', 0, '', '[]'),
(35, 'C9', 'R4', '2022-07-06', '[\"92\"]', 25, '', '2022-07-01 15:49:36', '2022-07-01 15:50:21', 2, 'Selected table is currently unavailable', '[]'),
(36, 'C1', 'R4', '2022-07-03', '[\"91\"]', 24, '', '2022-07-01 15:49:55', '2022-07-01 15:50:46', 3, '', '[]'),
(37, 'C10', 'R4', '2022-07-02', '[\"91\"]', 24, '', '2022-07-01 15:51:38', '2022-07-01 15:52:01', 2, 'Selected table is currently unavailable', '[]');

-- --------------------------------------------------------

--
-- Table structure for table `restaurant`
--

CREATE TABLE `restaurant` (
  `rest_id` varchar(5) NOT NULL,
  `rest_name` varchar(100) NOT NULL,
  `owner_name` varchar(64) NOT NULL,
  `owner_contact_num` varchar(12) NOT NULL,
  `owner_email` varchar(96) NOT NULL,
  `contact_num` varchar(12) NOT NULL,
  `email` varchar(96) NOT NULL,
  `address` varchar(400) NOT NULL,
  `city` varchar(20) DEFAULT NULL,
  `rest_profile` varchar(255) NOT NULL DEFAULT 'default_pp_rest.png',
  `cover_photo` varchar(255) NOT NULL DEFAULT 'default_cover.png',
  `documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`documents`)),
  `description` varchar(1000) NOT NULL,
  `dining_style_id` int(11) NOT NULL DEFAULT 0,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]',
  `opening_hours` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]',
  `daily_opening_hours` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]',
  `photos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]',
  `additional_notes` varchar(1000) NOT NULL,
  `join_date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `restaurant`
--

INSERT INTO `restaurant` (`rest_id`, `rest_name`, `owner_name`, `owner_contact_num`, `owner_email`, `contact_num`, `email`, `address`, `city`, `rest_profile`, `cover_photo`, `documents`, `description`, `dining_style_id`, `tags`, `opening_hours`, `daily_opening_hours`, `photos`, `additional_notes`, `join_date`) VALUES
('R1', 'Penny Homecook', 'Penny Lim', '012-3456789', '', '03-12345678', 'pennylim2000@gmail.com', '36, Jalan Besar, Kampung Macap, 86200 Johor', 'Johor', 'R1.png', 'R1.jpg', '[\"R1.pdf\"]', 'Penny Homecook offers a wide array of fresh food. We use the freshest ingredients in preparing our food to provide the best quality and taste. Try our delicious food today!', 1, '[\"homecook\",\"chinese food\",\"foodie\",\"yummy\"]', '[{\"from\":\"1\",\"to\":\"5\",\"open\":\"08:00\",\"close\":\"22:00\"},{\"from\":\"6\",\"to\":\"7\",\"open\":\"07:30\",\"close\":\"23:30\"}]', '{\"Monday\":{\"open\":\"08:00\",\"close\":\"22:00\"},\"Tuesday\":{\"open\":\"08:00\",\"close\":\"22:00\"},\"Wednesday\":{\"open\":\"08:00\",\"close\":\"22:00\"},\"Thursday\":{\"open\":\"08:00\",\"close\":\"22:00\"},\"Friday\":{\"open\":\"08:00\",\"close\":\"22:00\"},\"Saturday\":{\"open\":\"07:30\",\"close\":\"23:30\"},\"Sunday\":{\"open\":\"07:30\",\"close\":\"23:30\"}}', '[\"R1.png\",\"R1(1).png\",\"R1(2).png\",\"R1(3).png\"]', '', '2022-06-14'),
('R2', 'Vegan Food', 'James', '012-3456789', '', '03-11111111', 'veganFood@gmail.com', '25, Jalan 4/96, Taman Bukit Sri Cheras, 56100 Kuala Lumpur', 'Kuala Lumpur', 'R2.png', 'R2.jpg', '[\"R2.pdf\"]', 'Vegan Food offers menus for Lunch and Dinner. The restaurant has a elegant atmosphere music. Within a year of its opening, Vegan Food has been recognized for its quality of food and excellent service.', 2, '[\"vegetarian\",\"elegant\",\"quality\",\"nice\",\"yummy\"]', '[{\"from\":\"1\",\"to\":\"7\",\"open\":\"12:00\",\"close\":\"23:00\"}]', '{\"Monday\":{\"open\":\"12:00\",\"close\":\"23:00\"},\"Tuesday\":{\"open\":\"12:00\",\"close\":\"23:00\"},\"Wednesday\":{\"open\":\"12:00\",\"close\":\"23:00\"},\"Thursday\":{\"open\":\"12:00\",\"close\":\"23:00\"},\"Friday\":{\"open\":\"12:00\",\"close\":\"23:00\"},\"Saturday\":{\"open\":\"12:00\",\"close\":\"23:00\"},\"Sunday\":{\"open\":\"12:00\",\"close\":\"23:00\"}}', '[\"R2.png\",\"R2(1).png\",\"R2(2).png\"]', '', '2022-06-22'),
('R3', 'The Western', 'John Lee', '011-11111111', '', '03-11111111', 'thewestern@gmail.com', '5 Wisma Azan 134, Jalan Raja Abdullah, Kuala Lumpur', 'Kuala Lumpur', 'R3.png', 'R3.jpg', '[\"R3.pdf\"]', 'The Western offers a wide array of fresh food. We use the freshest ingredients in preparing our food to provide the best quality and taste. Try our delicious food today!', 1, '[\"westernfood\",\"fresh\",\"delicious\",\"westernfoodlover\"]', '[{\"from\":\"1\",\"to\":\"5\",\"open\":\"10:00\",\"close\":\"23:30\"},{\"from\":\"6\",\"to\":\"7\",\"open\":\"08:00\",\"close\":\"23:30\"}]', '{\"Monday\":{\"open\":\"10:00\",\"close\":\"23:30\"},\"Tuesday\":{\"open\":\"10:00\",\"close\":\"23:30\"},\"Wednesday\":{\"open\":\"10:00\",\"close\":\"23:30\"},\"Thursday\":{\"open\":\"10:00\",\"close\":\"23:30\"},\"Friday\":{\"open\":\"10:00\",\"close\":\"23:30\"},\"Saturday\":{\"open\":\"08:00\",\"close\":\"23:30\"},\"Sunday\":{\"open\":\"08:00\",\"close\":\"23:30\"}}', '[\"R3.png\",\"R3(1).png\",\"R3(2).png\"]', '', '2022-06-22'),
('R4', 'Hot Chicken', 'Jordan', '012-2222222', '', '03-87594613', 'hotchicken@gmail.com', 'No. 12, Jln Mewah 3, Ampang, Selangor', 'Selangor', 'R4.png', 'R4.png', '[\"R4.pdf\"]', 'Hot Chicken is a fast food restaurant. We serves fried chicken—chunks of chicken, battered or breaded and deep-fried.', 1, '[\"chicken\",\"yummy\",\"fresh\"]', '[{\"from\":\"1\",\"to\":\"7\",\"open\":\"11:00\",\"close\":\"23:00\"}]', '{\"Monday\":{\"open\":\"11:00\",\"close\":\"23:00\"},\"Tuesday\":{\"open\":\"11:00\",\"close\":\"23:00\"},\"Wednesday\":{\"open\":\"11:00\",\"close\":\"23:00\"},\"Thursday\":{\"open\":\"11:00\",\"close\":\"23:00\"},\"Friday\":{\"open\":\"11:00\",\"close\":\"23:00\"},\"Saturday\":{\"open\":\"11:00\",\"close\":\"23:00\"},\"Sunday\":{\"open\":\"11:00\",\"close\":\"23:00\"}}', '[\"R4.png\",\"R4(1).png\",\"R4(2).png\"]', '', '2022-06-22'),
('R5', 'The Japanese', 'Jack', '013-3333333', '', '03-89576425', 'thejapanese@gmail.com', '238-240 Batu 2 Jalan Wilayah Persekutuan, Perak', 'Perak', 'R5.png', 'R5.png', '[\"R5.pdf\"]', 'We offers an abundance of gastronomical delights with a boundless variety of regional and seasonal dishes.', 3, '[]', '[{\"from\":\"1\",\"to\":\"7\",\"open\":\"09:00\",\"close\":\"22:00\"}]', '{\"Monday\":{\"open\":\"09:00\",\"close\":\"22:00\"},\"Tuesday\":{\"open\":\"09:00\",\"close\":\"22:00\"},\"Wednesday\":{\"open\":\"09:00\",\"close\":\"22:00\"},\"Thursday\":{\"open\":\"09:00\",\"close\":\"22:00\"},\"Friday\":{\"open\":\"09:00\",\"close\":\"22:00\"},\"Saturday\":{\"open\":\"09:00\",\"close\":\"22:00\"},\"Sunday\":{\"open\":\"09:00\",\"close\":\"22:00\"}}', '[\"R5.png\",\"R5(1).png\",\"R5(2).png\"]', '', '2022-06-22');

-- --------------------------------------------------------

--
-- Table structure for table `rest_guest`
--

CREATE TABLE `rest_guest` (
  `rest_id` varchar(5) NOT NULL,
  `guest_id` varchar(5) NOT NULL,
  `online` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `settings_id` int(11) NOT NULL,
  `rest_id` varchar(5) NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`value`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`settings_id`, `rest_id`, `key`, `value`) VALUES
(1, 'R1', 'time_slot', '{\"earliest-time\":\"07:30\",\"latest-time\":\"23:30\",\"h\":\"1\",\"m\":\"\",\"maximum-time-slot\":\"1\"}'),
(2, 'R2', 'time_slot', '{\"earliest-time\":\"12:00\",\"latest-time\":\"23:00\",\"h\":\"\",\"m\":\"30\",\"maximum-time-slot\":\"2\"}'),
(3, 'R3', 'time_slot', '{\"earliest-time\":\"08:00\",\"latest-time\":\"22:00\",\"h\":\"1\",\"m\":\"\",\"maximum-time-slot\":\"1\"}'),
(4, 'R1', 'sharing_post', '{\"title\":\"Check out Penny Homecook on F&B Corner!\"}'),
(5, 'R1', 'rating_review', '{\"reply_options\":[\"Thanks for your 5-star review! We sincerely appreciate your feedback.\",\"We are so happy to hear you had a great experience at our restaurant. Your review made our day! Our whole team works very hard to keep our customers happy, but we can only do it thanks to amazing customers like you. Thanks again, and we hope to serve you again soon.\"],\"auto_reply_index\":\"0\"}'),
(6, 'R3', 'rating_review', '{\"reply_options\":[\"Thanks for your 5-star review! We sincerely appreciate your feedback.\"],\"auto_reply_index\":\"0\"}'),
(7, 'R5', 'time_slot', '{\"earliest-time\":\"10:00\",\"latest-time\":\"21:00\",\"h\":\"\",\"m\":\"30\",\"maximum-time-slot\":\"1\"}'),
(8, 'R4', 'time_slot', '{\"earliest-time\":\"09:00\",\"latest-time\":\"21:00\",\"h\":\"\",\"m\":\"30\",\"maximum-time-slot\":\"1\"}');

-- --------------------------------------------------------

--
-- Table structure for table `sharing`
--

CREATE TABLE `sharing` (
  `rest_id` varchar(5) NOT NULL,
  `count` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `month` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sharing`
--

INSERT INTO `sharing` (`rest_id`, `count`, `year`, `month`) VALUES
('R1', 17, 2022, 6),
('R1', 3, 2022, 7),
('R3', 5, 2022, 7),
('R5', 1, 2022, 6),
('R5', 5, 2022, 7);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `rest_id` varchar(5) NOT NULL,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `email` varchar(96) NOT NULL,
  `contact_num` varchar(12) NOT NULL,
  `address` varchar(400) NOT NULL,
  `date_of_birth` date NOT NULL,
  `image` varchar(255) NOT NULL,
  `entry_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `rest_id`, `firstname`, `lastname`, `email`, `contact_num`, `address`, `date_of_birth`, `image`, `entry_date`) VALUES
(1, 'R1', 'Karina', '', 'karina@gmail.com', '013-4556789', 'A 35 Jln Seenivasagam, Ipoh', '1978-06-24', 'default_staff_img.png', '2022-06-24 22:47:17'),
(3, 'R1', 'Don', '', 'don@gmail.com', '012-3456789', '36, Jln Bandar 3, Taman Melawati Hulu, Klang, Selangor', '1998-07-02', 'R1-3.jpg', '2022-07-01 12:47:23'),
(4, 'R3', 'Aimee', '', 'aimee@gmail.com', '012-3456788', '11 Menara Mutiara Bangsar 8 Jln Liku, Kuala Lumpur', '1986-07-12', 'default_staff_img.png', '2022-07-01 15:01:11'),
(5, 'R3', 'karen', '', 'karen@hotmail.com', '010-1325456', 'Batu 5, Jalan , Wilayah Persekutuan, Kuala Lumpur', '1977-08-20', 'default_staff_img.png', '2022-07-01 15:06:57'),
(6, 'R5', 'Danny', '', 'danny@gmail.com', '012-3468988', '3 48 3 Jln Usj 9/5P Taman Seafield Jaya Petaling Jaya', '1996-07-27', 'default_staff_img.png', '2022-07-01 15:24:17'),
(7, 'R5', 'Gary', '', 'gary@gmail.com', '012-37482394', 'Jalan Putra Perdana 1/3, Taman Putra Perdana, Puchong', '1988-09-12', 'default_staff_img.png', '2022-07-01 15:24:49'),
(8, 'R2', 'Luke', '', 'luke@gmail.com', '014-6258273', '3 48 3 Jln Usj 9/5P Taman Seafield Jaya Petaling Jaya', '1973-07-20', 'default_staff_img.png', '2022-07-01 15:41:11'),
(9, 'R4', 'Vicky', '', 'vicky@gmail.com', '016-45887595', 'G Blok C Jln Selingsing 4 Taman Sri Wilayah Persekutuan', '1977-03-05', 'default_staff_img.png', '2022-07-01 15:48:27');

-- --------------------------------------------------------

--
-- Table structure for table `table`
--

CREATE TABLE `table` (
  `table_id` int(11) NOT NULL,
  `rest_id` varchar(5) NOT NULL,
  `table_num_name` varchar(10) NOT NULL,
  `capacity` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `table`
--

INSERT INTO `table` (`table_id`, `rest_id`, `table_num_name`, `capacity`, `status`, `sort_order`) VALUES
(1, 'R1', 'T01', 1, 1, 0),
(2, 'R1', 'T02', 1, 1, 1),
(3, 'R1', 'T03', 2, 1, 2),
(4, 'R1', 'T04', 2, 1, 3),
(5, 'R1', 'T05', 3, 1, 4),
(6, 'R1', 'T06', 3, 1, 5),
(8, 'R2', '001', 1, 0, 0),
(9, 'R2', '002', 2, 1, 0),
(10, 'R2', '003', 3, 1, 0),
(11, 'R2', '004', 4, 1, 0),
(12, 'R3', 'N-01', 1, 1, 0),
(13, 'R3', 'N-02', 1, 0, 1),
(14, 'R3', 'N-03', 2, 0, 2),
(15, 'R3', 'N-04', 2, 1, 3),
(16, 'R3', 'N-05', 3, 1, 4),
(17, 'R3', 'N-06', 3, 1, 5),
(18, 'R3', 'N-07', 4, 1, 6),
(21, 'R5', 'J01', 4, 1, 0),
(22, 'R5', 'J02', 4, 0, 1),
(23, 'R5', 'J03', 2, 1, 2),
(24, 'R4', '001', 2, 0, 0),
(25, 'R4', '002', 4, 1, 1),
(26, 'R4', '003', 4, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `time_slot`
--

CREATE TABLE `time_slot` (
  `time_slot_id` int(11) NOT NULL,
  `rest_id` varchar(5) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` int(11) NOT NULL,
  `tables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`tables`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `time_slot`
--

INSERT INTO `time_slot` (`time_slot_id`, `rest_id`, `start_time`, `end_time`, `status`, `tables`) VALUES
(17, 'R2', '12:00:00', '12:30:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(18, 'R2', '12:30:00', '13:00:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(19, 'R2', '13:00:00', '13:30:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(20, 'R2', '13:30:00', '14:00:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(21, 'R2', '14:00:00', '14:30:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(22, 'R2', '14:30:00', '15:00:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(23, 'R2', '15:00:00', '15:30:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(24, 'R2', '15:30:00', '16:00:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(25, 'R2', '16:00:00', '16:30:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(26, 'R2', '16:30:00', '17:00:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(27, 'R2', '17:00:00', '17:30:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(28, 'R2', '17:30:00', '18:00:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(29, 'R2', '18:00:00', '18:30:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(30, 'R2', '18:30:00', '19:00:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(31, 'R2', '19:00:00', '19:30:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(32, 'R2', '19:30:00', '20:00:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(33, 'R2', '20:00:00', '20:30:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(34, 'R2', '20:30:00', '21:00:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(35, 'R2', '21:00:00', '21:30:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(36, 'R2', '21:30:00', '22:00:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(37, 'R2', '22:00:00', '22:30:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(38, 'R2', '22:30:00', '23:00:00', 1, '{\"8\":\"1\",\"9\":\"1\",\"10\":\"1\",\"11\":\"1\"}'),
(39, 'R3', '08:00:00', '09:00:00', 1, '{\"12\":\"1\",\"13\":\"1\",\"14\":\"1\",\"15\":\"1\",\"16\":\"1\",\"17\":\"1\",\"18\":\"1\"}'),
(40, 'R3', '09:00:00', '10:00:00', 1, '{\"12\":\"1\",\"13\":\"1\",\"14\":\"1\",\"15\":\"1\",\"16\":\"1\",\"17\":\"1\",\"18\":\"1\"}'),
(41, 'R3', '10:00:00', '11:00:00', 1, '{\"12\":\"1\",\"13\":\"1\",\"14\":\"1\",\"15\":\"1\",\"16\":\"1\",\"17\":\"1\",\"18\":\"1\"}'),
(42, 'R3', '11:00:00', '12:00:00', 1, '{\"12\":\"1\",\"13\":\"1\",\"14\":\"1\",\"15\":\"1\",\"16\":\"1\",\"17\":\"1\",\"18\":\"1\"}'),
(43, 'R3', '12:00:00', '13:00:00', 1, '{\"12\":\"1\",\"13\":\"1\",\"14\":\"1\",\"15\":\"1\",\"16\":\"1\",\"17\":\"1\",\"18\":\"1\"}'),
(44, 'R3', '13:00:00', '14:00:00', 1, '{\"12\":\"1\",\"13\":\"1\",\"14\":\"1\",\"15\":\"1\",\"16\":\"1\",\"17\":\"1\",\"18\":\"1\"}'),
(45, 'R3', '14:00:00', '15:00:00', 1, '{\"12\":\"1\",\"13\":\"1\",\"14\":\"1\",\"15\":\"1\",\"16\":\"1\",\"17\":\"1\",\"18\":\"1\"}'),
(46, 'R3', '15:00:00', '16:00:00', 1, '{\"12\":\"1\",\"13\":\"1\",\"14\":\"1\",\"15\":\"1\",\"16\":\"1\",\"17\":\"1\",\"18\":\"1\"}'),
(47, 'R3', '16:00:00', '17:00:00', 1, '{\"12\":\"1\",\"13\":\"1\",\"14\":\"1\",\"15\":\"1\",\"16\":\"1\",\"17\":\"1\",\"18\":\"1\"}'),
(48, 'R3', '17:00:00', '18:00:00', 1, '{\"12\":\"1\",\"13\":\"1\",\"14\":\"1\",\"15\":\"1\",\"16\":\"1\",\"17\":\"1\",\"18\":\"1\"}'),
(49, 'R3', '18:00:00', '19:00:00', 1, '{\"12\":\"1\",\"13\":\"1\",\"14\":\"1\",\"15\":\"1\",\"16\":\"1\",\"17\":\"1\",\"18\":\"1\"}'),
(50, 'R3', '19:00:00', '20:00:00', 1, '{\"12\":\"1\",\"13\":\"1\",\"14\":\"1\",\"15\":\"1\",\"16\":\"1\",\"17\":\"1\",\"18\":\"1\"}'),
(51, 'R3', '20:00:00', '21:00:00', 1, '{\"12\":\"1\",\"13\":\"1\",\"14\":\"1\",\"15\":\"1\",\"16\":\"1\",\"17\":\"1\",\"18\":\"1\"}'),
(52, 'R3', '21:00:00', '22:00:00', 1, '{\"12\":\"1\",\"13\":\"1\",\"14\":\"1\",\"15\":\"1\",\"16\":\"1\",\"17\":\"1\",\"18\":\"1\"}'),
(53, 'R1', '07:30:00', '08:30:00', 1, '{\"1\":\"1\",\"2\":\"1\",\"3\":\"1\",\"4\":\"1\",\"5\":\"1\",\"6\":\"1\"}'),
(54, 'R1', '08:30:00', '09:30:00', 1, '{\"1\":\"1\",\"2\":\"1\",\"3\":\"1\",\"4\":\"1\",\"5\":\"1\",\"6\":\"1\"}'),
(55, 'R1', '09:30:00', '10:30:00', 0, '{\"1\":\"1\",\"2\":\"1\",\"3\":\"1\",\"4\":\"1\",\"5\":\"1\",\"6\":\"1\"}'),
(56, 'R1', '10:30:00', '11:30:00', 1, '{\"1\":\"0\",\"2\":\"0\",\"3\":\"1\",\"4\":\"1\",\"5\":\"1\",\"6\":\"1\"}'),
(57, 'R1', '11:30:00', '12:30:00', 1, '{\"1\":\"1\",\"2\":\"1\",\"3\":\"1\",\"4\":\"1\",\"5\":\"1\",\"6\":\"1\"}'),
(58, 'R1', '12:30:00', '13:30:00', 1, '{\"1\":\"1\",\"2\":\"1\",\"3\":\"1\",\"4\":\"1\",\"5\":\"1\",\"6\":\"1\"}'),
(59, 'R1', '13:30:00', '14:30:00', 1, '{\"1\":\"1\",\"2\":\"1\",\"3\":\"1\",\"4\":\"1\",\"5\":\"1\",\"6\":\"1\"}'),
(60, 'R1', '14:30:00', '15:30:00', 1, '{\"1\":\"1\",\"2\":\"1\",\"3\":\"1\",\"4\":\"1\",\"5\":\"1\",\"6\":\"1\"}'),
(61, 'R1', '15:30:00', '16:30:00', 1, '{\"1\":\"1\",\"2\":\"1\",\"3\":\"1\",\"4\":\"1\",\"5\":\"1\",\"6\":\"1\"}'),
(62, 'R1', '16:30:00', '17:30:00', 1, '{\"1\":\"1\",\"2\":\"1\",\"3\":\"1\",\"4\":\"1\",\"5\":\"1\",\"6\":\"1\"}'),
(63, 'R1', '17:30:00', '18:30:00', 1, '{\"1\":\"1\",\"2\":\"1\",\"3\":\"1\",\"4\":\"1\",\"5\":\"1\",\"6\":\"1\"}'),
(64, 'R1', '18:30:00', '19:30:00', 1, '{\"1\":\"1\",\"2\":\"1\",\"3\":\"1\",\"4\":\"1\",\"5\":\"1\",\"6\":\"1\"}'),
(65, 'R1', '19:30:00', '20:30:00', 1, '{\"1\":\"1\",\"2\":\"1\",\"3\":\"1\",\"4\":\"1\",\"5\":\"1\",\"6\":\"1\"}'),
(66, 'R1', '20:30:00', '21:30:00', 1, '{\"1\":\"1\",\"2\":\"1\",\"3\":\"1\",\"4\":\"1\",\"5\":\"1\",\"6\":\"1\"}'),
(67, 'R1', '21:30:00', '22:30:00', 1, '{\"1\":\"1\",\"2\":\"1\",\"3\":\"1\",\"4\":\"1\",\"5\":\"1\",\"6\":\"1\"}'),
(68, 'R1', '22:30:00', '23:30:00', 1, '{\"1\":\"1\",\"2\":\"1\",\"3\":\"1\",\"4\":\"1\",\"5\":\"1\",\"6\":\"1\"}'),
(69, 'R5', '10:00:00', '10:30:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(70, 'R5', '10:30:00', '11:00:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(71, 'R5', '11:00:00', '11:30:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(72, 'R5', '11:30:00', '12:00:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(73, 'R5', '12:00:00', '12:30:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(74, 'R5', '12:30:00', '13:00:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(75, 'R5', '13:00:00', '13:30:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(76, 'R5', '13:30:00', '14:00:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(77, 'R5', '14:00:00', '14:30:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(78, 'R5', '14:30:00', '15:00:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(79, 'R5', '15:00:00', '15:30:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(80, 'R5', '15:30:00', '16:00:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(81, 'R5', '16:00:00', '16:30:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(82, 'R5', '16:30:00', '17:00:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(83, 'R5', '17:00:00', '17:30:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(84, 'R5', '17:30:00', '18:00:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(85, 'R5', '18:00:00', '18:30:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(86, 'R5', '18:30:00', '19:00:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(87, 'R5', '19:00:00', '19:30:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(88, 'R5', '19:30:00', '20:00:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(89, 'R5', '20:00:00', '20:30:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(90, 'R5', '20:30:00', '21:00:00', 1, '{\"21\":\"1\",\"22\":\"1\",\"23\":\"1\"}'),
(91, 'R4', '09:00:00', '09:30:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(92, 'R4', '09:30:00', '10:00:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(93, 'R4', '10:00:00', '10:30:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(94, 'R4', '10:30:00', '11:00:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(95, 'R4', '11:00:00', '11:30:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(96, 'R4', '11:30:00', '12:00:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(97, 'R4', '12:00:00', '12:30:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(98, 'R4', '12:30:00', '13:00:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(99, 'R4', '13:00:00', '13:30:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(100, 'R4', '13:30:00', '14:00:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(101, 'R4', '14:00:00', '14:30:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(102, 'R4', '14:30:00', '15:00:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(103, 'R4', '15:00:00', '15:30:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(104, 'R4', '15:30:00', '16:00:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(105, 'R4', '16:00:00', '16:30:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(106, 'R4', '16:30:00', '17:00:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(107, 'R4', '17:00:00', '17:30:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(108, 'R4', '17:30:00', '18:00:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(109, 'R4', '18:00:00', '18:30:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(110, 'R4', '18:30:00', '19:00:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(111, 'R4', '19:00:00', '19:30:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(112, 'R4', '19:30:00', '20:00:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(113, 'R4', '20:00:00', '20:30:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}'),
(114, 'R4', '20:30:00', '21:00:00', 1, '{\"24\":\"1\",\"25\":\"1\",\"26\":\"1\"}');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `rest_id` varchar(5) NOT NULL,
  `username` varchar(32) NOT NULL,
  `password` varchar(1000) NOT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `user_group_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `rest_id`, `username`, `password`, `staff_id`, `user_group_id`) VALUES
(1, 'R1', 'admin', '$2y$10$U6HqQPVlYOMF7W1SVnhPsufMIec59Qyg6tgL1LcmBnhtLNl0yg0pG', NULL, 1),
(2, 'R2', 'admin', '$2y$10$.fGoQzB/.xWo9u1wF/ql0uaFNWiSxHLa/Gkti4/cvq09uAgicCx7m', NULL, 2),
(3, 'R3', 'admin', '$2y$10$w3907FgN8HuvAoaOrTQNp.P4NT1ftSMLFo73I0ez7sawVtQ3NXM0m', NULL, 3),
(4, 'R4', 'admin', '$2y$10$rc//qoMpWqRB1WMmfb7V..4TiK93zYdEhRxK7h6jdyItO86KzLf6K', NULL, 4),
(5, 'R5', 'admin', '$2y$10$y2E2filutP4dgrWGRnjGzez6TL6eM.qgfdpBFsasonE.JKBAHTWjK', NULL, 5),
(6, 'R1', 'karina', '$2y$10$YuKnv8dmY/c2kMmgWWrU3.MAnWuIDhgFthqXXdDpJ.uFqXwpCyooC', 1, 6),
(8, 'R1', 'don', '$2y$10$crykz6JfmDIGAcBjsNhSN.iqf8dq.MNyx2hR1YfZorujrDQFtjKiS', 3, 6),
(9, 'R3', 'aimee', '$2y$10$MFhiuHPqFujuwKPcpKsCquW5wgEiq/I58PtrjgwrKv/H7kNNuHnrW', 4, 8),
(10, 'R3', 'karen', '$2y$10$C/Pj9MIRL8AlpyM/x0z7SOM.2nIJAwfB..LmMbve2ARrzeO3rSaIW', 5, 8),
(11, 'R2', 'luke', '$2y$10$1ioAj1ZWj7UnEBEa9SVciuJwhdPHO9Eaw2cOmcwGp7k9QmoQnk2Ty', 8, 10),
(12, 'R4', 'Vicky', '$2y$10$epA1rJaHYb7YHu7Q5oZ2mOx3Z0Qdzz2y0fXWvcv/jfsqja2lrVE3a', 9, 11);

-- --------------------------------------------------------

--
-- Table structure for table `user_group`
--

CREATE TABLE `user_group` (
  `user_group_id` int(11) NOT NULL,
  `rest_id` varchar(5) NOT NULL,
  `user_group_name` varchar(100) NOT NULL,
  `access_permission` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`access_permission`)),
  `modify_permission` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`modify_permission`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user_group`
--

INSERT INTO `user_group` (`user_group_id`, `rest_id`, `user_group_name`, `access_permission`, `modify_permission`) VALUES
(1, 'R1', 'Administrator', '[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\",\"13\",\"14\",\"15\",\"16\",\"17\",\"18\"]', '[\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\",\"13\",\"14\",\"15\",\"16\",\"18\"]'),
(2, 'R2', 'Administrator', '[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\",\"13\",\"14\",\"15\",\"16\",\"17\",\"18\"]', '[\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\",\"13\",\"14\",\"15\",\"16\",\"18\"]'),
(3, 'R3', 'Administrator', '[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\",\"13\",\"14\",\"15\",\"16\",\"17\",\"18\"]', '[\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\",\"13\",\"14\",\"15\",\"16\",\"18\"]'),
(4, 'R4', 'Administrator', '[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\",\"13\",\"14\",\"15\",\"16\",\"17\",\"18\"]', '[\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\",\"13\",\"14\",\"15\",\"16\",\"18\"]'),
(5, 'R5', 'Administrator', '[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\",\"13\",\"14\",\"15\",\"16\",\"17\",\"18\"]', '[\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\",\"13\",\"14\",\"15\",\"16\",\"18\"]'),
(6, 'R1', 'Staff', '[\"18\",\"17\",\"9\",\"8\",\"14\",\"10\",\"11\",\"12\",\"15\",\"6\",\"4\",\"5\",\"7\",\"13\"]', '[\"14\",\"10\",\"11\",\"6\",\"13\"]'),
(8, 'R3', 'Staff', '[\"18\",\"17\",\"9\",\"8\",\"14\",\"10\",\"11\",\"12\",\"15\",\"6\",\"4\",\"5\",\"7\"]', '[\"8\",\"14\",\"10\",\"11\",\"6\"]'),
(9, 'R5', 'staff', '[\"18\",\"17\",\"9\",\"8\",\"14\",\"10\",\"11\",\"12\",\"15\",\"6\",\"4\",\"5\",\"7\"]', '[\"8\",\"14\",\"10\",\"11\",\"6\"]'),
(10, 'R2', 'Staff', '[\"18\",\"17\",\"9\",\"8\",\"14\",\"10\",\"11\",\"12\",\"15\",\"6\",\"4\",\"5\",\"7\"]', '[\"14\",\"10\",\"11\",\"6\",\"13\"]'),
(11, 'R4', 'Staff', '[\"18\",\"17\",\"9\",\"8\",\"14\",\"10\",\"11\",\"12\",\"15\",\"6\",\"4\",\"5\",\"7\"]', '[\"14\",\"10\",\"11\",\"6\",\"13\"]');

-- --------------------------------------------------------

--
-- Table structure for table `visitor`
--

CREATE TABLE `visitor` (
  `rest_id` varchar(5) NOT NULL,
  `count` int(11) NOT NULL,
  `month` tinyint(4) NOT NULL,
  `year` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `visitor`
--

INSERT INTO `visitor` (`rest_id`, `count`, `month`, `year`) VALUES
('R1', 22, 6, 2022),
('R1', 9, 7, 2022),
('R2', 4, 6, 2022),
('R2', 4, 7, 2022),
('R3', 3, 6, 2022),
('R3', 7, 7, 2022),
('R4', 2, 6, 2022),
('R4', 3, 7, 2022),
('R5', 3, 6, 2022),
('R5', 6, 7, 2022);

-- --------------------------------------------------------

--
-- Table structure for table `voucher`
--

CREATE TABLE `voucher` (
  `voucher_id` int(11) NOT NULL,
  `voucher_type_id` int(11) NOT NULL,
  `cus_id` varchar(5) NOT NULL,
  `applied_order_id` int(11) NOT NULL,
  `redeemed_date` date NOT NULL DEFAULT current_timestamp(),
  `expired_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `voucher`
--

INSERT INTO `voucher` (`voucher_id`, `voucher_type_id`, `cus_id`, `applied_order_id`, `redeemed_date`, `expired_date`) VALUES
(1, 1, 'C1', 0, '2021-05-24', '2022-05-24'),
(2, 2, 'C1', 31, '2022-06-24', '2023-06-24'),
(3, 1, 'C1', 0, '2022-06-24', '2023-06-24'),
(4, 2, 'C10', 0, '2022-06-24', '2023-06-24'),
(5, 2, 'C2', 0, '2022-06-24', '2023-06-24'),
(6, 1, 'C5', 0, '2022-06-24', '2023-06-24');

-- --------------------------------------------------------

--
-- Table structure for table `voucher_type`
--

CREATE TABLE `voucher_type` (
  `voucher_type_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `points` int(11) NOT NULL,
  `equal_price` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `voucher_type`
--

INSERT INTO `voucher_type` (`voucher_type_id`, `name`, `points`, `equal_price`) VALUES
(1, '150 pts to RM 1', 150, 1),
(2, '750 pts to RM 5', 750, 5),
(3, '1,500 pts to RM 10', 1500, 10),
(4, '3,000 pts to RM 20', 3000, 20);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`chat_id`);

--
-- Indexes for table `conversation`
--
ALTER TABLE `conversation`
  ADD PRIMARY KEY (`conversation_id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`cus_id`);

--
-- Indexes for table `cus_points`
--
ALTER TABLE `cus_points`
  ADD PRIMARY KEY (`cus_points_id`);

--
-- Indexes for table `cus_promo_redemption`
--
ALTER TABLE `cus_promo_redemption`
  ADD PRIMARY KEY (`cus_promo_redemption_id`);

--
-- Indexes for table `cus_rest_history`
--
ALTER TABLE `cus_rest_history`
  ADD PRIMARY KEY (`cus_rest_history_id`);

--
-- Indexes for table `dining_style`
--
ALTER TABLE `dining_style`
  ADD PRIMARY KEY (`dining_style_id`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `favourite_restaurant`
--
ALTER TABLE `favourite_restaurant`
  ADD PRIMARY KEY (`cus_id`,`rest_id`);

--
-- Indexes for table `food_item`
--
ALTER TABLE `food_item`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`);

--
-- Indexes for table `login_activity`
--
ALTER TABLE `login_activity`
  ADD PRIMARY KEY (`login_activity_id`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `permission`
--
ALTER TABLE `permission`
  ADD PRIMARY KEY (`permission_id`);

--
-- Indexes for table `promotion`
--
ALTER TABLE `promotion`
  ADD PRIMARY KEY (`promotion_id`);

--
-- Indexes for table `promotional_ads`
--
ALTER TABLE `promotional_ads`
  ADD PRIMARY KEY (`promotional_ads_id`);

--
-- Indexes for table `rating_review`
--
ALTER TABLE `rating_review`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`reservation_id`);

--
-- Indexes for table `restaurant`
--
ALTER TABLE `restaurant`
  ADD PRIMARY KEY (`rest_id`);

--
-- Indexes for table `rest_guest`
--
ALTER TABLE `rest_guest`
  ADD PRIMARY KEY (`rest_id`,`guest_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`settings_id`);

--
-- Indexes for table `sharing`
--
ALTER TABLE `sharing`
  ADD PRIMARY KEY (`rest_id`,`year`,`month`) USING BTREE;

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`);

--
-- Indexes for table `table`
--
ALTER TABLE `table`
  ADD PRIMARY KEY (`table_id`);

--
-- Indexes for table `time_slot`
--
ALTER TABLE `time_slot`
  ADD PRIMARY KEY (`time_slot_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_group`
--
ALTER TABLE `user_group`
  ADD PRIMARY KEY (`user_group_id`);

--
-- Indexes for table `visitor`
--
ALTER TABLE `visitor`
  ADD PRIMARY KEY (`rest_id`,`month`,`year`);

--
-- Indexes for table `voucher`
--
ALTER TABLE `voucher`
  ADD PRIMARY KEY (`voucher_id`);

--
-- Indexes for table `voucher_type`
--
ALTER TABLE `voucher_type`
  ADD PRIMARY KEY (`voucher_type_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `chat`
--
ALTER TABLE `chat`
  MODIFY `chat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `conversation`
--
ALTER TABLE `conversation`
  MODIFY `conversation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cus_points`
--
ALTER TABLE `cus_points`
  MODIFY `cus_points_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `cus_promo_redemption`
--
ALTER TABLE `cus_promo_redemption`
  MODIFY `cus_promo_redemption_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cus_rest_history`
--
ALTER TABLE `cus_rest_history`
  MODIFY `cus_rest_history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `dining_style`
--
ALTER TABLE `dining_style`
  MODIFY `dining_style_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `food_item`
--
ALTER TABLE `food_item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `login_activity`
--
ALTER TABLE `login_activity`
  MODIFY `login_activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `permission`
--
ALTER TABLE `permission`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `promotion`
--
ALTER TABLE `promotion`
  MODIFY `promotion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `promotional_ads`
--
ALTER TABLE `promotional_ads`
  MODIFY `promotional_ads_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `settings_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `table`
--
ALTER TABLE `table`
  MODIFY `table_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `time_slot`
--
ALTER TABLE `time_slot`
  MODIFY `time_slot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_group`
--
ALTER TABLE `user_group`
  MODIFY `user_group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `voucher`
--
ALTER TABLE `voucher`
  MODIFY `voucher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `voucher_type`
--
ALTER TABLE `voucher_type`
  MODIFY `voucher_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
