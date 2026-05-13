<?php

    //mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR);
    //mexer no config, databse e no valida, ta torto
    /*criar uma função valida aqui no databse para validar o usuario, ela abrir
    a conexao aqui e retornar um vetor com os dados do usuario, se retornar nulo nao ta certo,
     se quiser manter la naquele arquivo, mudar a forma como ele consulta e puxa os dados,
    nao precisa do banco pq ele ja passa na string de conexao
    - so ajusta o databse.php
    tirar as coisas de banco do valida
    */

    function open_database() {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "pedacinhodeamor";
        $charset = 'utf8';

        // DSN (Data Source Name)
        $dsn = "mysql:host=$servername;dbname=$dbname;charset=$charset";

        // Opções de configuração do PDO
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retorna resultados como array associativo
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa prepared statements nativos
        ];

        try {
            $conn = new PDO($dsn, $username, $password, $options);
            return $conn;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return null;
        }
    }

    function close_database($conn) {
        try {
            $conn = null;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    //Pesquisa um Registro pelo ID em uma Tabela
    function find( $table, $id = null ) {

        $database = open_database(); //abre conexão
        $found = null;

        try {
            if ($id) {                
                $sql = $database->prepare("SELECT * FROM $table WHERE id = ?");
                $sql->execute([$id]);
                $found = $sql->fetch(PDO::FETCH_ASSOC); //fetch é metodo para armazenar dados, retorna vetor associativo
                //$result = $database->query($sql);                
            } else {
                
                $sql = $database->prepare("SELECT * FROM $table");
                $sql->execute();
                //$found = $sql->fetchAll(PDO::FETCH_ASSOC);
                //$result = $database->query($sql);
                
                if ($sql->rowCount() > 0) {
                    //$found = $sql->fetchAll(PDO::FETCH_ASSOC);
                    
                    //Metodo alternativo
                    
                    $found = array();
                    while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
                        array_push($found, $row); //gera um vetor para cada linha que veio do banco
                    } 
                }
            }
        } 
        
        catch (Exception $e) {
            $_SESSION['message'] = $e->GetMessage();
            $_SESSION['type'] = 'danger';
        }
        
        close_database($database);
        return $found;
    }

    //Pesquisa Todos os Registros de uma Tabela

    function find_all( $table ) {
        return find($table);
    }

    //Insere um registro no BD
/*
   function save($table, $data) {
    $database = open_database();

    try {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));

        $sql = $database->prepare("INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})");

        foreach ($data as $key => $value) {
            $sql->bindValue(":$key", $value);
        }

        $sql->execute();

        $_SESSION["message"] = "Registro cadastrado com sucesso.";
        $_SESSION["type"] = "success";

    } catch (Exception $e) {
        $_SESSION["message"] = "Nao foi possivel realizar a operacao.<br>{$e->getMessage()}";
        $_SESSION["type"] = "danger";
    }

    $database = null;
}

    
*/
    function save($table = null, $data = null) {

            $database = open_database();
    /*
            $columns = null;
            $values = null;

            foreach ($data as $key => $value) {
                $columns .= trim($key, "'") . ","; //ta concatenando os valores | trim remove, rtrim so da direita
                $values .= "'$value',";
            }

            // remove a ultima virgula de cada objeto
            $columns = rtrim($columns, ",");
            $values = rtrim($values, ",");

            $sql = "INSERT INTO " . $table . "($columns)" . " VALUES " . "($values);";

            
    */
                // remove aspas simples das chaves
            $cleanData = [];
            foreach ($data as $key => $value) {
                $cleanKey = trim($key, "'");
                $cleanData[$cleanKey] = $value;
            }

            try {
                // monta lista de colunas, fica tipo->  resultado: "nome, email"
                $columns = implode(", ", array_keys($cleanData));

                // monta lista de placeholders (um ? para cada valor) -> resultado: "?, ?", esses ? vao ser substituidos pelos valores do vetor $data, na ordem
                $placeholders = implode(", ", array_fill(0, count($cleanData), "?"));

                // prepara a query, fica com os placeholders -> resultado: "INSERT INTO tabela (nome, email) VALUES (?, ?)"
                $sql = $database->prepare("INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})");

                $sql->execute(array_values($cleanData));

                $_SESSION["message"] = "Registro cadastrado com sucesso.";
                $_SESSION["type"] = "success";
            
            } catch (Exception $e) { 
            
                $_SESSION["message"] = "Nao foi possivel realizar a operacao.\n<br>{$e->getMessage()}"; //para recuperar a mensagem de erro do banco
                $_SESSION["type"] = "danger";
            } 

            close_database($database);
        }


    

    //Atualiza um registro em uma tabela, por ID

    function update($table = null, $id = 0, $data = null) {

        $database = open_database();
/*
        $items = null;

        foreach ($data as $key => $value) {
            $items .= trim($key, "'") . "='$value',";
        }

        // remove a ultima virgula
        $items = rtrim($items, ',');

        $sql = "UPDATE $table SET $items WHERE id=$id;";
*/
          // remove aspas simples das chaves
            $cleanData = [];
            foreach ($data as $key => $value) {
                $cleanKey = trim($key, "'");
                $cleanData[$cleanKey] = $value;
            }

        try {
            // monta lista de "coluna = ?" para cada campo, diferente do save, aqui tem que ser "coluna = ?", e nao so os nomes das colunas
            $set = implode(", ", array_map(function($key) {
                return "{$key} = ?";
            }, array_keys($cleanData)));

            $sql = $database->prepare("UPDATE {$table} SET {$set} WHERE id = ?");

            //arrumando os valores certinho
            $values = array_values($cleanData);
            $values[] = $id; //add id no final

            $sql->execute($values);

            $_SESSION["message"] = "Registro atualizado com sucesso.";
            $_SESSION["type"] = "success";

        } catch (Exception $e) { 

            $_SESSION["message"] = "Nao foi possivel realizar a operacao.";
            $_SESSION["type"] = "danger";
        } 

        close_database($database);
    }

    //Remove uma linha de uma tabela pelo ID do registro
    
    function remove( $table = null, $id = null ) {

        $database = open_database();
            
        try {
            if ($id) {
                $tabela = find($table, $id);
                //$sql = "DELETE FROM $table WHERE id = $id";
                $sql = $database->prepare("DELETE FROM {$table} WHERE id = ?");
                $result = $sql->execute([$id]);

                if ($result) {   	
                    $_SESSION['message'] = "Registro Removido com Sucesso.";
                    $_SESSION['type'] = 'success';

                    // se existir coluna 'foto', remove o arquivo
                    if (!empty($tabela["foto"])) {
                        unlink("fotos/" . $tabela["foto"]);
                    }

                    //unlink("fotos/" . $tabela["foto"]);
                }
            }
        } catch (Exception $e) { 

            $_SESSION['message'] = $e->GetMessage();
            $_SESSION['type'] = 'danger';
        }

        close_database($database);
    }

     //Pesquisa Registros pelo parametro informado
    function filter( $table = null, $p = null ) {

        $database = open_database(); //abre conexão
        $found = null;

        try {
            if ($p) {
                //$sql = "SELECT * FROM $table WHERE $p";
                $sql = $database->prepare("SELECT * FROM {$table} WHERE {$p}");

                $sql->execute();

                $found = $sql->fetchAll(PDO::FETCH_ASSOC);

                if (!$found) {
                    throw new Exception("Não foram encontrados dados");
                }



                /*
                if ($sql->fetch(PDO::FETCH_ASSOC) > 0) {

                    $found = [];
                    while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
                        $found[] = $row; //gera um vetor para cada linha que veio do banco 
                    } 
                } else {
                    throw new Exception("Não foram encontrados dados");
                }*/
            }
                
        }
        
        catch (Exception $e) {
            $_SESSION['message'] = $e->GetMessage();
            $_SESSION['type'] = 'danger';
        }
        
        close_database($database);
        return $found;
    }

    function clear_messages() {
        $_SESSION['message'] = null;
        $_SESSION['type'] = null;
    }

    function validacao ($usuario, $senha ) {
        $database = open_database();

        try {
            if (!empty($usuario) AND !empty($senha)) {
                

                $sql = $database->prepare("SELECT id, nome, email, password FROM usuarios WHERE email = ? LIMIT 1");
                $sql->execute([$usuario]);

                $dados = $sql->fetch(PDO::FETCH_ASSOC);
                
                if ($dados && password_verify($senha, $dados['password'])) {
   
                  
                    if(!isset($_SESSION)) session_start();
                    $_SESSION['message'] = "Bem vindo " . $dados['nome'] . "!";
                    $_SESSION['type'] = "success";
                    $_SESSION['id'] = $dados['id'];
                    $_SESSION['nome'] = $dados['nome'];
                    $_SESSION['email'] = $dados['email'];
                    $_SESSION['logado'] = true;
                    
                    header("Location: ". BASEURL . "index.php");
                    exit;
                } else {
                    throw new Exception("Email ou senha inválidos!");
                }
            } else {
                throw new Exception("Preencha email e senha!");
            }
        } catch (Exception $e) {
            if(!isset($_SESSION)) session_start();
            $_SESSION['message'] = $e->getMessage();
            $_SESSION['type'] = 'danger';
            header("Location: ". BASEURL . "index.php");
            exit;
        }

        close_database($database);

    }
?>