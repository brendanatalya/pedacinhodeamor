<!DOCTYPE html>
<html lang="pt-br">
<head>

<meta charset="utf-8">

	<link rel="icon" type="image/x-icon" href="<?php echo BASEURL;?>imagens/icon.png">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Brenda e Iris">
    <title>Pedacinho de Amor</title>


    <!-- bootstrap -->
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/bootstrap/bootstrap.min.css">

    <!-- font awesome -->
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/awesome/all.min.css">

          <!-- css -->
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/style_pda.css">


  
</head>

<header id="home">
    <?php  if(!isset($_SESSION)) session_start();  ?>

    <!-- NAVBAR -->
    <nav id="nav">

        <div class="nav-center">

            <!-- LOGO -->
            <div class="nav-header">

                <a href="<?php echo BASEURL; ?>index.php">
                    <img src="<?php echo BASEURL; ?>imagens/logo.png" class="logo" alt="">
                </a>

                <!-- BOTÃO MOBILE -->
                <button class="nav-toggle">
                    <i class="fas fa-bars"></i>
                </button>

            </div>

            <!-- LINKS -->
            <div class="links-container">

                <ul class="links">

                    <li>
                        <a href="<?php echo BASEURL; ?>index.php">Início</a>
                    </li>

                    <li>
                        <a href="<?php echo BASEURL; ?>paginas/sobrenos.php">Sobre nós</a>
                    </li>

                    <li>
                        <a href="<?php echo BASEURL; ?>paginas/cardapio.php">Cardápio</a>
                    </li>


                    <li>
                        <a>Personalizados</a>
                    </li>
                    
                </ul>

                <!-- ICONES -->
                <div class="nav-icons">

                    <?php if(!empty($_SESSION['logado']) && $_SESSION['logado'] === true): ?>
                        <a href="<?php echo BASEURL; ?>minha_conta.php">
                            <i class="fa-regular fa-circle-user"></i>
                            <p>Olá, <?php echo htmlspecialchars($_SESSION['nome']); ?></p>
                        </a>

                    <?php else: ?>
                        <a data-bs-toggle="modal" data-bs-target="#modalLogin">
                            <i class="fa-regular fa-circle-user"></i>
                            <p>Login</p>
                        </a>
                    <?php endif; ?>
                    

                    <a>
                        <i class="fas fa-shopping-cart"></i>
                        <p>Carrinho (<?php echo array_sum($_SESSION['cart'] ?? []); ?>)</p>
                    </a>

                </div>

            </div>

        </div>

    </nav>

</header>
