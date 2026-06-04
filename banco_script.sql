create table usuarios(
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    cpf CHAR(11) NOT NULL UNIQUE,
    telefone VARCHAR(20),
    endereco VARCHAR(255),
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'cliente') NOT NULL
    );
        INSERT INTO `usuarios` (`id`, `nome`, `email`, `cpf`, `telefone`, `endereco`, `senha`, `tipo`) 
            VALUES 
                (NULL, 'joao', 'joao@gmail.com', '12312312312', '1212222222', 'rua x, bairro y]', '$2y$10$o5hpmtInSFO6jwhz1WaVDevx4UOr.bFJac21xSZ9cXcLZDjWyUcWu', 'cliente'), 
                (NULL, 'Íris Pires Do Nascimento', 'irispirees@gmail.com', '', NULL, '', '$2y$10$UfG2oscilVRXmCKie0ECwepLcsJnlni/IAABaurPozRlA6vdQ/H8y', 'cliente'), 
                (NULL, 'Administrador', 'adminpda@gmail.com', '123123123', '1212222222', 'sao bento', '$2y$10$8JS374iUX6sfM/RWKasDR.XWg5WyGqA32tN96DVAjWu2SOPZvmtN6', 'admin')

    create table produtos(
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    disponivel BOOLEAN NOT NULL DEFAULT TRUE,
    tipo ENUM('salgado', 'doce', 'bolo', 'personalizado') NOT NULL,
    imagem_referencia VARCHAR(255) NULL
    )
        INSERT INTO `produtos` (`id`, `nome`, `descricao`, `preco`, `disponivel`, `tipo`, `imagem_referencia`) 
            VALUES 
                (NULL, 'camafeu', 'bla bla', '12.00', '3', 'doce', NULL), 
                (NULL, 'dasd', 'sda', '12.22', '1', 'bolo', NULL), 
                (NULL, 'dasdad', 'i', '12.22', '1', 'personalizado', 'uploads/produtos/1778783331_22.jpg'),
                (NULL, 'da', 'das', '0.12', '1', 'salgado', 'uploads/produtos/1778782865_21.jpg')