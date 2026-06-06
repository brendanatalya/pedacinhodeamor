<?php
if (!isset($_SESSION)) session_start();
require_once '../config.php';
require_once ABSPATH . 'inc/database.php';

$usuario_logado = !empty($_SESSION['logado']) && $_SESSION['logado'] === true;
$tipos = [
    'all' => 'Todos',
    'doce' => 'Doces',
    'salgado' => 'Salgados',
    'bolo' => 'Bolos',
    'personalizado' => 'Personalizados',
];

$tipo = $_GET['tipo'] ?? 'salgado'; // Filtro padrão para salgados
$produtos = find_products($tipo === 'all' ? null : $tipo);
$cartMessage = $_SESSION['cart_message'] ?? '';
unset($_SESSION['cart_message']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salgados - Pedacinho de Amor</title>
    <link rel="stylesheet" href="../css_pda/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css_pda/style_pda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <h1>Pedacinho de Amor</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="sobrenos.html">Sobre Nós</a></li>
                <li><a href="doces.php">Doces</a></li>
                <li><a href="salgados.php" class="active">Salgados</a></li>
                <li><a href="personalizados.php">Personalizados</a></li>
                <li><a href="carrinho.php"><i class="fas fa-shopping-cart"></i> Carrinho (<?php echo array_sum($_SESSION['cart'] ?? []); ?>)</a></li>
                <?php if ($usuario_logado): ?>
                    <li><a href="inc/logout.php">Sair</a></li>
                <?php else: ?>
                    <li><a class="btn btn-primary text-white" href="index.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

   

    <script src="js/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>