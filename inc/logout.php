<?php 
    include ("../config.php");
    try{
        session_start(); //inicia a sessao ou acessa a sessao existente
        session_destroy(); //destroi a sessao limpando todos os valores salvos
        header("Location: ". BASEURL . "index.php"); //redireciona o usuario para a pagina de login
    } catch (Exception $e) {
        $_SESSION['message'] = "Ocorreu um erro: " . $e->getMessage();
        $_SESSION['type'] = 'danger';
    }

?>