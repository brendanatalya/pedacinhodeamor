create table usuarios(id INT PRIMARY KEY AUTO_INCREMENT,
nome VARCHAR(100) NOT NULL,
email VARCHAR(150) NOT NULL UNIQUE,
cpf CHAR(11) NOT NULL UNIQUE,
telefone VARCHAR(20),
endereco VARCHAR(255),
senha VARCHAR(255) NOT NULL,
tipo ENUM('admin', 'cliente') NOT NULL;)