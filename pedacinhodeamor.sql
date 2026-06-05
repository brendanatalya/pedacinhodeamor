-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 01/06/2026 às 14:36
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
-- Estrutura para tabela `avaliacoes`
--

CREATE TABLE `avaliacoes` (
  `id` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `nota_produto` tinyint(1) NOT NULL,
  `nota_atend` tinyint(1) NOT NULL,
  `comentario` text DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque_ingredientes`
--

CREATE TABLE `estoque_ingredientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `unidade` varchar(20) NOT NULL,
  `qtd_estoque` decimal(10,3) NOT NULL DEFAULT 0.000,
  `qtd_minima` decimal(10,3) NOT NULL DEFAULT 0.000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `estoque_ingredientes`
--

INSERT INTO `estoque_ingredientes` (`id`, `nome`, `unidade`, `qtd_estoque`, `qtd_minima`) VALUES
(1, 'Farinha de Trigo', 'kg', 10.000, 4.000),
(2, 'Açúcar', 'kg', 12.000, 3.000),
(3, 'Leite em Pó', 'kg', 4.000, 2.000);

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
(1, 1, 24.00, 'pendente', NULL, 'normal', NULL, 2, '2026-06-01 08:56:54', '2026-06-01 13:56:30', NULL, 'retirada', '00:00:00');

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

INSERT INTO `usuarios` (`id`, `nome`, `email`, `cpf`, `telefone`, `endereco`, `foto`, `senha`, `tipo`) VALUES
(1, 'joao', 'joao@gmail.com', '12312312312', '1212222222', 'rua x, bairro y]', NULL, '$2y$10$o5hpmtInSFO6jwhz1WaVDevx4UOr.bFJac21xSZ9cXcLZDjWyUcWu', 'cliente'),
(3, 'Íris Pires Do Nascimento', 'irispirees@gmail.com', '', NULL, '', NULL, '$2y$10$UfG2oscilVRXmCKie0ECwepLcsJnlni/IAABaurPozRlA6vdQ/H8y', 'cliente'),
(7, 'Administrador', 'adminpda@gmail.com', '123123123', '1212222222', 'sao bento', NULL, '$2y$10$8JS374iUX6sfM/RWKasDR.XWg5WyGqA32tN96DVAjWu2SOPZvmtN6', 'admin'),
(8, 'brendinha', 'brendinha@gmail.com', NULL, NULL, NULL, '$2y$10$8JS374iUX6sfM/RWKasDR.XWg5WyGqA32tN96DVAjWu2SOPZvmtN6', 'cliente');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `avaliacoes`
--
ALTER TABLE `avaliacoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_avaliacao_pedido` (`id_pedido`),
  ADD KEY `fk_aval_cliente` (`id_cliente`);

--
-- Índices de tabela `estoque_ingredientes`
--
ALTER TABLE `estoque_ingredientes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `avaliacoes`
--
ALTER TABLE `avaliacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `estoque_ingredientes`
--
ALTER TABLE `estoque_ingredientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `avaliacoes`
--
ALTER TABLE `avaliacoes`
  ADD CONSTRAINT `fk_aval_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_aval_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
