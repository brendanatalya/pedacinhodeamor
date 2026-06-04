<!DOCTYPE html>
<html lang="pt-br">
<head>

<meta charset="utf-8">

	<link rel="icon" type="image/x-icon" href="imagens/icon.png">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Brenda e Iris">
    <title>Pedacinho de Amor</title>

    <link rel="stylesheet" href="css/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="css/awesome/all.min.css">
    <link rel="stylesheet" href="css/style_pda.css">
</head>
<body>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedacinho de Amor</title>

    <!-- bootstrap -->
    <link rel="stylesheet" href="css/bootstrap/bootstrap.min.css">

    <!-- font awesome -->
    <link rel="stylesheet" href="css/awesome/all.min.css">

    <!-- css -->
    <link rel="stylesheet" href="css/style_pda.css">
</head>

<body>

<header id="home">

    <!-- NAVBAR -->
    <nav id="nav">

        <div class="nav-center">

            <!-- LOGO -->
            <div class="nav-header">

                <a href="index.php">
                    <img src="imagens/logo.png" class="logo" alt="">
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
                        <a href="index.php">Início</a>
                    </li>

                    <li>
                        <a href="sobrenos.php">Sobre nós</a>
                    </li>

                    <li>
                        <a href="doces.php">Doces</a>
                    </li>

                    <li>
                        <a href="salgados.php">Salgados</a>
                    </li>

                    <li>
                        <a href="personalizados.php">Personalizados</a>
                    </li>

                </ul>

                <!-- ICONES -->
                <div class="nav-icons">

                    <?php //if(!empty($_SESSION['logado']) && $_SESSION['logado'] === true): ?>

                    <?php //else: ?>
                        <a href="#">
                            <i class="fa-regular fa-circle-user"></i>
                            <p>Olá, <?php //echo htmlspecialchars($_SESSION['nome']); ?></p>
                        </a>
                    <?php //endif; ?>
                    

                    <a href="#">
                        <i class="fa-solid fa-basket-shopping"></i>
                        <p>Carrinho (<?php echo array_sum($_SESSION['cart'] ?? []); ?>)</p>
                    </a>

                </div>

            </div>

        </div>

    </nav>

</header>
