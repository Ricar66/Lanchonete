-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 08/05/2025 às 00:36
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
-- Banco de dados: `lanchonete_db`
--
CREATE DATABASE IF NOT EXISTS `lanchonete_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `lanchonete_db`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_adicionais`
--

DROP TABLE IF EXISTS `tb_adicionais`;
CREATE TABLE `tb_adicionais` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) DEFAULT NULL,
  `nome` varchar(100) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `categoria` varchar(50) NOT NULL DEFAULT 'geral',
  `descricao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_adicionais`
--

INSERT INTO `tb_adicionais` (`id`, `produto_id`, `nome`, `preco`, `categoria`, `descricao`, `ativo`) VALUES
(1, NULL, 'tomate', 6.00, 'geral', NULL, 1),
(2, NULL, 'catupiry', 6.00, 'geral', NULL, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_administradores`
--

DROP TABLE IF EXISTS `tb_administradores`;
CREATE TABLE `tb_administradores` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_administradores`
--

INSERT INTO `tb_administradores` (`id`, `usuario`, `senha`) VALUES
(2, 'admin', '123');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_categorias`
--

DROP TABLE IF EXISTS `tb_categorias`;
CREATE TABLE `tb_categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `icone` varchar(50) DEFAULT 'fa-utensils'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_categorias`
--

INSERT INTO `tb_categorias` (`id`, `nome`, `icone`) VALUES
(1, 'Lanches', 'fa-burger'),
(2, 'Porções', 'fa-drumstick-bite'),
(3, 'Bebidas', 'fa-glass-water'),
(4, 'Sobremesas', 'fa-ice-cream');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_clientes`
--

DROP TABLE IF EXISTS `tb_clientes`;
CREATE TABLE `tb_clientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `data_cadastro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_clientes`
--

INSERT INTO `tb_clientes` (`id`, `nome`, `data_cadastro`) VALUES
(1, 'Ricardo C m moretti', '2025-04-24 12:18:43'),
(2, 'ALESSANDRA CRISTINA DE MARCO', '2025-05-03 13:08:32');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_enderecos`
--

DROP TABLE IF EXISTS `tb_enderecos`;
CREATE TABLE `tb_enderecos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `logradouro` varchar(100) NOT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `complemento` varchar(50) DEFAULT NULL,
  `bairro` varchar(50) NOT NULL,
  `cidade` varchar(50) NOT NULL,
  `estado` char(2) NOT NULL,
  `cep` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_enderecos`
--

INSERT INTO `tb_enderecos` (`id`, `cliente_id`, `logradouro`, `numero`, `complemento`, `bairro`, `cidade`, `estado`, `cep`) VALUES
(1, 1, 'manoel dos santos', '55', '', 'Residencial Palmares', 'Ribeirão Preto', 'SP', '14092-430'),
(2, 2, 'Joao Ferracini', '124', '', 'Residencial Palmares', 'Ribeirão Preto', 'SP', '14092-430');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_itens_pedido`
--

DROP TABLE IF EXISTS `tb_itens_pedido`;
CREATE TABLE `tb_itens_pedido` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) DEFAULT NULL,
  `produto_id` int(11) DEFAULT NULL,
  `quantidade` int(11) DEFAULT 1,
  `preco_unitario` decimal(10,2) NOT NULL,
  `adicionais` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_itens_pedido`
--

INSERT INTO `tb_itens_pedido` (`id`, `pedido_id`, `produto_id`, `quantidade`, `preco_unitario`, `adicionais`) VALUES
(1, 19, 1, 1, 32.00, 'queijo e tomate'),
(2, 20, 3, 1, 36.00, NULL),
(3, 21, 3, 1, 36.00, NULL),
(4, 22, 4, 1, 20.00, NULL),
(5, 22, 3, 1, 36.00, NULL),
(6, 23, 3, 1, 37.00, NULL),
(7, 24, 7, 1, 32.00, NULL),
(8, 25, 3, 1, 37.00, NULL),
(9, 26, 3, 1, 37.00, NULL),
(11, 28, 3, 1, 37.00, NULL),
(12, 29, 3, 1, 37.00, NULL),
(13, 30, 9, 1, 20.00, NULL),
(14, 31, 8, 1, 36.00, NULL),
(15, 32, 3, 1, 37.00, NULL),
(16, 34, 3, 1, 37.00, NULL),
(17, 36, 3, 1, 37.00, NULL),
(18, 37, 9, 1, 20.00, NULL),
(19, 38, 8, 1, 38.00, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_itens_pedido_adicionais`
--

DROP TABLE IF EXISTS `tb_itens_pedido_adicionais`;
CREATE TABLE `tb_itens_pedido_adicionais` (
  `id` int(11) NOT NULL,
  `item_pedido_id` int(11) NOT NULL,
  `adicional_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_itens_pedido_adicionais`
--

INSERT INTO `tb_itens_pedido_adicionais` (`id`, `item_pedido_id`, `adicional_id`, `quantidade`) VALUES
(1, 6, 1, 1),
(2, 8, 1, 1),
(3, 9, 1, 1),
(4, 13, 1, 1),
(5, 14, 1, 1),
(6, 15, 1, 1),
(7, 16, 1, 1),
(8, 18, 1, 1),
(9, 18, 2, 1),
(10, 19, 1, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_itens_venda`
--

DROP TABLE IF EXISTS `tb_itens_venda`;
CREATE TABLE `tb_itens_venda` (
  `id` int(11) NOT NULL,
  `venda_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 1,
  `preco_unitario` decimal(10,2) NOT NULL,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_logs`
--

DROP TABLE IF EXISTS `tb_logs`;
CREATE TABLE `tb_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `acao` varchar(100) NOT NULL,
  `detalhes` text NOT NULL,
  `data_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_pedidos`
--

DROP TABLE IF EXISTS `tb_pedidos`;
CREATE TABLE `tb_pedidos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `data_pedido` datetime DEFAULT current_timestamp(),
  `observacoes` text DEFAULT NULL,
  `status` enum('pendente','preparo','entregue','cancelado') DEFAULT 'pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_pedidos`
--

INSERT INTO `tb_pedidos` (`id`, `cliente_id`, `data_pedido`, `observacoes`, `status`) VALUES
(14, 1, '2025-05-02 11:21:15', '', 'pendente'),
(15, 1, '2025-05-02 11:40:43', '', 'pendente'),
(16, 1, '2025-05-02 11:51:10', '', 'pendente'),
(17, 1, '2025-05-02 11:58:48', '', 'pendente'),
(18, 1, '2025-05-02 11:58:55', '', 'pendente'),
(19, 1, '2025-05-02 12:07:01', '', 'pendente'),
(20, 1, '2025-05-02 12:21:55', 'bem passado', 'pendente'),
(21, 1, '2025-05-02 12:24:54', 'bem passado', 'pendente'),
(22, 1, '2025-05-02 12:25:43', '', 'pendente'),
(23, 1, '2025-05-02 12:46:26', '', 'pendente'),
(24, 1, '2025-05-02 13:10:24', '', 'pendente'),
(25, 1, '2025-05-02 15:15:49', '', 'pendente'),
(26, NULL, '2025-05-02 15:30:05', '', 'pendente'),
(28, NULL, '2025-05-02 15:56:25', '', 'pendente'),
(29, NULL, '2025-05-02 15:59:55', '', 'pendente'),
(30, 2, '2025-05-03 13:10:59', 'bem passado', 'pendente'),
(31, NULL, '2025-05-04 17:48:03', '', 'pendente'),
(32, 1, '2025-05-04 17:49:03', '', 'pendente'),
(33, NULL, '2025-05-04 18:07:37', '', 'pendente'),
(34, NULL, '2025-05-04 18:26:44', '', 'pendente'),
(35, NULL, '2025-05-04 19:31:16', '', 'pendente'),
(36, NULL, '2025-05-04 19:42:29', '', 'pendente'),
(37, 1, '2025-05-04 22:51:31', '', 'pendente'),
(38, NULL, '2025-05-07 19:27:26', '', 'pendente');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_produtos`
--

DROP TABLE IF EXISTS `tb_produtos`;
CREATE TABLE `tb_produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `categoria_id` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_produtos`
--

INSERT INTO `tb_produtos` (`id`, `nome`, `descricao`, `preco`, `ativo`, `categoria_id`) VALUES
(1, 'X - tudo', 'Tomate, Alface, Salsicha ', 32.00, 0, 1),
(3, 'Carne Queijo', 'carne, queijo, alface e tomate ', 37.00, 1, 1),
(4, 'X - tudo', 'bacon e molho ', 20.00, 0, 1),
(5, 'X - tudo', 'tomate', 32.00, 0, 1),
(6, 'tomate', '', 5.00, 0, 1),
(7, 'X - tudo', 'TOMATE E CEBOLA', 32.00, 1, 1),
(8, 'BATATA FRITA', '', 38.00, 1, 2),
(9, 'x - frango', 'frango, queijo', 20.00, 1, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_telefones`
--

DROP TABLE IF EXISTS `tb_telefones`;
CREATE TABLE `tb_telefones` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `numero` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_telefones`
--

INSERT INTO `tb_telefones` (`id`, `cliente_id`, `numero`) VALUES
(1, 1, '(16) 98813-3161'),
(2, 2, '(16) 98813-3161');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_vendas`
--

DROP TABLE IF EXISTS `tb_vendas`;
CREATE TABLE `tb_vendas` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `data_venda` datetime DEFAULT current_timestamp(),
  `valor_total` decimal(10,2) NOT NULL,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_vendas`
--

INSERT INTO `tb_vendas` (`id`, `pedido_id`, `cliente_id`, `data_venda`, `valor_total`, `observacoes`) VALUES
(3, 29, NULL, '2025-05-02 16:09:05', 37.00, NULL),
(7, 31, NULL, '2025-05-04 17:48:14', 36.00, NULL),
(12, 36, NULL, '2025-05-04 19:42:29', 37.00, NULL),
(13, 37, 1, '2025-05-04 22:51:31', 32.00, NULL),
(14, 38, NULL, '2025-05-07 19:27:26', 44.00, NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `tb_adicionais`
--
ALTER TABLE `tb_adicionais`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices de tabela `tb_administradores`
--
ALTER TABLE `tb_administradores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Índices de tabela `tb_categorias`
--
ALTER TABLE `tb_categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `tb_clientes`
--
ALTER TABLE `tb_clientes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_enderecos`
--
ALTER TABLE `tb_enderecos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Índices de tabela `tb_itens_pedido`
--
ALTER TABLE `tb_itens_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices de tabela `tb_itens_pedido_adicionais`
--
ALTER TABLE `tb_itens_pedido_adicionais`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_pedido_id` (`item_pedido_id`),
  ADD KEY `adicional_id` (`adicional_id`);

--
-- Índices de tabela `tb_itens_venda`
--
ALTER TABLE `tb_itens_venda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venda_id` (`venda_id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices de tabela `tb_logs`
--
ALTER TABLE `tb_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Índices de tabela `tb_pedidos`
--
ALTER TABLE `tb_pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Índices de tabela `tb_produtos`
--
ALTER TABLE `tb_produtos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Índices de tabela `tb_telefones`
--
ALTER TABLE `tb_telefones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Índices de tabela `tb_vendas`
--
ALTER TABLE `tb_vendas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `pedido_id` (`pedido_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `tb_adicionais`
--
ALTER TABLE `tb_adicionais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `tb_administradores`
--
ALTER TABLE `tb_administradores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `tb_categorias`
--
ALTER TABLE `tb_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `tb_clientes`
--
ALTER TABLE `tb_clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `tb_enderecos`
--
ALTER TABLE `tb_enderecos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `tb_itens_pedido`
--
ALTER TABLE `tb_itens_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `tb_itens_pedido_adicionais`
--
ALTER TABLE `tb_itens_pedido_adicionais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `tb_itens_venda`
--
ALTER TABLE `tb_itens_venda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tb_logs`
--
ALTER TABLE `tb_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tb_pedidos`
--
ALTER TABLE `tb_pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de tabela `tb_produtos`
--
ALTER TABLE `tb_produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `tb_telefones`
--
ALTER TABLE `tb_telefones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `tb_vendas`
--
ALTER TABLE `tb_vendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `tb_adicionais`
--
ALTER TABLE `tb_adicionais`
  ADD CONSTRAINT `tb_adicionais_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `tb_produtos` (`id`);

--
-- Restrições para tabelas `tb_enderecos`
--
ALTER TABLE `tb_enderecos`
  ADD CONSTRAINT `tb_enderecos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `tb_clientes` (`id`);

--
-- Restrições para tabelas `tb_itens_pedido`
--
ALTER TABLE `tb_itens_pedido`
  ADD CONSTRAINT `tb_itens_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `tb_pedidos` (`id`),
  ADD CONSTRAINT `tb_itens_pedido_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `tb_produtos` (`id`);

--
-- Restrições para tabelas `tb_itens_pedido_adicionais`
--
ALTER TABLE `tb_itens_pedido_adicionais`
  ADD CONSTRAINT `tb_itens_pedido_adicionais_ibfk_1` FOREIGN KEY (`item_pedido_id`) REFERENCES `tb_itens_pedido` (`id`),
  ADD CONSTRAINT `tb_itens_pedido_adicionais_ibfk_2` FOREIGN KEY (`adicional_id`) REFERENCES `tb_adicionais` (`id`);

--
-- Restrições para tabelas `tb_itens_venda`
--
ALTER TABLE `tb_itens_venda`
  ADD CONSTRAINT `tb_itens_venda_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `tb_vendas` (`id`),
  ADD CONSTRAINT `tb_itens_venda_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `tb_produtos` (`id`);

--
-- Restrições para tabelas `tb_logs`
--
ALTER TABLE `tb_logs`
  ADD CONSTRAINT `tb_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `tb_administradores` (`id`);

--
-- Restrições para tabelas `tb_pedidos`
--
ALTER TABLE `tb_pedidos`
  ADD CONSTRAINT `tb_pedidos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `tb_clientes` (`id`);

--
-- Restrições para tabelas `tb_produtos`
--
ALTER TABLE `tb_produtos`
  ADD CONSTRAINT `tb_produtos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `tb_categorias` (`id`);

--
-- Restrições para tabelas `tb_telefones`
--
ALTER TABLE `tb_telefones`
  ADD CONSTRAINT `tb_telefones_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `tb_clientes` (`id`);

--
-- Restrições para tabelas `tb_vendas`
--
ALTER TABLE `tb_vendas`
  ADD CONSTRAINT `tb_vendas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `tb_clientes` (`id`),
  ADD CONSTRAINT `tb_vendas_ibfk_2` FOREIGN KEY (`pedido_id`) REFERENCES `tb_pedidos` (`id`),
  ADD CONSTRAINT `tb_vendas_ibfk_3` FOREIGN KEY (`pedido_id`) REFERENCES `tb_pedidos` (`id`),
  ADD CONSTRAINT `tb_vendas_ibfk_4` FOREIGN KEY (`pedido_id`) REFERENCES `tb_pedidos` (`id`) ON DELETE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
