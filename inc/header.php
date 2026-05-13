<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gem Company</title>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="author" content="Brenda e Maria">
	<link rel="icon" type="image/x-icon" href="imagens/icon.png">
    <link rel="icon" type="image/x-icon" href="../imagens/icon.png">

    <link rel="stylesheet" href="<?php echo BASEURL; ?>css/awesome/all.min.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css/bootstrap/bootstrap.min.css">        
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css/style.css">

</head>
<body>
  
    <nav class="navbar navbar-expand-md bg-dark-subtle fixed-top" data-bs-theme="dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASEURL; ?>"><i class="fa-solid fa-gem"></i> Gem Company</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbar">
                <ul class="navbar-nav mb-2 mb-lg-0 nav-underline">
                    <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-users"></i> CLIENTES
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo BASEURL; ?>customers"> <i class="fa-solid fa-users"></i> Gerenciar Clientes</a></li>
                        <?php
                            session_start();
                            if (isset($_SESSION["user"])) : //verifica se existe usuario logado?>
                            <li><a class="dropdown-item" href="<?php echo BASEURL; ?>customers/add.php"> <i class="fa-solid fa-user-plus"></i> Novo Cliente</a></li>
                        <?php endif;?>
                    </ul>
                    </li>
                </ul>
                <ul class="navbar-nav mb-2 mb-lg-0 nav-underline">
                    <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-user-doctor"></i> MÉDICOS
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo BASEURL; ?>medicos"> <i class="fa-solid fa-user-doctor"></i> Gerenciar Médicos</a></li>
                        <?php if (isset($_SESSION["user"])) : //verifica se existe usuario logado?>
                            <li><a class="dropdown-item" href="<?php echo BASEURL; ?>medicos/add.php"> <i class="fa-solid fa-heart-circle-plus"></i> Novo Médico</a></li>
                        <?php endif;?>
                    </ul>
                    </li>
                </ul>
                <ul class="navbar-nav mb-2 mb-lg-0 nav-underline">
                    <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-newspaper"></i> REVISTAS
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo BASEURL; ?>revistas"> <i class="fa-solid fa-newspaper"></i> Gerenciar Revistas</a></li>
                        <?php if (isset($_SESSION["user"])) : //verifica se existe usuario logado?>
                            <li><a class="dropdown-item" href="<?php echo BASEURL; ?>revistas/add.php"> <i class="fa-solid fa-file-circle-plus"></i> Nova Revista</a></li>
                        <?php endif;?>
                    </ul>
                    </li>
                </ul>
                <?php                     
                    if (isset($_SESSION["user"])) : //verifica se esta logado ?>
                    <?php if ($_SESSION["user"] == "admin") : //verifica se esta logado como admin ?>
                        <ul class="navbar-nav mb-2 mb-lg-0 nav-underline">
                            <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-users-gear"></i> USUARIOS
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo BASEURL; ?>usuarios"> <i class="fa-solid fa-users-gear"></i> Gerenciar Usuarios</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASEURL; ?>usuarios/add.php"> <i class="fa-solid fa-user-gear"></i> Novo Usuario</a></li>
                            </ul>
                            </li>
                        </ul>
                    <?php endif; ?>
                    <div class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASEURL;?>inc/logout.php">
                                Bem vindo <?php echo $_SESSION["user"]?>! <i class="fa-solid fa-person-walking-arrow-right"></i> Desconectar
                            </a>
                        </li>
                    </div>
                <?php else :?>

                    <div class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASEURL;?>inc/login.php">
                                <i class="fa-solid fa-user"></i> Login
                            </a>
                        </li>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="container mt-5 pt-2" >