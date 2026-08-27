-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 23, 2024 at 06:18 AM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gstvnewsdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `epaper`
--

DROP TABLE IF EXISTS `epaper`;
CREATE TABLE IF NOT EXISTS `epaper` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `userID` int NOT NULL,
  `ecatID` int NOT NULL,
  `etitle` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `engtitle` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Story_data` json DEFAULT NULL,
  `metatitle` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metakeyword` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadesc` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `epaper`
--

INSERT INTO `epaper` (`id`, `userID`, `ecatID`, `etitle`, `engtitle`, `slug`, `Story_data`, `metatitle`, `metakeyword`, `metadesc`, `status`, `created_at`, `updated_at`) VALUES
(15, 1, 2, 'બરોડા', 'baroda', 'baroda', '[{\"image\": \"http://localhost/gstvnews/backend/public/uploads/epaper/2024/10/22/mumbai_AlkWryRm1729596912.webp\"}, {\"image\": \"http://localhost/gstvnews/backend/public/uploads/epaper/2024/10/22/surat_YnI72nJO1729596912.webp\"}, {\"image\": \"http://localhost/gstvnews/backend/public/uploads/epaper/2024/10/22/456551119_1552780258643392_4018828136637333624_n_AOo6ODWj1729596929.webp\"}]', 'બરોડા', 'બરોડા', 'બરોડા', 'Active', '2024-10-22 11:35:12', '2024-10-22 11:36:58'),
(16, 1, 7, 'રાજકોટ', 'rajkot', 'rajkot', '[{\"image\": \"http://localhost/gstvnews/backend/public/uploads/epaper/2024/10/23/20241023-6717e6c49494853681696-Binder1-0[1]_F7ieiRrr1729664009.webp\"}, {\"image\": \"http://localhost/gstvnews/backend/public/uploads/epaper/2024/10/23/20241023-6717e6c49494853681696-Binder1-0[2]_5yAMNAiQ1729664011.webp\"}]', 'રાજકોટ', 'રાજકોટ', 'રાજકોટ', 'Active', '2024-10-22 11:37:24', '2024-10-23 06:13:33');

-- --------------------------------------------------------

--
-- Table structure for table `epapercategory`
--

DROP TABLE IF EXISTS `epapercategory`;
CREATE TABLE IF NOT EXISTS `epapercategory` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `userID` int NOT NULL,
  `cattype` varchar(91) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `engtitle` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `featureImage` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metatitle` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metakeyword` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadesc` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catOrder` int NOT NULL DEFAULT '0',
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `epapercategory`
--

INSERT INTO `epapercategory` (`id`, `userID`, `cattype`, `title`, `engtitle`, `slug`, `featureImage`, `metatitle`, `metakeyword`, `metadesc`, `catOrder`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'city', 'mumbai', 'mumbai', 'mumbai', NULL, 'mumbai', 'mumbai', 'mumbai', 1, 'Active', '2024-10-22 05:39:58', '2024-10-22 07:57:27'),
(2, 1, 'magazine', 'bal bhaskar', 'balbhaskar', 'balbhaskar', NULL, 'balbhaskar', 'balbhaskar', 'balbhaskar', 2, 'Active', '2024-10-22 06:15:59', '2024-10-22 06:15:59'),
(6, 1, 'magazine', 'vapi', 'vapi', 'vapi', NULL, 'vapi', 'vapi', NULL, 6, 'Active', '2024-10-22 06:29:49', '2024-10-22 07:57:12'),
(4, 1, 'city', 'Surat', 'Surat', 'surat', NULL, 'Surat', 'Surat', 'Surat', 4, 'Active', '2024-10-22 06:22:48', '2024-10-22 06:23:02'),
(7, 1, 'city', 'rajkot', 'rajkot', 'rajkot', NULL, 'rajkot', 'rajkot', 'rajkot', 7, 'Active', '2024-10-22 09:32:01', '2024-10-22 09:32:01');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
