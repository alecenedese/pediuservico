-- Dados de exemplo para desenvolvimento
SET NAMES utf8mb4;

-- Grupos (categorias principais exibidas em buscar.php)
INSERT INTO grupos (codigo, titulo) VALUES
 (1,'Elétrica'),
 (2,'Encanamento'),
 (3,'Limpeza'),
 (4,'Pintura'),
 (5,'Ar-condicionado'),
 (6,'Reforma');

-- Categorias (subcategorias por grupo)
INSERT INTO categoria (codigo, codgrupo, titulo, proxima_fase) VALUES
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
 (601,6,'Reforma de banheiro',1);  -- próxima fase (disponível em breve)

-- Prestadores
INSERT INTO parceiro (id, TIPO, STATUS, NOME, CNPJ_CPF, CELULAR, ESTADO, MUNICIPIO, moedas) VALUES
 (1,'PF','ativo','João Eletricista','11111111111','27999990001','ES','Vitória',50),
 (2,'PF','ativo','Maria Encanadora','22222222222','27999990002','ES','Vila Velha',50),
 (3,'PF','ativo','Carlos Pintor','33333333333','27999990003','ES','Serra',50),
 (4,'PF','ativo','Ana Limpeza','44444444444','27999990004','ES','Cariacica',50),
 (5,'PF','ativo','Pedro Refrigeração','55555555555','27999990005','ES','Vitória',50);

-- Vínculo prestador <-> categoria (cp.codsubcategoria = categoria.codigo)
INSERT INTO categoria_prestador (codcadastro, codcategoria, codsubcategoria) VALUES
 (1,1,101),(1,1,102),(1,1,103),   -- João: elétrica
 (2,2,201),(2,2,202),             -- Maria: encanamento
 (3,4,401),                       -- Carlos: pintura
 (4,3,301),(4,3,302),             -- Ana: limpeza
 (5,5,501),(5,5,502);             -- Pedro: ar-condicionado

-- Subcategorias (espelho usado em buscas/autocomplete)
INSERT INTO subcategoria (codigo, codcategoria, codgrupo, titulo, nome) VALUES
 (101,101,1,'Instalação de tomadas','Instalação de tomadas'),
 (201,201,2,'Conserto de vazamento','Conserto de vazamento'),
 (301,301,3,'Limpeza residencial','Limpeza residencial'),
 (401,401,4,'Pintura de parede','Pintura de parede'),
 (501,501,5,'Instalação de split','Instalação de split');

-- Cliente de exemplo
INSERT INTO clientes (id, TIPO, NOME, CNPJ_CPF, CELULAR, ESTADO, MUNICIPIO) VALUES
 (1,'PF','Cliente Teste','99999999999','27988887777','ES','Vitória');
