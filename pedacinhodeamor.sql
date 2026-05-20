-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 17/05/2026 às 00:56
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `pedacinhodeamor`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens_pedido`
--

CREATE TABLE `itens_pedido` (
  `id` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `qtd` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `observacao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `itens_pedido`
--

INSERT INTO `itens_pedido` (`id`, `id_pedido`, `id_produto`, `qtd`, `preco_unitario`, `subtotal`, `observacao`) VALUES
(1, 7, 1, 1, 12.00, 12.00, ''),
(2, 8, 1, 1, 12.00, 12.00, ''),
(3, 11, 1, 1, 12.00, 12.00, ''),
(4, 12, 4, 1, 12.22, 12.22, ''),
(5, 13, 1, 1, 12.00, 12.00, ''),
(6, 14, 4, 1, 12.22, 12.22, ''),
(7, 15, 1, 2, 12.00, 24.00, '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pendente',
  `observacao` text DEFAULT NULL,
  `tipo` enum('normal','personalizado') NOT NULL DEFAULT 'normal',
  `imagem_referencia` varchar(255) DEFAULT NULL,
  `qtd_itens` int(11) NOT NULL DEFAULT 0,
  `data_pedido` datetime NOT NULL DEFAULT current_timestamp(),
  `data_entrega` datetime NOT NULL,
  `forma_pagamento` varchar(50) DEFAULT NULL,
  `tipo_entrega` enum('retirada','entrega') DEFAULT 'retirada',
  `hora_entrega` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pedidos`
--

INSERT INTO `pedidos` (`id`, `id_cliente`, `valor_total`, `status`, `observacao`, `tipo`, `imagem_referencia`, `qtd_itens`, `data_pedido`, `data_entrega`, `forma_pagamento`, `tipo_entrega`, `hora_entrega`) VALUES
(1, 1, 24.28, 'pendente', '', '', NULL, 0, '2026-05-16 19:10:51', '2026-05-27 00:00:00', NULL, 'retirada', '12:12:00'),
(2, 1, 24.28, 'pendente', '', '', NULL, 0, '2026-05-16 19:11:39', '2026-05-28 00:00:00', NULL, 'retirada', '11:11:00'),
(3, 1, 24.28, 'pendente', '', '', NULL, 0, '2026-05-16 19:12:14', '2026-05-29 00:00:00', NULL, 'retirada', '11:01:00'),
(4, 1, 24.28, 'pendente', '', '', NULL, 0, '2026-05-16 19:12:57', '2026-05-28 00:00:00', NULL, 'retirada', '11:01:00'),
(5, 1, 24.28, 'pendente', '', '', NULL, 0, '2026-05-16 19:15:50', '2026-05-29 00:00:00', NULL, 'retirada', '11:01:00'),
(6, 1, 24.28, 'pendente', '', '', NULL, 0, '2026-05-16 19:15:55', '2026-05-29 00:00:00', NULL, 'retirada', '11:01:00'),
(7, 1, 24.28, 'pendente', '', '', NULL, 0, '2026-05-16 19:17:14', '2026-05-22 00:00:00', NULL, 'retirada', '11:01:00'),
(8, 1, 24.28, 'pendente', '', '', NULL, 0, '2026-05-16 19:18:24', '2026-05-29 00:00:00', NULL, 'retirada', '11:01:00'),
(9, 1, 24.28, 'pendente', '', 'personalizado', NULL, 1, '2026-05-16 19:22:27', '2026-05-29 00:00:00', 'WhatsApp', 'retirada', '11:01:00'),
(10, 1, 24.28, 'pendente', '', 'personalizado', NULL, 1, '2026-05-16 19:24:18', '2026-05-22 00:00:00', 'WhatsApp', 'retirada', '11:01:00'),
(11, 1, 24.28, 'pendente', '', 'personalizado', NULL, 1, '2026-05-16 19:25:33', '2026-05-28 00:00:00', 'WhatsApp', 'retirada', '11:01:00'),
(12, 1, 24.50, 'pendente', '', 'normal', NULL, 1, '2026-05-16 19:28:53', '2026-05-29 00:00:00', 'WhatsApp', 'entrega', '13:43:00'),
(13, 1, 24.28, 'pendente', '', 'normal', NULL, 1, '2026-05-16 19:30:13', '2026-05-28 00:00:00', 'WhatsApp', 'entrega', '19:32:00'),
(14, 1, 24.50, 'em_preparacao', '', 'normal', NULL, 1, '2026-05-16 19:33:29', '2026-06-06 00:00:00', 'WhatsApp', 'entrega', '14:04:00'),
(15, 1, 36.28, 'entregue', '', 'normal', NULL, 1, '2026-05-16 19:34:28', '2026-05-22 00:00:00', 'WhatsApp', 'entrega', '21:34:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `disponivel` tinyint(1) NOT NULL DEFAULT 1,
  `tipo` enum('salgado','doce','bolo','personalizado') NOT NULL,
  `imagem_referencia` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `descricao`, `preco`, `disponivel`, `tipo`, `imagem_referencia`) VALUES
(1, 'camafeu', 'bla bla', 12.00, 1, 'doce', 'imagens/uploads/produtos/6a08d5e796714_23.jpg'),
(4, 'dasd', 'sda', 12.22, 1, 'bolo', 'imagens/uploads/produtos/6a08d5e0869f5_25.jpg'),
(7, 'das', 'sda', 232.31, 1, 'bolo', 'imagens/uploads/produtos/1778963920_22.jpg');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `cpf` char(11) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('admin','cliente') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `cpf`, `telefone`, `endereco`, `senha`, `tipo`) VALUES
(1, 'joao', 'joao@gmail.com', '12312312312', '1212222222', 'rua x, bairro y]', '$2y$10$o5hpmtInSFO6jwhz1WaVDevx4UOr.bFJac21xSZ9cXcLZDjWyUcWu', 'cliente'),
(3, 'Íris Pires Do Nascimento', 'irispirees@gmail.com', '', NULL, '', '$2y$10$UfG2oscilVRXmCKie0ECwepLcsJnlni/IAABaurPozRlA6vdQ/H8y', 'cliente'),
(7, 'Administrador', 'adminpda@gmail.com', '123123123', '1212222222', 'sao bento', '$2y$10$8JS374iUX6sfM/RWKasDR.XWg5WyGqA32tN96DVAjWu2SOPZvmtN6', 'admin');

CREATE TABLE `estoque_ingredientes` (
    'id'            INT            NOT NULL AUTO_INCREMENT,
    'nome'          VARCHAR(100)   NOT NULL,
    'unidade'       VARCHAR(20)    NOT NULL,
    'qtd_estoque'   DECIMAL(10,3)  NOT NULL DEFAULT 0.000,
    'qtd_minima'    DECIMAL(10,3)  NOT NULL DEFAULT 0.000,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `itens_pedido`
--
ALTER TABLE `itens_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_produto` (`id_produto`);

--
-- Índices de tabela `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cpf` (`cpf`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `itens_pedido`
--
ALTER TABLE `itens_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `itens_pedido`
--
ALTER TABLE `itens_pedido`
  ADD CONSTRAINT `itens_pedido_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `itens_pedido_ibfk_2` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id`);

--
-- Restrições para tabelas `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
