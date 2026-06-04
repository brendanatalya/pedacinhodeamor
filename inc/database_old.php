<?php

    mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR);

    function open_database() {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            $conn->set_charset("utf8"); //faz com que nao de erro de caracteres
            return $conn;
        } catch (Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    function close_database($conn) {
        try {
            mysqli_close($conn);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    //Pesquisa um Registro pelo ID em uma Tabela
    function find( $table = null, $id = null ) {

        $database = open_database(); //abre conexão
        $found = null;

        try {
            if ($id) {
                $sql = "SELECT * FROM " . $table . " WHERE id = " . $id;
                $result = $database->query($sql);
                
                if ($result->num_rows > 0) {
                    $found = $result->fetch_assoc(); //fetch é metodo para armazenar dados, retorna vetor associativo
                }
                
            } else {
                
                $sql = "SELECT * FROM " . $table;
                $result = $database->query($sql);
                
                if ($result->num_rows > 0) {
                    //$found = $result->fetch_all(MYSQLI_ASSOC);
                    
                    /* Metodo alternativo*/ 
                    
                    $found = array();
                    while ($row = $result->fetch_assoc()) {
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

    function save($table = null, $data = null) {

        $database = open_database();

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
        
        try {
            $database->query($sql); //'->' chama metodo, aqui ta mandando executar a instrução

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

        $items = null;

        foreach ($data as $key => $value) {
            $items .= trim($key, "'") . "='$value',";
        }

        // remove a ultima virgula
        $items = rtrim($items, ',');

        $sql = "UPDATE $table SET $items WHERE id=$id;";

        try {
            $database->query($sql);

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
                $sql = "DELETE FROM $table WHERE id = $id";
                
                if ($result = $database->query($sql)) {   	
                    $_SESSION['message'] = "Registro Removido com Sucesso.";
                    $_SESSION['type'] = 'success';
                    unlink("fotos/" . $tabela["foto"]);
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
                $sql = "SELECT * FROM $table WHERE $p";
                $result = $database->query($sql);
                
                if ($result->num_rows > 0) {

                    $found = [];
                    while ($row = $result->fetch_assoc()) {
                        $found[] = $row; //gera um vetor para cada linha que veio do banco 
                    } 
                } else {
                    throw new Exception("Não foram encontrados dados");
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

    function clear_messages() {
        $_SESSION['message'] = null;
        $_SESSION['type'] = null;
    }
?>