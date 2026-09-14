-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 26/08/2026 às 15:45
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

--
-- Despejando dados para a tabela `avaliacoes`
--

INSERT INTO `avaliacoes` (`id`, `id_pedido`, `id_cliente`, `nota_produto`, `nota_atend`, `comentario`, `criado_em`) VALUES
(1, 16, 8, 5, 5, 'Amei! Produto chegou fresquinho e o atendimento foi ótimo. Com certeza vou pedir de novo!', '2026-05-29 15:45:00'),
(2, 17, 1, 5, 4, 'Tudo muito gostoso! O croissant estava perfeito e os doces vieram bem fresquinhos.', '2026-06-03 17:10:00');

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
(7, 15, 1, 2, 12.00, 24.00, ''),
(8, 16, 1, 3, 12.00, 36.00, ''),
(9, 17, 13, 1, 4.50, 4.50, ''),
(10, 17, 14, 1, 9.00, 9.00, ''),
(11, 17, 3, 2, 3.00, 6.00, ''),
(0, 0, 12, 3, 8.50, 25.50, '');

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
(1, 1, 24.00, 'pendente', NULL, 'normal', NULL, 2, '2026-06-01 08:56:54', '2026-06-01 13:56:30', NULL, 'retirada', '00:00:00'),
(16, 8, 36.00, 'entregue', NULL, 'normal', NULL, 3, '2026-05-28 10:30:00', '2026-05-29 00:00:00', 'pix', 'entrega', '14:00:00'),
(17, 1, 19.50, 'entregue', NULL, 'normal', NULL, 3, '2026-06-02 10:15:00', '2026-06-03 00:00:00', 'pix', 'retirada', '16:30:00');

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `descricao`, `preco`, `disponivel`, `tipo`, `imagem_referencia`) VALUES
(1, 'Coxinha', 'Coxinha de frango', 6.00, 1, 'salgado', 'coxinha.jpg'),
(3, 'Brigadeiro', 'Brigadeiro tradicional', 2.50, 1, 'doce', 'brigadeiro.jpg'),
(4, 'Beijinho', 'Beijinho de coco', 2.50, 1, 'doce', 'beijinho.jpg'),
(5, 'Bolo de Chocolate', 'Fatia de bolo de chocolate', 8.00, 1, 'bolo', 'bolodechocolate.jpg'),
(6, 'Bolo de Cenoura', 'Fatia de bolo de cenoura', 7.50, 1, 'bolo', 'bolodecenoura.jpg'),
(12, 'Copinho da Felicidade', 'Copinho com creme e chocolate', 8.50, 1, 'doce', 'copinho.jpg'),
(13, 'Pão de Queijo', 'Pão de queijo tradicional', 4.50, 1, 'salgado', 'paodequeijo.jpg'),
(14, 'Croissant de Presunto e Queijo', 'Croissant recheado com presunto e queijo', 9.00, 1, 'salgado', 'croissantqueijo.jpg'),
(16, 'Croissant de chocolate', 'Croissant recheado com chocolate', 8.00, 1, 'doce', 'croissantchocolate.jpg');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto_ingrediente`
--

CREATE TABLE `produto_ingrediente` (
  `id_produto` int(11) NOT NULL,
  `id_ingrediente` int(11) NOT NULL,
  `qtd_necessaria` decimal(10,3) NOT NULL
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
  `tipo` enum('admin','cliente') NOT NULL,
  `token_recuperacao` varchar(64) DEFAULT NULL,
  `token_expiracao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `cpf`, `telefone`, `endereco`, `foto`, `senha`, `tipo`, `token_recuperacao`, `token_expiracao`) VALUES
(1, 'joao', 'joao@gmail.com', '12312312312', '1212222222', 'rua x, bairro y]', NULL, '$2y$10$o5hpmtInSFO6jwhz1WaVDevx4UOr.bFJac21xSZ9cXcLZDjWyUcWu', 'cliente', NULL, NULL),
(3, 'Íris Pires Do Nascimento', 'irispirees@gmail.com', '', NULL, '', NULL, '$2y$10$UfG2oscilVRXmCKie0ECwepLcsJnlni/IAABaurPozRlA6vdQ/H8y', 'cliente', 'a1818baa8efb0ced927a87703b333a596a016b110e6c11948016079b78d87517', '2026-08-26 17:40:58'),
(7, 'Administrador', 'adminpda@gmail.com', '123123123', '1212222222', 'sao bento', NULL, '$2y$10$8JS374iUX6sfM/RWKasDR.XWg5WyGqA32tN96DVAjWu2SOPZvmtN6', 'admin', NULL, NULL),
(8, 'brenda', 'brenda@gmail.com', '', NULL, '', NULL, '$2y$10$.iOHWrTR0uJoY.bjH4IUvemfk9pSXpKv6oyYhDRLWYIyTZPBLzIiu', 'cliente', NULL, NULL),
(9, 'Iris', 'iris.nascimento3@aluno.cps.sp.gov.br', '', NULL, NULL, NULL, '$2y$10$0GYhmmNxBAQ8/R.hXgbVxusO2hRutxwjzPOsgHaB6X65qwfPPMzsC', 'cliente', NULL, NULL);

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
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `produto_ingrediente`
--
ALTER TABLE `produto_ingrediente`
  ADD PRIMARY KEY (`id_produto`,`id_ingrediente`),
  ADD KEY `id_ingrediente` (`id_ingrediente`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `estoque_ingredientes`
--
ALTER TABLE `estoque_ingredientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `avaliacoes`
--
ALTER TABLE `avaliacoes`
  ADD CONSTRAINT `fk_aval_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_aval_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `produto_ingrediente`
--
ALTER TABLE `produto_ingrediente`
  ADD CONSTRAINT `produto_ingrediente_ibfk_1` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id`),
  ADD CONSTRAINT `produto_ingrediente_ibfk_2` FOREIGN KEY (`id_ingrediente`) REFERENCES `estoque_ingredientes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
