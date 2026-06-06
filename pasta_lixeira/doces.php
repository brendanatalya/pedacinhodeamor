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

$tipo = $_GET['tipo'] ?? 'doce'; // Filtro padrão para doces
$produtos = find_products($tipo === 'all' ? null : $tipo);
$cartMessage = $_SESSION['cart_message'] ?? '';
unset($_SESSION['cart_message']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doces - Pedacinho de Amor</title>
    <link rel="stylesheet" href="../css_pda/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css_pda/style_pda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <h1>Pedacinho de Amor</h1>
        <nav>
            <ul>
                <li><a href="../index.php">Home</a></li>
                <li><a href="../paginas/sobrenos.html">Sobre Nós</a></li>
                <li><a href="doces.php" class="active">Doces</a></li>
                <li><a href="salgados.php">Salgados</a></li>
                <li><a href="personalizados.php">Personalizados</a></li>
                <li><a href="carrinho.php"><i class="fas fa-shopping-cart"></i> Carrinho (<?php echo array_sum($_SESSION['cart'] ?? []); ?>)</a></li>
                <?php if ($usuario_logado): ?>
                    <li><a href="../inc/logout.php">Sair</a></li>
                <?php else: ?>
                    <li><a class="btn btn-primary text-white" href="../index.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section class="doces-hero" style="background-image:url('imagens/doce3.webp');">
            <div class="doces-hero__overlay"></div>
            <div class="doces-hero__content">
                <h1>DOCES</h1>
                <p>Deliciosamente feitos com carinho!</p>
            </div>
        </section>

        <div class="container">
            <?php if ($cartMessage): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($cartMessage); ?></div>
            <?php endif; ?>

            <?php if (!$usuario_logado): ?>
                <div class="alert alert-warning">Faça login para adicionar produtos ao carrinho.</div>
            <?php endif; ?>

            <div class="filter-buttons mb-4">
                <?php foreach ($tipos as $key => $label): ?>
                    <a href="doces.php?tipo=<?php echo $key === 'all' ? '' : urlencode($key); ?>" class="filter-btn <?php echo ($tipo === $key || ($key === 'all' && $tipo === '')) ? 'active' : ''; ?>">
                        <?php echo $label; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if (empty($produtos)): ?>
                <div class="alert alert-info">Nenhum produto encontrado para esta categoria.</div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="products-container">
                    <?php foreach ($produtos as $produto): ?>
                        <div class="col">
                            <div class="product-card <?php echo !$produto['disponivel'] ? 'unavailable' : ''; ?>">
                                <?php if (!empty($produto['imagem_referencia'])): ?>
                                    <img src="<?php echo htmlspecialchars($produto['imagem_referencia']); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                                <?php endif; ?>
                                <div class="product-info">
                                    <h3><?php echo htmlspecialchars($produto['nome']); ?></h3>
                                    <p><?php echo htmlspecialchars($produto['descricao'] ?? 'Delicioso produto artesanal'); ?></p>
                                    <div class="product-price">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></div>
                                    <?php if (!$produto['disponivel']): ?>
                                        <div class="unavailable-badge">Indisponível</div>
                                    <?php endif; ?>
                                    <form action="add_carrinho.php" method="POST">
                                        <input type="hidden" name="product_id" value="<?php echo $produto['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES); ?>">
                                        <button type="submit" class="add-to-cart-btn" <?php echo (!$usuario_logado || !$produto['disponivel']) ? 'disabled' : ''; ?>>
                                            <i class="fas fa-shopping-cart"></i> Adicionar ao Carrinho
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

     <main>
        <section class="doces-hero" style="background-image:url('imagens/doce3.webp');">
            <div class="doces-hero__overlay"></div>
            <div class="doces-hero__content">
                <h1>SALGADOS</h1>
                <p>Deliciosamente feitos com carinho!</p>
            </div>
        </section>

        <div class="container">
            <?php if ($cartMessage): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($cartMessage); ?></div>
            <?php endif; ?>

            <?php if (!$usuario_logado): ?>
                <div class="alert alert-warning">Faça login para adicionar produtos ao carrinho.</div>
            <?php endif; ?>

            <div class="filter-buttons mb-4">
                <?php foreach ($tipos as $key => $label): ?>
                    <a href="salgados.php?tipo=<?php echo $key === 'all' ? '' : urlencode($key); ?>" class="filter-btn <?php echo ($tipo === $key || ($key === 'all' && $tipo === '')) ? 'active' : ''; ?>">
                        <?php echo $label; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if (empty($produtos)): ?>
                <div class="alert alert-info">Nenhum produto encontrado para esta categoria.</div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="products-container">
                    <?php foreach ($produtos as $produto): ?>
                        <div class="col">
                            <div class="product-card <?php echo !$produto['disponivel'] ? 'unavailable' : ''; ?>">
                                <?php if (!empty($produto['imagem_referencia'])): ?>
                                    <img src="<?php echo htmlspecialchars($produto['imagem_referencia']); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                                <?php endif; ?>
                                <div class="product-info">
                                    <h3><?php echo htmlspecialchars($produto['nome']); ?></h3>
                                    <p><?php echo htmlspecialchars($produto['descricao'] ?? 'Delicioso produto artesanal'); ?></p>
                                    <div class="product-price">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></div>
                                    <?php if (!$produto['disponivel']): ?>
                                        <div class="unavailable-badge">Indisponível</div>
                                    <?php endif; ?>
                                    <form action="add_carrinho.php" method="POST">
                                        <input type="hidden" name="product_id" value="<?php echo $produto['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES); ?>">
                                        <button type="submit" class="add-to-cart-btn" <?php echo (!$usuario_logado || !$produto['disponivel']) ? 'disabled' : ''; ?>>
                                            <i class="fas fa-shopping-cart"></i> Adicionar ao Carrinho
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="js/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>