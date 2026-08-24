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

// ──────────────────────────────────────────────────────────────
// PROCESSAR PRODUTOS NORMAIS
// ──────────────────────────────────────────────────────────────
foreach ($cart as $product_id => $qty) {
    $produto = find_product(intval($product_id));
    if (!$produto) {
        continue;
    }

    $product_qty = max(1, intval($qty));
    $preco = floatval($produto['preco'] ?? 0);
    $subtotal = $product_qty * $preco;

    $produto['quantity'] = $product_qty;
    $produto['subtotal'] = $subtotal;
    $produto['id'] = intval($product_id);

    if (!empty($produto['disponivel'])) {
        $availableItems[] = $produto;
        $total += $subtotal;
    } else {
        $unavailableItems[] = $produto;
    }
}

// ──────────────────────────────────────────────────────────────
// CALCULAR QUANTIDADES
// ──────────────────────────────────────────────────────────────
$qtd_normais = !empty($cart) ? array_sum($cart) : 0;
$qtd_personalizados = 0;

foreach ($cart_personalizado as $item) {
    $qtd_personalizados += max(1, intval($item['quantity'] ?? 1));
}

$total_itens_carrinho = $qtd_normais + $qtd_personalizados;
$carrinho_vazio = empty($availableItems) && empty($unavailableItems) && empty($cart_personalizado);

$cartMessage = $_SESSION['cart_message'] ?? '';
if (!empty($cartMessage)) {
    unset($_SESSION['cart_message']);
}
?>

<body>
    <div class="container">
        <h2 class="mt-4 titulocarrinho">Seu Carrinho</h2>

        <?php if (!empty($cartMessage)): ?>
            <div class="alert alert-sucesso" role="alert">
                <?php echo htmlspecialchars($cartMessage); ?>
            </div>
        <?php endif; ?>

        <?php if (!$usuario_logado): ?>
            <div class="alert alert-aviso" role="alert">
                <strong>Atenção:</strong> Faça login para finalizar seu pedido.
            </div>
        <?php endif; ?>

        <?php if ($carrinho_vazio): ?>
            <div class="alert alert-rosa" role="alert">
                Seu carrinho está vazio. 
                <a href="cardapio.php" class="alert-link">Continuar comprando</a>
            </div>

        <?php else: ?>
            <div class="carrinho">
                <div class="carrinho-itens">

                    <!-- PRODUTOS DISPONÍVEIS -->
                    <?php if (!empty($availableItems)): ?>
                        <section class="disponivel-section">
                            <h3>Itens Disponíveis</h3>
                            
                            <?php foreach ($availableItems as $produto): ?>
                                <div class="carrinho-item" data-product-id="<?php echo $produto['id']; ?>">
                                    
                                    <?php if (!empty($produto['imagem_referencia'])): ?>
                                        <img 
                                            src="../imagens/<?php echo htmlspecialchars($produto['imagem_referencia']); ?>" 
                                            alt="<?php echo htmlspecialchars($produto['nome']); ?>"
                                            class="carrinho-item-img"
                                        >
                                    <?php else: ?>
                                        <div class="carrinho-item-img-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="carrinho-item-info">
                                        <h4><?php echo htmlspecialchars($produto['nome']); ?></h4>
                                        <p class="produto-descricao">
                                            <?php echo htmlspecialchars($produto['descricao'] ?? 'Produto artesanal de qualidade'); ?>
                                        </p>
                                        <p class="produto-preco">
                                            R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?>
                                        </p>

                                        <!-- CONTROLE DE QUANTIDADE -->
                                        <div class="carrinho-item-qtd">
                                            <form action="update_carrinho.php" method="POST" class="d-inline">
                                                <input type="hidden" name="product_id" value="<?php echo $produto['id']; ?>">
                                                <input type="hidden" name="quantity" value="<?php echo max(0, $produto['quantity'] - 1); ?>">
                                                <button 
                                                    type="submit" 
                                                    name="action" 
                                                    value="update" 
                                                    class="quantity-btn btn-minus"
                                                    aria-label="Diminuir quantidade"
                                                >
                                                    −
                                                </button>
                                            </form>
                                            
                                            <input 
                                                type="number" 
                                                class="quantity-input" 
                                                value="<?php echo $produto['quantity']; ?>" 
                                                readonly
                                                aria-label="Quantidade de <?php echo htmlspecialchars($produto['nome']); ?>"
                                            >
                                            
                                            <form action="update_carrinho.php" method="POST" class="d-inline">
                                                <input type="hidden" name="product_id" value="<?php echo $produto['id']; ?>">
                                                <input type="hidden" name="quantity" value="<?php echo $produto['quantity'] + 1; ?>">
                                                <button 
                                                    type="submit" 
                                                    name="action" 
                                                    value="update" 
                                                    class="quantity-btn btn-plus"
                                                    aria-label="Aumentar quantidade"
                                                >
                                                    +
                                                </button>
                                            </form>
                                        </div>

                                        <!-- SUBTOTAL -->
                                        <p class="produto-subtotal">
                                            Subtotal: <strong>R$ <?php echo number_format($produto['subtotal'], 2, ',', '.'); ?></strong>
                                        </p>

                                        <!-- REMOVER PRODUTO -->
                                        <form action="update_carrinho.php" method="POST" class="d-inline">
                                            <input type="hidden" name="product_id" value="<?php echo $produto['id']; ?>">
                                            <button 
                                                type="submit" 
                                                name="action" 
                                                value="remove" 
                                                class="remove-btn"
                                                onclick="return confirm('Remover este produto do carrinho?')"
                                            >
                                                <i class="fas fa-trash"></i> Remover
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </section>
                    <?php endif; ?>

                    <!-- PRODUTOS PERSONALIZADOS -->
                    <?php if (!empty($cart_personalizado)): ?>
                        <section class="personalizados-section mt-4">
                            <h3>Produtos Personalizados (Sob Encomenda)</h3>
                            
                            <?php foreach ($cart_personalizado as $i => $item): ?>
                                <div class="carrinho-item personalizado-item">
                                    
                                    <?php if (!empty($item['imagem_path'])): ?>
                                        <img 
                                            src="<?php echo '../' . htmlspecialchars($item['imagem_path']); ?>"
                                            alt="Referência do produto personalizado"
                                            class="carrinho-item-img"
                                            style="width:80px;height:80px;object-fit:cover;border-radius:8px;"
                                        >
                                    <?php else: ?>
                                        <div class="carrinho-item-img-placeholder" style="width:80px;height:80px;">
                                            <i class="fas fa-star"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="carrinho-item-info">
                                        <h4>
                                            <?php echo ucfirst(htmlspecialchars($item['tipo'] ?? '')); ?> 
                                            — 
                                            <?php echo htmlspecialchars($item['tema'] ?? 'Sem tema'); ?>
                                        </h4>

                                        <!-- INFORMAÇÕES COMUNS -->
                                        <div class="personalizado-details">
                                            <?php if (!empty($item['sabor'])): ?>
                                                <p>
                                                    <strong>Sabor:</strong> 
                                                    <?php echo htmlspecialchars($item['sabor']); ?>
                                                </p>
                                            <?php endif; ?>

                                            <?php if (!empty($item['tamanho'])): ?>
                                                <p>
                                                    <strong>Tamanho:</strong> 
                                                    <?php echo htmlspecialchars($item['tamanho']); ?>
                                                </p>
                                            <?php endif; ?>

                                            <?php if (!empty($item['cor'])): ?>
                                                <p>
                                                    <strong>Cor:</strong> 
                                                    <?php echo htmlspecialchars($item['cor']); ?>
                                                </p>
                                            <?php endif; ?>

                                            <?php if (!empty($item['restricoes']) && is_array($item['restricoes'])): ?>
                                                <p>
                                                    <strong>Restrições:</strong> 
                                                    <?php echo htmlspecialchars(implode(', ', $item['restricoes'])); ?>
                                                </p>
                                            <?php endif; ?>

                                            <?php if (!empty($item['data_desejada'])): ?>
                                                <p>
                                                    <strong>Data desejada:</strong> 
                                                    <?php 
                                                        $data = new DateTime($item['data_desejada']);
                                                        echo $data->format('d/m/Y'); 
                                                    ?>
                                                </p>
                                            <?php endif; ?>

                                            <?php if (!empty($item['detalhes'])): ?>
                                                <p>
                                                    <strong>Detalhes:</strong> 
                                                    <em><?php echo htmlspecialchars($item['detalhes']); ?></em>
                                                </p>
                                            <?php endif; ?>

                                            <!-- INFORMAÇÕES ESPECÍFICAS DO BOLO -->
                                            <?php if ($item['tipo'] === 'bolo' && !empty($item['andares'])): ?>
                                                <p>
                                                    <strong>Andares:</strong> 
                                                    <?php echo intval($item['andares']); ?>
                                                </p>
                                            <?php endif; ?>

                                            <?php if ($item['tipo'] === 'bolo' && !empty($item['pessoas'])): ?>
                                                <p>
                                                    <strong>Pessoas:</strong> 
                                                    <?php echo intval($item['pessoas']); ?>
                                                </p>
                                            <?php endif; ?>

                                            <?php if ($item['tipo'] === 'bolo' && !empty($item['cobertura'])): ?>
                                                <p>
                                                    <strong>Cobertura:</strong> 
                                                    <?php echo htmlspecialchars($item['cobertura']); ?>
                                                </p>
                                            <?php endif; ?>

                                            <?php if (!empty($item['camadas_sabor']) && is_array($item['camadas_sabor'])): ?>
                                                <p>
                                                    <strong>Sabores por camada:</strong> 
                                                    <?php 
                                                        $sabores = array_map(function($s) { 
                                                            return htmlspecialchars($s); 
                                                        }, $item['camadas_sabor']);
                                                        echo implode(', ', $sabores); 
                                                    ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>

                                        <p class="personalizado-qty">
                                            <strong>Quantidade:</strong> <?php echo intval($item['quantity'] ?? 1); ?>
                                        </p>

                                        <p class="text-success">
                                            <em>💰 Preço sob orçamento</em>
                                        </p>

                                        <!-- REMOVER PERSONALIZADO -->
                                        <form action="update_carrinho.php" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="remove_personalizado">
                                            <input type="hidden" name="index" value="<?php echo $i; ?>">
                                            <button 
                                                type="submit" 
                                                class="remove-btn"
                                                onclick="return confirm('Remover este produto personalizado?')"
                                            >
                                                <i class="fas fa-trash"></i> Remover
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </section>
                    <?php endif; ?>

                    <!-- PRODUTOS INDISPONÍVEIS -->
                    <?php if (!empty($unavailableItems)): ?>
                        <section class="unavailable-section mt-4">
                            <h3>Itens Indisponíveis</h3>
                            
                            <form action="update_carrinho.php" method="POST">
                                <button 
                                    type="submit" 
                                    name="action" 
                                    value="remove_unavailable" 
                                    class="clear-unavailable-btn"
                                    onclick="return confirm('Remover todos os itens indisponíveis?')"
                                >
                                    <i class="fas fa-times"></i> Excluir todos os itens indisponíveis
                                </button>
                            </form>

                            <?php foreach ($unavailableItems as $produto): ?>
                                <div class="carrinho-item unavailable" style="opacity: 0.6;">
                                    
                                    <?php if (!empty($produto['imagem_referencia'])): ?>
                                        <img 
                                            src="../imagens/<?php echo htmlspecialchars($produto['imagem_referencia']); ?>" 
                                            alt="<?php echo htmlspecialchars($produto['nome']); ?>"
                                            class="carrinho-item-img"
                                        >
                                    <?php else: ?>
                                        <div class="carrinho-item-img-placeholder">
                                            <i class="fas fa-ban"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="carrinho-item-info">
                                        <h4><?php echo htmlspecialchars($produto['nome']); ?></h4>
                                        <p><?php echo htmlspecialchars($produto['descricao'] ?? 'Produto artesanal'); ?></p>
                                        
                                        <div class="unavailable-badge">
                                            <i class="fas fa-exclamation-circle"></i> Indisponível
                                        </div>
                                        
                                        <p>Quantidade reservada: <?php echo $produto['quantity']; ?></p>

                                        <!-- REMOVER INDISPONÍVEL -->
                                        <form action="update_carrinho.php" method="POST" class="d-inline">
                                            <input type="hidden" name="product_id" value="<?php echo $produto['id']; ?>">
                                            <button 
                                                type="submit" 
                                                name="action" 
                                                value="remove" 
                                                class="remove-btn"
                                            >
                                                <i class="fas fa-trash"></i> Remover
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </section>
                    <?php endif; ?>

                </div>

                <!-- RESUMO DO CARRINHO -->
                <aside class="carrinho-summary">
                    <h4>Resumo da Compra</h4>

                    <div class="summary-row">
                        <span>Total de itens:</span>
                        <span class="summary-value"><?php echo $total_itens_carrinho; ?></span>
                    </div>

                    <div class="summary-row">
                        <span>Itens padrão:</span>
                        <span class="summary-value"><?php echo $qtd_normais; ?></span>
                    </div>

                    <div class="summary-row">
                        <span>Itens personalizados:</span>
                        <span class="summary-value"><?php echo $qtd_personalizados; ?></span>
                    </div>

                    <hr>

                    <div class="summary-row">
                        <span>Subtotal padrão:</span>
                        <span class="summary-value">R$ <?php echo number_format($total, 2, ',', '.'); ?></span>
                    </div>

                    <div class="summary-row total">
                        <span>Total Atual:</span>
                        <span class="summary-value">R$ <?php echo number_format($total, 2, ',', '.'); ?></span>
                    </div>

                    <div class="summary-note">
                        <small class="text-muted">
                            <i class="fa-solid fa-store"></i> Somente retirada na loja
                        </small>
                    </div>

                    <button 
                        class="checkout-btn" 
                        onclick="checkout()" 
                        <?php echo (!$usuario_logado || $total_itens_carrinho <= 0) ? 'disabled' : ''; ?>
                    >
                        <i class="fas fa-check"></i> Finalizar Compra
                    </button>

                    <?php if (!$usuario_logado): ?>
                        <p class="text-muted small mt-3">
                            Faça login para poder finalizar o pedido.
                        </p>
                    <?php endif; ?>

                    <a href="cardapio.php" class="btn-continue-shopping">
                        <i class="fas fa-arrow-left"></i> Continuar Comprando
                    </a>
                </aside>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function checkout() {
            window.location.href = 'checkout_form.php';
        }
    </script>

    <?php 
        include '../inc/modal.php';
        include(FOOTER_TEMPLATE);
    ?>
</body>
</html>