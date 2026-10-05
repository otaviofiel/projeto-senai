-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 09/08/2026 às 23:16
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
-- Banco de dados: `cantina_senai`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `administradores`
--

CREATE TABLE `administradores` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `escola` varchar(150) NOT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `administradores`
--

INSERT INTO `administradores` (`id`, `nome`, `email`, `senha`, `escola`, `data_cadastro`) VALUES
(1, 'Administrador', 'admin@filazero.com', '123456', 'SENAI Ourinhos', '2026-08-08 02:07:46');

-- --------------------------------------------------------

--
-- Estrutura para tabela `alunos`
--

CREATE TABLE `alunos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `escola` varchar(150) NOT NULL,
  `turma` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `status` enum('pendente','aprovado','recusado') DEFAULT 'pendente',
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `senha` int(11) NOT NULL,
  `itens` text NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` varchar(20) DEFAULT 'Pendente',
  `data_hora` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pedidos`
--

INSERT INTO `pedidos` (`id`, `senha`, `itens`, `total`, `status`, `data_hora`) VALUES
(1, 4, '[{\"nome\":\"Coxinha de Frango\",\"preco\":6.5,\"imagem\":\"https://placehold.co/150x150/eeeeee/999999?text=Coxinha\",\"quantidade\":1},{\"nome\":\"Coca-Cola Lata\",\"preco\":5,\"imagem\":\"https://placehold.co/150x150/eeeeee/999999?text=Refrigerante\",\"quantidade\":1}]', 11.50, 'Finalizado', '2026-07-01 00:19:04'),
(2, 33, '[{\"nome\":\"Coxinha de Frango\",\"preco\":6.5,\"imagem\":\"https://placehold.co/150x150/eeeeee/999999?text=Coxinha\",\"quantidade\":1},{\"nome\":\"Coca-Cola Lata\",\"preco\":5,\"imagem\":\"https://placehold.co/150x150/eeeeee/999999?text=Refrigerante\",\"quantidade\":1}]', 11.50, 'Finalizado', '2026-07-01 00:20:06'),
(3, 65, '[{\"nome\":\"Bolo de Pote\",\"preco\":8,\"imagem\":\"https://placehold.co/150x150/eeeeee/999999?text=Bolo\",\"quantidade\":1},{\"nome\":\"Hambúrguer\",\"preco\":12,\"imagem\":\"https://placehold.co/150x150/eeeeee/999999?text=Hamburguer\",\"quantidade\":1}]', 20.00, 'Finalizado', '2026-07-01 00:27:47'),
(4, 36, '[{\"nome\":\"Bolo de Pote\",\"preco\":8,\"imagem\":\"https://placehold.co/150x150/eeeeee/999999?text=Bolo\",\"quantidade\":1},{\"nome\":\"Coca-Cola Lata\",\"preco\":5,\"imagem\":\"https://placehold.co/150x150/eeeeee/999999?text=Refrigerante\",\"quantidade\":2},{\"nome\":\"Coxinha de Frango\",\"preco\":6.5,\"imagem\":\"https://placehold.co/150x150/eeeeee/999999?text=Coxinha\",\"quantidade\":1}]', 24.50, 'Pendente', '2026-08-08 20:22:38');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `estoque` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `categoria`, `preco`, `imagem`, `estoque`) VALUES
(1, 'Coxinha de Frango', 'Salgados', 6.50, 'https://placehold.co/150x150/eeeeee/999999?text=Coxinha', 20),
(2, 'Coca-Cola Lata', 'Bebidas', 5.00, 'https://placehold.co/150x150/eeeeee/999999?text=Refrigerante', 45),
(3, 'Bolo de Pote', 'Doces', 8.00, 'https://placehold.co/150x150/eeeeee/999999?text=Bolo', 10),
(4, 'Hambúrguer', 'Lanches', 12.00, 'https://placehold.co/150x150/eeeeee/999999?text=Hamburguer', 15);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `alunos`
--
ALTER TABLE `alunos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

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
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `alunos`
--
ALTER TABLE `alunos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
