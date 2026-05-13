<?php 
    include ("../config.php");
    require_once(DBAPI);
    
    if (!isset($_SESSION)) session_start();
    header('Content-Type: application/json; charset=utf-8');

    //verifica se houve post e se o usuario ou a senha sao vazios
    $usuario = trim($_POST['login'] ?? $_POST['email'] ?? '');
    if(!empty($_POST) && (empty($usuario) || empty($_POST['senha']))){
        echo json_encode([
            'success' => false,
            'message' => 'Preencha todos os campos.'
        ]);
        exit;
    }

    //tenta se conectar a um banco de dados
    try {
        //pegando o login e senha do form
        $usuario = trim($_POST['login'] ?? $_POST['email'] ?? '');
        $senha = $_POST['senha'];

        validacao($usuario, $senha);
        
        // Se chegou aqui, login foi bem-sucedido
        echo json_encode([
            'success' => true,
            'message' => 'Login realizado com sucesso!'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
?>