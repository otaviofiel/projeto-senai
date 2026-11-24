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
  `status` enum('pendente','aprovado','recusado','banido','admin') DEFAULT 'pendente',
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `alunos`
--

INSERT INTO `alunos` (`id`, `nome`, `escola`, `turma`, `email`, `status`, `data_cadastro`) VALUES
(1, 'Lucas Gabriel Silva', 'SENAI Ourinhos', '2º DS - Tarde', 'lucas.silva@aluno.senai.br', 'aprovado', '2026-08-01 10:15:00'),
(2, 'Beatriz Martins Oliveira', 'SENAI Ourinhos', '1º Mecatrônica', 'beatriz.martins@aluno.senai.br', 'aprovado', '2026-08-01 10:45:00'),
(3, 'Matheus Henrique Santos', 'SENAI Ourinhos', '3º Eletroeletrônica', 'matheus.santos@aluno.senai.br', 'admin', '2026-08-02 08:30:00'),
(4, 'Ana Clara Ferreira', 'SENAI Ourinhos', '2º DS - Manhã', 'ana.ferreira@aluno.senai.br', 'aprovado', '2026-08-02 09:20:00'),
(5, 'Guilherme Rocha Lima', 'SENAI Ourinhos', '1º Redes de Computadores', 'guilherme.lima@aluno.senai.br', 'pendente', '2026-08-03 11:10:00'),
(6, 'Mariana Costa Souza', 'SENAI Ourinhos', '2º Automação Industrial', 'mariana.souza@aluno.senai.br', 'aprovado', '2026-08-03 14:05:00'),
(7, 'Felipe Rodrigues Alves', 'SENAI Ourinhos', '1º DS - Tarde', 'felipe.alves@aluno.senai.br', 'banido', '2026-08-04 15:30:00'),
(8, 'Camila Barbosa Ribeiro', 'SENAI Ourinhos', '3º Mecânica', 'camila.ribeiro@aluno.senai.br', 'aprovado', '2026-08-04 16:45:00'),
(9, 'Pedro Henrique Gomes', 'SENAI Ourinhos', '2º Mecatrônica', 'pedro.gomes@aluno.senai.br', 'pendente', '2026-08-05 08:15:00'),
(10, 'Larissa Fernandes Dias', 'SENAI Ourinhos', '1º DS - Manhã', 'larissa.dias@aluno.senai.br', 'aprovado', '2026-08-05 09:50:00'),
(11, 'Thiago Augusto Vieira', 'SENAI Ourinhos', '2º Eletroeletrônica', 'thiago.vieira@aluno.senai.br', 'aprovado', '2026-08-06 10:25:00'),
(12, 'Isabela Cristina Castro', 'SENAI Ourinhos', '3º Automação Industrial', 'isabela.castro@aluno.senai.br', 'recusado', '2026-08-06 11:40:00'),
(13, 'Bruno Vinicius Cardoso', 'SENAI Ourinhos', '1º Logística', 'bruno.cardoso@aluno.senai.br', 'aprovado', '2026-08-07 13:10:00'),
(14, 'Gabriela Nogueira Pinto', 'SENAI Ourinhos', '2º Logística', 'gabriela.pinto@aluno.senai.br', 'pendente', '2026-08-07 14:35:00'),
(15, 'Rafael Almeida Correia', 'SENAI Ourinhos', '3º DS - Noite', 'rafael.correia@aluno.senai.br', 'aprovado', '2026-08-08 08:00:00'),
(16, 'Juliana Mendes Duarte', 'SENAI Ourinhos', '1º Mecânica', 'juliana.duarte@aluno.senai.br', 'aprovado', '2026-08-08 09:30:00'),
(17, 'Leonardo Pires Monteiro', 'SENAI Ourinhos', '2º Redes de Computadores', 'leonardo.monteiro@aluno.senai.br', 'aprovado', '2026-08-08 11:20:00'),
(18, 'Sophia Helena Carvalho', 'SENAI Ourinhos', '1º DS - Tarde', 'sophia.carvalho@aluno.senai.br', 'pendente', '2026-08-09 10:00:00'),
(19, 'Enzo Gabriel Ramos', 'SENAI Ourinhos', '2º Mecânica', 'enzo.ramos@aluno.senai.br', 'aprovado', '2026-08-09 14:15:00'),
(20, 'Leticia Vasconcelos Prado', 'SENAI Ourinhos', '3º Mecatrônica', 'leticia.prado@aluno.senai.br', 'aprovado', '2026-08-09 16:50:00'),
(21, 'Arthur Henrique Moreira', 'SENAI Ourinhos', '2º DS - Tarde', 'arthur.moreira@aluno.senai.br', 'aprovado', '2026-08-10 09:10:00'),
(22, 'Yasmin Vitória Farias', 'SENAI Ourinhos', '1º Eletroeletrônica', 'yasmin.farias@aluno.senai.br', 'aprovado', '2026-08-10 11:30:00');

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
  `imagem` varchar(500) DEFAULT NULL,
  `estoque` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `categoria`, `preco`, `imagem`, `estoque`) VALUES
(1, 'Coxinha de Frango com Catupiry', 'Salgados', 6.50, 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?w=400&q=80', 35),
(2, 'Coca-Cola Lata 350ml', 'Bebidas', 5.00, 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=400&q=80', 60),
(3, 'Bolo de Pote Ninho com Morango', 'Doces', 8.00, 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400&q=80', 25),
(4, 'Hambúrguer Artesanal Especial SENAI', 'Lanches', 12.00, 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&q=80', 20),
(5, 'Enroladinho Folhado Presunto e Queijo', 'Salgados', 6.00, 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=400&q=80', 30),
(6, 'Pão de Queijo Tradicional Mineiro', 'Salgados', 4.50, 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&q=80', 50),
(7, 'Misto Quente Especial na Chapa', 'Lanches', 7.50, 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?w=400&q=80', 25),
(8, 'Suco Natural de Laranja 400ml', 'Bebidas', 6.00, 'https://images.unsplash.com/photo-1613478223719-2ab802602423?w=400&q=80', 40),
(9, 'Torta Holandesa Cremosa Individual', 'Doces', 8.50, 'https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?w=400&q=80', 20),
(10, 'Kibe Recheado com Catupiry', 'Salgados', 6.50, 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=400&q=80', 25),
(11, 'Esfiha de Carne Bovina Temperada', 'Salgados', 6.00, 'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?w=400&q=80', 30),
(12, 'Sanduíche Natural de Frango com Salada', 'Lanches', 8.50, 'https://images.unsplash.com/photo-1509722747041-616f39b57569?w=400&q=80', 18),
(13, 'X-Bacon Especial Burger', 'Lanches', 14.00, 'https://images.unsplash.com/photo-1553979459-d2229ba7433b?w=400&q=80', 15),
(14, 'Brigadeiro Gourmet Tradicional', 'Doces', 3.50, 'https://images.unsplash.com/photo-1599785209707-a456fc1337bb?w=400&q=80', 45),
(15, 'Pudim de Leite Condensado no Pote', 'Doces', 7.00, 'https://images.unsplash.com/photo-1517427294548-d61804eec7ce?w=400&q=80', 22),
(16, 'Cookie Artesanal com Gotas de Chocolate', 'Doces', 5.00, 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=400&q=80', 35),
(17, 'Guaraná Antarctica Lata 350ml', 'Bebidas', 5.00, 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?w=400&q=80', 50),
(18, 'Suco de Polpa de Maracujá 400ml', 'Bebidas', 6.00, 'https://images.unsplash.com/photo-1536935338788-846bb9981813?w=400&q=80', 30),
(19, 'Água Mineral sem Gás 500ml', 'Bebidas', 3.00, 'https://images.unsplash.com/photo-1548839140-29a749e1bc4e?w=400&q=80', 80),
(20, 'Pastel Assado Integral Ricota e Espinafre', 'Salgados', 7.00, 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=400&q=80', 20);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de tabela `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
