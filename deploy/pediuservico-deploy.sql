/*M!999999\- enable the sandbox mode */ 

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `avaliacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `avaliacoes` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `codpedido` int(11) DEFAULT NULL,
  `codcadastro` int(11) DEFAULT NULL,
  `codcliente` int(11) DEFAULT NULL,
  `nota` int(11) DEFAULT NULL,
  `comentario` text DEFAULT NULL,
  `data_hora` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `avaliacoes` WRITE;
/*!40000 ALTER TABLE `avaliacoes` DISABLE KEYS */;
/*!40000 ALTER TABLE `avaliacoes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `categoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categoria` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `codgrupo` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `proxima_fase` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=602 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `categoria` WRITE;
/*!40000 ALTER TABLE `categoria` DISABLE KEYS */;
INSERT INTO `categoria` VALUES
(101,1,'Instalação de tomadas',0),
(102,1,'Troca de disjuntor',0),
(103,1,'Instalação de chuveiro',0),
(201,2,'Conserto de vazamento',0),
(202,2,'Desentupimento',0),
(301,3,'Limpeza residencial',0),
(302,3,'Limpeza pós-obra',0),
(401,4,'Pintura de parede',0),
(501,5,'Instalação de split',0),
(502,5,'Limpeza de ar-condicionado',0),
(601,6,'Reforma de banheiro',1);
/*!40000 ALTER TABLE `categoria` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `categoria_prestador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categoria_prestador` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `codcadastro` int(11) NOT NULL,
  `codcategoria` int(11) NOT NULL,
  `codsubcategoria` int(11) NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `categoria_prestador` WRITE;
/*!40000 ALTER TABLE `categoria_prestador` DISABLE KEYS */;
INSERT INTO `categoria_prestador` VALUES
(1,1,1,101),
(2,1,1,102),
(3,1,1,103),
(4,2,2,201),
(5,2,2,202),
(6,3,4,401),
(7,4,3,301),
(8,4,3,302),
(9,5,5,501),
(10,5,5,502);
/*!40000 ALTER TABLE `categoria_prestador` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `chats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `chats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codpedido` int(11) DEFAULT NULL,
  `remetente` varchar(30) DEFAULT NULL,
  `mensagem` text DEFAULT NULL,
  `data_hora` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `chats` WRITE;
/*!40000 ALTER TABLE `chats` DISABLE KEYS */;
/*!40000 ALTER TABLE `chats` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cidades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cidades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uf` varchar(2) DEFAULT NULL,
  `nome` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cidades` WRITE;
/*!40000 ALTER TABLE `cidades` DISABLE KEYS */;
/*!40000 ALTER TABLE `cidades` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `TIPO` varchar(30) DEFAULT NULL,
  `NOME` varchar(150) DEFAULT NULL,
  `CNPJ_CPF` varchar(30) DEFAULT NULL,
  `TELEFONE` varchar(30) DEFAULT NULL,
  `CELULAR` varchar(30) DEFAULT NULL,
  `ESTADO` varchar(60) DEFAULT NULL,
  `MUNICIPIO` varchar(120) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `dataCad` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES
(1,'PF','Cliente Teste','99999999999',NULL,'27988887777','ES','Vitória',NULL,'2026-06-24 19:40:07');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `config_app`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `config_app` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chave` varchar(60) DEFAULT NULL,
  `valor` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `config_app` WRITE;
/*!40000 ALTER TABLE `config_app` DISABLE KEYS */;
/*!40000 ALTER TABLE `config_app` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `denuncias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `denuncias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codcadastro` int(11) DEFAULT NULL,
  `codpedido` int(11) DEFAULT NULL,
  `motivo` text DEFAULT NULL,
  `data_hora` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `denuncias` WRITE;
/*!40000 ALTER TABLE `denuncias` DISABLE KEYS */;
/*!40000 ALTER TABLE `denuncias` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `disparo_pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `disparo_pedidos` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `codpedido` int(11) DEFAULT NULL,
  `codcadastro` int(11) DEFAULT NULL,
  `aceito` varchar(5) DEFAULT 'n',
  `visto` tinyint(1) DEFAULT 0,
  `style` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `data_hora` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `disparo_pedidos` WRITE;
/*!40000 ALTER TABLE `disparo_pedidos` DISABLE KEYS */;
/*!40000 ALTER TABLE `disparo_pedidos` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `edicao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `edicao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codcadastro` int(11) DEFAULT NULL,
  `campo` varchar(60) DEFAULT NULL,
  `valor` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `edicao` WRITE;
/*!40000 ALTER TABLE `edicao` DISABLE KEYS */;
/*!40000 ALTER TABLE `edicao` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `endereco_prestador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `endereco_prestador` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `codcadastro` int(11) DEFAULT NULL,
  `endereco` varchar(200) DEFAULT NULL,
  `lat` varchar(50) DEFAULT NULL,
  `lon` varchar(50) DEFAULT NULL,
  `principal` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `endereco_prestador` WRITE;
/*!40000 ALTER TABLE `endereco_prestador` DISABLE KEYS */;
/*!40000 ALTER TABLE `endereco_prestador` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `estados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `estados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uf` varchar(2) DEFAULT NULL,
  `nome` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `estados` WRITE;
/*!40000 ALTER TABLE `estados` DISABLE KEYS */;
/*!40000 ALTER TABLE `estados` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `grupos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `grupos` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `grupos` WRITE;
/*!40000 ALTER TABLE `grupos` DISABLE KEYS */;
INSERT INTO `grupos` VALUES
(1,'Elétrica'),
(2,'Encanamento'),
(3,'Limpeza'),
(4,'Pintura'),
(5,'Ar-condicionado'),
(6,'Reforma');
/*!40000 ALTER TABLE `grupos` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `markers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `markers` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) DEFAULT NULL,
  `codcadastro` int(11) DEFAULT NULL,
  `valor_min` varchar(60) DEFAULT NULL,
  `valor_max` varchar(60) DEFAULT NULL,
  `lat` varchar(50) DEFAULT NULL,
  `lon` varchar(50) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `codpedido` int(11) DEFAULT NULL,
  `qtdestrelas` varchar(10) DEFAULT NULL,
  `contraproposta` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `markers` WRITE;
/*!40000 ALTER TABLE `markers` DISABLE KEYS */;
/*!40000 ALTER TABLE `markers` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `moedas_extrato`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `moedas_extrato` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `codcadastro` int(11) DEFAULT NULL,
  `tipo` varchar(30) DEFAULT NULL,
  `valor` int(11) DEFAULT NULL,
  `descricao` varchar(200) DEFAULT NULL,
  `data_hora` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `moedas_extrato` WRITE;
/*!40000 ALTER TABLE `moedas_extrato` DISABLE KEYS */;
/*!40000 ALTER TABLE `moedas_extrato` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `pagamento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagamento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codcadastro` int(11) DEFAULT NULL,
  `valor` varchar(30) DEFAULT NULL,
  `txid` varchar(100) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `data_hora` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `pagamento` WRITE;
/*!40000 ALTER TABLE `pagamento` DISABLE KEYS */;
/*!40000 ALTER TABLE `pagamento` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `parceiro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `parceiro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `TIPO` varchar(30) DEFAULT NULL,
  `STATUS` varchar(30) DEFAULT NULL,
  `PERFIL_DO_CLIENTE` varchar(60) DEFAULT NULL,
  `NOME` varchar(150) DEFAULT NULL,
  `CNPJ_CPF` varchar(30) DEFAULT NULL,
  `RG_IE` varchar(30) DEFAULT NULL,
  `TELEFONE` varchar(30) DEFAULT NULL,
  `CELULAR` varchar(30) DEFAULT NULL,
  `CELULAR2` varchar(30) DEFAULT NULL,
  `email_pessoal` varchar(150) DEFAULT NULL,
  `email_comercial` varchar(150) DEFAULT NULL,
  `ENDERECO` varchar(200) DEFAULT NULL,
  `NUMERO` varchar(30) DEFAULT NULL,
  `TIPO_IMOVEL` varchar(60) DEFAULT NULL,
  `REFERENCIA` varchar(200) DEFAULT NULL,
  `BAIRRO` varchar(120) DEFAULT NULL,
  `ESTADO` varchar(60) DEFAULT NULL,
  `MUNICIPIO` varchar(120) DEFAULT NULL,
  `COD_IBGE` varchar(30) DEFAULT NULL,
  `FOTO` varchar(255) DEFAULT NULL,
  `FOTOCOMDOC` varchar(255) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `serviconao` tinyint(1) DEFAULT 0,
  `moedas` int(11) DEFAULT 0,
  `lat` varchar(50) DEFAULT NULL,
  `log` varchar(50) DEFAULT NULL,
  `ultimoAcesso` datetime DEFAULT NULL,
  `dataCad` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `parceiro` WRITE;
/*!40000 ALTER TABLE `parceiro` DISABLE KEYS */;
INSERT INTO `parceiro` VALUES
(1,'PF','ativo',NULL,'João Eletricista','11111111111',NULL,NULL,'27999990001',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'ES','Vitória',NULL,NULL,NULL,NULL,0,50,NULL,NULL,NULL,'2026-06-24 19:40:07'),
(2,'PF','ativo',NULL,'Maria Encanadora','22222222222',NULL,NULL,'27999990002',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'ES','Vila Velha',NULL,NULL,NULL,NULL,0,50,NULL,NULL,NULL,'2026-06-24 19:40:07'),
(3,'PF','ativo',NULL,'Carlos Pintor','33333333333',NULL,NULL,'27999990003',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'ES','Serra',NULL,NULL,NULL,NULL,0,50,NULL,NULL,NULL,'2026-06-24 19:40:07'),
(4,'PF','ativo',NULL,'Ana Limpeza','44444444444',NULL,NULL,'27999990004',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'ES','Cariacica',NULL,NULL,NULL,NULL,0,50,NULL,NULL,NULL,'2026-06-24 19:40:07'),
(5,'PF','ativo',NULL,'Pedro Refrigeração','55555555555',NULL,NULL,'27999990005',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'ES','Vitória',NULL,NULL,NULL,NULL,0,50,NULL,NULL,NULL,'2026-06-24 19:40:07');
/*!40000 ALTER TABLE `parceiro` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `pedido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedido` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) DEFAULT NULL,
  `codcli` int(11) DEFAULT NULL,
  `NOME` varchar(150) DEFAULT NULL,
  `CNPJ_CPF` varchar(30) DEFAULT NULL,
  `CELULAR` varchar(30) DEFAULT NULL,
  `categoria` int(11) DEFAULT NULL,
  `subcategoria` int(11) DEFAULT NULL,
  `servicos` text DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `audio` varchar(255) DEFAULT NULL,
  `foto_` varchar(255) DEFAULT NULL,
  `availability` varchar(60) DEFAULT NULL,
  `data_hora` datetime DEFAULT current_timestamp(),
  `lat` varchar(50) DEFAULT NULL,
  `log` varchar(50) DEFAULT NULL,
  `local` varchar(200) DEFAULT NULL,
  `tempo` varchar(60) DEFAULT NULL,
  `valor` varchar(60) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `pedido` WRITE;
/*!40000 ALTER TABLE `pedido` DISABLE KEYS */;
/*!40000 ALTER TABLE `pedido` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `pega_contato`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pega_contato` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) DEFAULT NULL,
  `celular` varchar(30) DEFAULT NULL,
  `codpedido` int(11) DEFAULT NULL,
  `codcadastro` int(11) DEFAULT NULL,
  `codcliente` int(11) DEFAULT NULL,
  `aceito_orcamento` varchar(5) DEFAULT NULL,
  `data_hora` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `pega_contato` WRITE;
/*!40000 ALTER TABLE `pega_contato` DISABLE KEYS */;
/*!40000 ALTER TABLE `pega_contato` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `push_fila`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `push_fila` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codcadastro` int(11) DEFAULT NULL,
  `titulo` varchar(150) DEFAULT NULL,
  `mensagem` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pendente',
  `data_hora` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `push_fila` WRITE;
/*!40000 ALTER TABLE `push_fila` DISABLE KEYS */;
/*!40000 ALTER TABLE `push_fila` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `push_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `push_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codcadastro` int(11) DEFAULT NULL,
  `token` text DEFAULT NULL,
  `endpoint` text DEFAULT NULL,
  `p256dh` text DEFAULT NULL,
  `auth` text DEFAULT NULL,
  `tipo` varchar(30) DEFAULT NULL,
  `data_hora` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `push_tokens` WRITE;
/*!40000 ALTER TABLE `push_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `push_tokens` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `quantidade_pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `quantidade_pedidos` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `codcadastro` int(11) DEFAULT NULL,
  `quantidade` int(11) DEFAULT 0,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `quantidade_pedidos` WRITE;
/*!40000 ALTER TABLE `quantidade_pedidos` DISABLE KEYS */;
/*!40000 ALTER TABLE `quantidade_pedidos` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `subcategoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `subcategoria` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `codcategoria` int(11) NOT NULL,
  `codgrupo` int(11) DEFAULT NULL,
  `titulo` varchar(150) NOT NULL,
  `nome` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=502 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `subcategoria` WRITE;
/*!40000 ALTER TABLE `subcategoria` DISABLE KEYS */;
INSERT INTO `subcategoria` VALUES
(101,101,1,'Instalação de tomadas','Instalação de tomadas'),
(201,201,2,'Conserto de vazamento','Conserto de vazamento'),
(301,301,3,'Limpeza residencial','Limpeza residencial'),
(401,401,4,'Pintura de parede','Pintura de parede'),
(501,501,5,'Instalação de split','Instalação de split');
/*!40000 ALTER TABLE `subcategoria` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `timer_acordo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `timer_acordo` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `codpedido` int(11) DEFAULT NULL,
  `codcadastro` int(11) DEFAULT NULL,
  `inicio` datetime DEFAULT NULL,
  `fim` datetime DEFAULT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `timer_acordo` WRITE;
/*!40000 ALTER TABLE `timer_acordo` DISABLE KEYS */;
/*!40000 ALTER TABLE `timer_acordo` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) DEFAULT NULL,
  `celular` varchar(30) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `tipo` varchar(30) DEFAULT NULL,
  `dataCad` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `verificacoes_usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `verificacoes_usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `celular` varchar(30) DEFAULT NULL,
  `codigo` varchar(10) DEFAULT NULL,
  `verificado` tinyint(1) DEFAULT 0,
  `data_hora` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `verificacoes_usuario` WRITE;
/*!40000 ALTER TABLE `verificacoes_usuario` DISABLE KEYS */;
/*!40000 ALTER TABLE `verificacoes_usuario` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `verification_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `verification_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(30) DEFAULT NULL,
  `code` varchar(10) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `verification_codes` WRITE;
/*!40000 ALTER TABLE `verification_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `verification_codes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

