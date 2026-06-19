<?php
if (!isset($_SESSION)) session_start();
require_once '../config.php';
require_once ABSPATH . 'inc/database.php';
require_once DBAPI; 
include(HEADER_TEMPLATE);

$usuario_logado = !empty($_SESSION['logado']) && $_SESSION['logado'] === true;
$cart = $_SESSION['cart'] ?? [];
$cart_personalizado = $_SESSION['cart_personalizado'] ?? [];

$availableItems = [];
$unavailableItems = [];
$total = 0;

// Processa os produtos normais
foreach ($cart as $product_id => $qty) {
    $produto = find_product(intval($product_id));
    if (!$produto) continue;

    $produto['quantity'] = max(1, intval($qty));
    $produto['subtotal'] = $produto['quantity'] * floatval($produto['preco']);

    if ($produto['disponivel']) {
        $availableItems[] = $produto;
        $total += $produto['subtotal'];
    } else {
        $unavailableItems[] = $produto;
    }
}

// Cálculo exato de quantidades para os contadores
$qtd_normais = array_sum($cart);
$qtd_personalizados = 0;
foreach ($cart_personalizado as $item) {
    $qtd_personalizados += intval($item['quantity'] ?? 1);
}
$total_itens_carrinho = $qtd_normais + $qtd_personalizados;

// Verifica se o cart inteiro está totalmente vazio (Normais + Personalizados)
$carrinho_vazio = empty($availableItems) && empty($unavailableItems) && empty($cart_personalizado);

$cartMessage = $_SESSION['cart_message'] ?? '';
unset($_SESSION['cart_message']);
?>
<body>

    <div class="container">
        <h2 class="mt-4 titulocarrinho">Seu Carrinho</h2>

        <?php if ($cartMessage): ?>
            <div class="alert alert-sucesso"><?php echo htmlspecialchars($cartMessage); ?></div>
        <?php endif; ?>

        <?php if (!$usuario_logado): ?>
            <div class="alert alert-aviso">Faça login para finalizar seu pedido.</div>
        <?php endif; ?>

        <?php if ($carrinho_vazio): ?>
            <div class="alert alert-rosa">Seu carrinho está vazio. <a href="cardapio.php">Continuar comprando</a></div>
        <?php else: ?>
            <div class="carrinho">
                <div class="carrinho-itens">

                    <?php if (!empty($availableItems)): ?>
                        <h3>Itens Disponíveis</h3>
                        <?php foreach ($availableItems as $produto): ?>
                            <div class="carrinho-item">
                                <?php if (!empty($produto['imagem_referencia'])): ?>
                                    <img src="../imagens/<?php echo htmlspecialchars($produto['imagem_referencia']); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                                <?php endif; ?>
                                <div class="carrinho-item-info">
                                    <h4><?php echo htmlspecialchars($produto['nome']); ?></h4>
                                    <p><?php echo htmlspecialchars($produto['descricao'] ?? 'Produto artesanal'); ?></p>
                                    <p>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                                    <div class="carrinho-item-qtd">
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
                                        <button type="submit" name="action" value="remove" class="remove-btn"> Remover</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($cart_personalizado)): ?>
                        <div class="personalizados-section mt-4">
                            <h3>Produtos Personalizados (Sob Encomenda)</h3>
                            <?php foreach ($cart_personalizado as $i => $item): ?>
                                <div class="carrinho-item">
                                    <?php if (!empty($item['imagem_path'])): ?>
                                        <img src="<?php echo '../' . htmlspecialchars($item['imagem_path']); ?>"
                                             alt="Referência" style="width:80px;height:80px;object-fit:cover;border-radius:8px;">
                                    <?php endif; ?>
                                    <div class="carrinho-item-info">
                                        <h4><?php echo ucfirst($item['tipo']); ?> — <?php echo $item['tema']; ?></h4>
                                        <p><strong>Sabor:</strong> <?php echo $item['sabor']; ?></p>
                                        <?php if (!empty($item['tamanho'])): ?>
                                            <p><strong>Tamanho:</strong> <?php echo $item['tamanho']; ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($item['cor'])): ?>
                                            <p><strong>Cor:</strong> <?php echo $item['cor']; ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($item['restricoes'])): ?>
                                            <p><strong>Restrições:</strong> <?php echo implode(', ', $item['restricoes']); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($item['data_desejada'])): ?>
                                            <p><strong>Data desejada:</strong> <?php echo date('d/m/Y', strtotime($item['data_desejada'])); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($item['detalhes'])): ?>
                                            <p><strong>Detalhes:</strong> <?php echo $item['detalhes']; ?></p>
                                        <?php endif; ?>
                                        <p><strong>Quantidade:</strong> <?php echo $item['quantity']; ?></p>
                                        <p class="text-success"><em>Preço sob orçamento</em></p>
                                        <form action="update_carrinho.php" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="remove_personalizado">
                                            <input type="hidden" name="index" value="<?php echo $i; ?>">
                                            <button type="submit" class="remove-btn">Remover</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($unavailableItems)): ?>
                        <div class="unavailable-section mt-4">
                            <h3>Itens Indisponíveis</h3>
                            <form action="update_carrinho.php" method="POST">
                                <button type="submit" name="action" value="remove_unavailable" class="clear-unavailable-btn">Excluir todos os itens indisponíveis</button>
                            </form>
                            <?php foreach ($unavailableItems as $produto): ?>
                                <div class="carrinho-item" style="opacity: 0.6;">
                                    <?php if (!empty($produto['imagem_referencia'])): ?>
                                        <img src="<?php echo '../' . htmlspecialchars($produto['imagem_referencia']); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                                    <?php endif; ?>
                                    <div class="carrinho-item-info">
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

                <div class="carrinho-summary">
                    <h4>Resumo da Compra</h4>
                    <div class="summary-row">
                        <span>Total de itens</span>
                        <span><?php echo $total_itens_carrinho; ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Subtotal itens padrão</span>
                        <span>R$ <?php echo number_format($total, 2, ',', '.'); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total Atual</span>
                        <span>R$ <?php echo number_format($total, 2, ',', '.'); ?></span>
                    </div>
                    <div class="summary-row">
                        <small class="text-muted"><i class="fa-solid fa-store"></i> Somente retirada na loja</small>
                    </div>

                    <button class="checkout-btn" onclick="checkout()" <?php echo (!$usuario_logado || $total_itens_carrinho <= 0) ? 'disabled' : ''; ?>>Finalizar Compra</button>

                    <?php if (!$usuario_logado): ?>
                        <p class="text-muted small mt-2">Faça login para poder finalizar o pedido.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function checkout() {
            window.location.href = 'checkout_form.php';
        }
    </script>
    <?php include '../inc/modal.php'; 
        include(FOOTER_TEMPLATE);?>
</body>
</html>