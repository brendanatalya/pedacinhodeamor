<?php
if (!isset($_SESSION)) session_start();
require_once 'config.php';
require_once ABSPATH . 'inc/database.php';

$tipos = [
    'salgado' => 'Salgados',
    'doce' => 'Doces',
    'bolo' => 'Bolos',
    'personalizado' => 'Personalizados',
];

$tipo = $_GET['tipo'] ?? '';
if ($tipo && !array_key_exists($tipo, $tipos)) {
    $tipo = '';
}

$produtos = find_products($tipo ?: null);
$usuario_logado = !empty($_SESSION['logado']) && $_SESSION['logado'] === true;
$cartMessage = $_SESSION['cart_message'] ?? '';
unset($_SESSION['cart_message']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - Pedacinho de Amor</title>
    <link rel="stylesheet" href="css_pda/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="css_pda/style_pda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<header>
    <h1>Produtos</h1>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="sobrenos.html">Sobre Nós</a></li>
            <li><a href="produtos.php?tipo=doce">Doces</a></li>
            <li><a href="produtos.php?tipo=salgado">Salgados</a></li>
            <li><a href="produtos.php?tipo=bolo">Bolos</a></li>
            <li><a href="produtos.php?tipo=personalizado">Personalizados</a></li>
            <li><a href="cesta.php">Cesta</a></li>
            <?php if ($usuario_logado): ?>
                <?php if (!empty($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin'): ?>
                    <li><a href="admin.php">Admin</a></li>
                <?php endif; ?>
                <li><a href="inc/logout.php">Sair</a></li>
            <?php else: ?>
                <li><a class="btn btn-primary text-white" href="index.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<div class="container mt-4">
    <?php if ($cartMessage): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($cartMessage); ?></div>
    <?php endif; ?>

    <?php if (!$usuario_logado): ?>
        <div class="alert alert-warning">Faça login para adicionar produtos ao carrinho.</div>
    <?php endif; ?>

    <div class="btn-group mb-4" role="group" aria-label="Categorias">
        <a href="produtos.php" class="btn btn-outline-secondary<?php echo $tipo === '' ? ' active' : ''; ?>">Todos</a>
        <?php foreach ($tipos as $key => $label): ?>
            <a href="produtos.php?tipo=<?php echo urlencode($key); ?>" class="btn btn-outline-secondary<?php echo $tipo === $key ? ' active' : ''; ?>"><?php echo $label; ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($produtos)): ?>
        <div class="alert alert-info">Nenhum produto encontrado para esta categoria.</div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($produtos as $produto): ?>
                <div class="col">
                    <div class="card h-100">
                        <?php if (!empty($produto['imagem_referencia'])): ?>
                            <img src="<?php echo htmlspecialchars($produto['imagem_referencia']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($produto['descricao'])); ?></p>
                            <p class="card-text"><strong>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></strong></p>
                            <form action="add_carrinho.php" method="POST" class="mt-auto">
                                <input type="hidden" name="product_id" value="<?php echo $produto['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES); ?>">
                                <div class="input-group mb-3">
                                    <button type="submit" class="btn btn-success" <?php echo $usuario_logado ? '' : 'disabled'; ?>>Adicionar ao carrinho</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="js/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
