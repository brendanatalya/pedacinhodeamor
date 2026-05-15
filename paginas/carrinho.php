<?php
if (!isset($_SESSION)) session_start();
require_once '../config.php';
require_once ABSPATH . 'inc/database.php';

$usuario_logado = !empty($_SESSION['logado']) && $_SESSION['logado'] === true;
$cart = $_SESSION['cart'] ?? [];
$availableItems = [];
$unavailableItems = [];
$total = 0;
$frete = 12.28;

foreach ($cart as $product_id => $qty) {
    $produto = find_product(intval($product_id));
    if (!$produto) {
        continue;
    }

    $produto['quantity'] = max(1, intval($qty));
    $produto['subtotal'] = $produto['quantity'] * floatval($produto['preco']);

    if ($produto['disponivel']) {
        $availableItems[] = $produto;
        $total += $produto['subtotal'];
    } else {
        $unavailableItems[] = $produto;
    }
}

$cartMessage = $_SESSION['cart_message'] ?? '';
unset($_SESSION['cart_message']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho - Pedacinho de Amor</title>
    <link rel="stylesheet" href="css_pda/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="css_pda/style_pda.css">
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
                <li><a href="salgados.php">Salgados</a></li>
                <li><a href="personalizados.php">Personalizados</a></li>
                <li><a href="carrinho.php" class="active"><i class="fas fa-shopping-cart"></i> Carrinho (<?php echo array_sum($cart); ?>)</a></li>
                <?php if ($usuario_logado): ?>
                    <li><a href="inc/logout.php">Sair</a></li>
                <?php else: ?>
                    <li><a class="btn btn-primary text-white" href="index.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2 class="my-4">Seu Carrinho</h2>

        <?php if ($cartMessage): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($cartMessage); ?></div>
        <?php endif; ?>

        <?php if (!$usuario_logado): ?>
            <div class="alert alert-warning">Faça login para finalizar seu pedido.</div>
        <?php endif; ?>

        <?php if (empty($availableItems) && empty($unavailableItems)): ?>
            <div class="alert alert-secondary">Seu carrinho está vazio. <a href="doces.php">Continuar comprando</a></div>
        <?php else: ?>
            <div class="cart">
                <div class="cart-items">
                    <h3>Itens Disponíveis</h3>
                    <?php if (empty($availableItems)): ?>
                        <p>Nenhum item disponível no carrinho.</p>
                    <?php else: ?>
                        <?php foreach ($availableItems as $produto): ?>
                            <div class="cart-item">
                                <?php if (!empty($produto['imagem_referencia'])): ?>
                                    <img src="<?php echo htmlspecialchars($produto['imagem_referencia']); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                                <?php endif; ?>
                                <div class="cart-item-info">
                                    <h4><?php echo htmlspecialchars($produto['nome']); ?></h4>
                                    <p><?php echo htmlspecialchars($produto['descricao'] ?? 'Produto artesanal'); ?></p>
                                    <p>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                                    <div class="quantity-controls">
                                        <form action="update_carrinho.php" method="POST" class="d-inline">
                                            <input type="hidden" name="product_id" value="<?php echo $produto['id']; ?>">
                                            <input type="hidden" name="quantity" value="<?php echo $produto['quantity'] - 1; ?>">
                                            <button type="submit" name="action" value="update" class="quantity-btn">-</button>
                                        </form>
                                        <input type="number" class="quantity-input" value="<?php echo $produto['quantity']; ?>" readonly>
                                        <form action="update_carrinho.php" method="POST" class="d-inline">
                                            <input type="hidden" name="product_id" value="<?php echo $produto['id']; ?>">
                                            <input type="hidden" name="quantity" value="<?php echo $produto['quantity'] + 1; ?>">
                                            <button type="submit" name="action" value="update" class="quantity-btn">+</button>
                                        </form>
                                    </div>
                                    <p>Subtotal: R$ <?php echo number_format($produto['subtotal'], 2, ',', '.'); ?></p>
                                    <form action="update_carrinho.php" method="POST" class="d-inline">
                                        <input type="hidden" name="product_id" value="<?php echo $produto['id']; ?>">
                                        <button type="submit" name="action" value="remove" class="remove-btn">Remover</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($unavailableItems)): ?>
                        <div class="unavailable-section">
                            <h3>Itens Indisponíveis</h3>
                            <form action="update_carrinho.php" method="POST">
                                <button type="submit" name="action" value="remove_unavailable" class="clear-unavailable-btn">Excluir todos os itens indisponíveis</button>
                            </form>
                            <?php foreach ($unavailableItems as $produto): ?>
                                <div class="cart-item" style="opacity: 0.6;">
                                    <?php if (!empty($produto['imagem_referencia'])): ?>
                                        <img src="<?php echo htmlspecialchars($produto['imagem_referencia']); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                                    <?php endif; ?>
                                    <div class="cart-item-info">
                                        <h4><?php echo htmlspecialchars($produto['nome']); ?></h4>
                                        <p><?php echo htmlspecialchars($produto['descricao'] ?? 'Produto artesanal'); ?></p>
                                        <div class="unavailable-badge">Indisponível</div>
                                        <p>Quantidade reservada: <?php echo $produto['quantity']; ?></p>
                                        <form action="update_carrinho.php" method="POST" class="d-inline">
                                            <input type="hidden" name="product_id" value="<?php echo $produto['id']; ?>">
                                            <button type="submit" name="action" value="remove" class="remove-btn">Remover</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="cart-summary">
                    <h4>Resumo da Compra</h4>
                    <div class="summary-row">
                        <span>Total de itens</span>
                        <span><?php echo array_sum($cart); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>R$ <?php echo number_format($total, 2, ',', '.'); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Frete</span>
                        <span>R$ <?php echo $total > 0 ? number_format($frete, 2, ',', '.') : '0,00'; ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>R$ <?php echo number_format($total > 0 ? $total + $frete : 0, 2, ',', '.'); ?></span>
                    </div>
                    <button class="checkout-btn" onclick="checkout()" <?php echo (!$usuario_logado || $total <= 0) ? 'disabled' : ''; ?>>Finalizar Compra</button>
                    <?php if (!$usuario_logado): ?>
                        <p class="text-muted small mt-2">Faça login para poder finalizar o pedido.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function checkout() {
            // Redirecionar para a página de checkout
            window.location.href = 'checkout_form.php';
        }
    </script>

    <script src="js/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>