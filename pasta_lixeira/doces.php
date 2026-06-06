<?php
if (!isset($_SESSION)) session_start();
require_once '../config.php';
require_once ABSPATH . 'inc/database.php';
require_once DBAPI; 
include(HEADER_TEMPLATE);

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

<body>
    <main>
        <section class="doces-intro" style="background: url('<?php echo BASEURL; ?>/imagens/brigadeiros.jpg') no-repeat center center; background-size: cover;">
            <!-- bloco de bem vindo do site -->
            <div class="doces-fundo"></div>


            <div class="conteudodoces">
                <div class="doces-header">
                    <h1 class="doces-titulo">doces</h1>
                    <!-- linha bonitinha -->
                    <div class="doce-detalhe"></div>
                </div>
                <p class="doces-subtitulo">
                    Para adoçar e trazer aconchego a cada mordida!
                </p>
            </div>
        </section>

        <section class="container">
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
                                    <img src="<?php echo BASEURL . htmlspecialchars($produto['imagem_referencia']); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
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
        </section>
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