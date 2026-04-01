-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 16-Out-2023 às 16:06
-- Versão do servidor: 8.0.31
-- versão do PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `celke`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(220) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `events`
--

INSERT INTO `events` (`id`, `title`, `color`, `start`, `end`) VALUES
(1, 'Tutorial 1', '#FFD700', '2023-10-16 10:05:00', '2023-10-16 11:05:00'),
(2, 'Tutorial 2', '#0071c5', '2023-10-18 10:06:00', '2023-10-18 11:06:00'),
(3, 'Tutorial 3', '#40e0d0', '2023-10-20 10:07:00', '2023-10-20 11:07:00'),
(4, 'Tutorial 4', '#FFD700', '2023-10-23 10:08:00', '2023-10-23 11:08:00'),
(5, 'Tutorial 5', '#40e0d0', '2023-10-25 10:09:00', '2023-10-26 11:09:00'),
(6, 'Tutorial 6', '#0071c5', '2023-10-27 10:10:00', '2023-10-27 11:10:00'),
(7, 'Tutorial 7', '#A020F0', '2023-10-30 10:05:00', '2023-10-30 11:05:00'),
(8, 'Tutorial 8', '#8B0000', '2023-11-01 00:00:00', '2023-11-01 00:00:00'),
(9, 'Tutorial 9', '#FF4500', '2023-11-03 10:01:00', '2023-11-03 10:01:00'),
(10, 'Tutorial 10', '#228B22', '2023-11-06 10:01:00', '2023-11-06 10:01:00'),
(11, 'Tutorial 11', '#8B4513', '2023-11-08 10:01:00', '2023-11-08 10:01:00'),
(12, 'Tutorial 12', '#FFD700', '2023-11-10 10:01:00', '2023-11-10 10:01:00'),
(13, 'Tutorial 13', '#40E0D0', '2023-11-13 00:00:00', '2023-11-14 00:00:00'),
(14, 'Tutorial 14', '#436EEE', '2023-11-15 10:00:00', '2023-11-16 10:00:00'),
(15, 'Tutorial 15', '#1C1C1C', '2023-11-17 10:00:00', '2023-11-17 10:00:00'),
(16, 'Tutorial 16', '#228B22', '2023-11-20 10:00:00', '2023-11-20 10:30:00');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
