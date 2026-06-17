<?php
if (!isset($_SESSION)) session_start();
include '../config.php';
require_once ABSPATH . 'inc/database.php';
require_once DBAPI; 
include(HEADER_TEMPLATE);

// Precisa estar logado como cliente
if (empty($_SESSION['logado']) || $_SESSION['tipo'] !== 'cliente') {
    header('Location: ' . BASEURL . 'index.php');
    exit;
}

$usuario_id         = $_SESSION['id'];
$cart               = $_SESSION['cart'] ?? [];
$cart_personalizado = $_SESSION['cart_personalizado'] ?? [];

// Buscar dados do usuário
$conn  = open_database();
$stmt  = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
close_database($conn);

// Montar itens do carrinho normal
$cart_items = [];
$total      = 0;

foreach ($cart as $product_id => $qty) {
    $produto = find_product(intval($product_id));
    if (!$produto || !$produto['disponivel']) {
        unset($_SESSION['cart'][$product_id]);
        continue;
    }
    $produto['quantity'] = max(1, intval($qty));
    $produto['subtotal'] = $produto['quantity'] * floatval($produto['preco']);
    $cart_items[]        = $produto;
    $total              += $produto['subtotal'];
}

// Redireciona se carrinho vazio
if (empty($cart_items) && empty($cart_personalizado)) {
    header('Location: ' . BASEURL . 'paginas/carrinho.php');
    exit;
}
?>

<body>

    <div class="container mt-5 mb-5">
        <h2 class="mb-4 titulocarrinho">Finalizar Compra</h2>

        <!-- Mensagem de erro/sucesso -->
        <div id="alertMsg" class="alert d-none" role="alert"></div>

        <form id="checkoutForm">
            <div class="row">

                <!-- ===== LADO ESQUERDO ===== -->
                <div class="col-md-8">

                    <!-- DADOS DE RETIRADA -->
                    <div class="checkout-section">
                        <h5 class="section-title">
                            <i class="fas fa-map-marker-alt"></i> Dados de Retirada
                        </h5>

                        <input type="hidden" name="tipo_entrega" value="retirada">
                        <p class="text-muted mb-3">
                            <i class="fas fa-shopping-bag me-1"></i> Retirada na loja
                        </p>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Data de Retirada *</label>
                                    <input type="date" name="data_entrega" id="data_entrega"
                                        class="form-control" required
                                        min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Horário *</label>
                                    <input type="time" name="hora_entrega" id="hora_entrega"
                                        class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PERSONALIZAÇÕES DOS PRODUTOS NORMAIS -->
                    <?php if (!empty($cart_items)): ?>
                    <div class="checkout-section">
                        <h5 class="section-title">
                            <i class="fas fa-gift"></i> Personalizações
                        </h5>

                        <?php foreach ($cart_items as $item): ?>
                            <div class="form-section mb-3">
                                <h6><?php echo htmlspecialchars($item['nome']); ?>
                                    <small class="text-muted">x<?php echo $item['quantity']; ?></small>
                                </h6>

                                <?php if (in_array($item['tipo'], ['bolo', 'personalizado'])): ?>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="form-label">Sabor da Massa</label>
                                                <input type="text"
                                                    name="sabor_massa[<?php echo $item['id']; ?>]"
                                                    class="form-control"
                                                    placeholder="Ex: Chocolate, Baunilha...">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="form-label">Recheio</label>
                                                <input type="text"
                                                    name="sabor_recheio[<?php echo $item['id']; ?>]"
                                                    class="form-control"
                                                    placeholder="Ex: Morango, Brigadeiro...">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="form-label">Topping</label>
                                                <input type="text"
                                                    name="topping[<?php echo $item['id']; ?>]"
                                                    class="form-control"
                                                    placeholder="Ex: Morango, Confete...">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="form-label">Decoração</label>
                                                <input type="text"
                                                    name="decoracao[<?php echo $item['id']; ?>]"
                                                    class="form-control"
                                                    placeholder="Ex: Escrito de Parabéns...">
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="form-group mb-0">
                                    <label class="form-label">Observações</label>
                                    <textarea name="observacoes_item[<?php echo $item['id']; ?>]"
                                        class="form-control" rows="2"
                                        placeholder="Observações sobre este item..."></textarea>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- PERSONALIZADOS NO CARRINHO -->
                    <?php if (!empty($cart_personalizado)): ?>
                    <div class="checkout-section">
                        <h5 class="section-title">
                            <i class="fas fa-paint-brush"></i> Produtos Personalizados
                        </h5>
                        <?php foreach ($cart_personalizado as $idx => $p): ?>
                            <div class="form-section mb-2">
                                <strong><?php echo ucfirst(htmlspecialchars($p['tipo'])); ?></strong>
                                — <?php echo htmlspecialchars($p['sabor']); ?>
                                <?php if (!empty($p['tema'])): ?>
                                    | Tema: <?php echo htmlspecialchars($p['tema']); ?>
                                <?php endif; ?>
                                <br>
                                <small class="text-muted">Qtd: <?php echo $p['quantity']; ?>
                                    <?php if ($p['data_desejada']): ?>
                                        · Data desejada: <?php echo date('d/m/Y', strtotime($p['data_desejada'])); ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            O valor dos personalizados será combinado via WhatsApp.
                        </small>
                    </div>
                    <?php endif; ?>

                    <!-- OBSERVAÇÕES GERAIS -->
                    <div class="checkout-section">
                        <h5 class="section-title">
                            <i class="fas fa-sticky-note"></i> Observações Gerais
                        </h5>
                        <textarea name="observacoes" class="form-control" rows="3"
                            placeholder="Alguma observação geral sobre o pedido..."></textarea>
                    </div>

                </div>

                <!-- ===== LADO DIREITO - RESUMO ===== -->
                <div class="col-md-4">
                    <div class="checkout-section" style="position:sticky;top:20px;">
                        <h5 class="section-title">
                            <i class="fas fa-receipt"></i> Resumo do Pedido
                        </h5>

                        <div class="resumo">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="resumo-item">
                                    <span><?php echo htmlspecialchars($item['nome']); ?> x<?php echo $item['quantity']; ?></span>
                                    <span>R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></span>
                                </div>
                            <?php endforeach; ?>

                            <?php if (!empty($cart_personalizado)): ?>
                                <div class="resumo-item text-muted">
                                    <span><?php echo count($cart_personalizado); ?> personalizado(s)</span>
                                    <span>A combinar</span>
                                </div>
                            <?php endif; ?>

                            <div class="resumo-item total">
                                <span>Total produtos:</span>
                                <span>R$ <?php echo number_format($total, 2, ',', '.'); ?></span>
                            </div>
                        </div>

                        <hr>

                        <h6 class="section-title">Dados do Cliente</h6>
                        <p class="small">
                            <strong><?php echo htmlspecialchars($usuario['nome']); ?></strong><br>
                            <?php echo htmlspecialchars($usuario['email']); ?><br>
                            <?php if (!empty($usuario['telefone'])): ?>
                                <?php echo htmlspecialchars($usuario['telefone']); ?><br>
                            <?php endif; ?>
                            <?php if (!empty($usuario['endereco'])): ?>
                                <?php echo htmlspecialchars($usuario['endereco']); ?>
                            <?php endif; ?>
                        </p>

                        <button type="submit" class="btn btn-primary w-100 btn-lg" id="submitBtn">
                            <i class="fas fa-check-circle"></i> Confirmar Pedido
                        </button>
                        <a href="<?php echo BASEURL; ?>paginas/carrinho.php"
                            class="btn btn-secondary w-100 mt-2">
                            <i class="fas fa-arrow-left"></i> Voltar ao Carrinho
                        </a>
                    </div>
                </div>

            </div><!-- /row -->
        </form>
    </div>

    <?php include '../inc/footer.php'; ?>

    <script src="<?php echo BASEURL; ?>js/bootstrap/bootstrap.bundle.min.js"></script>
    <script>
    // ----- Mínimo da data: amanhã -----
    document.addEventListener('DOMContentLoaded', function () {
        const dataInput = document.getElementById('data_entrega');
        if (dataInput && !dataInput.min) {
            const amanha = new Date();
            amanha.setDate(amanha.getDate() + 1);
            dataInput.min = amanha.toISOString().split('T')[0];
        }
    });

    // ----- Submit via fetch -----
    document.getElementById('checkoutForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const btn     = document.getElementById('submitBtn');
        const alertEl = document.getElementById('alertMsg');
        alertEl.className = 'alert d-none';

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

        try {
            const response = await fetch('checkout.php', {
                method: 'POST',
                body: new FormData(this),
            });

            const data = await response.json();

            if (!data.success) {
                alertEl.className  = 'alert alert-danger';
                alertEl.textContent = data.message;
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle"></i> Confirmar Pedido';
                return;
            }

            // ----- Monta mensagem WhatsApp -----
            const nomeCliente = `<?php echo addslashes(htmlspecialchars($usuario['nome'])); ?>`;
            const telefone    = `<?php echo addslashes(htmlspecialchars($usuario['telefone'] ?? '')); ?>`;
            const dataEntrega = document.getElementById('data_entrega').value;
            const horaEntrega = document.getElementById('hora_entrega').value;
            const observacoes = document.querySelector('textarea[name="observacoes"]').value;

            let msg = `🎂 *NOVO PEDIDO - PEDACINHO DE AMOR*\n\n`;
            msg += `📌 *Pedido:* #${data.pedido_id}\n`;
            msg += `👤 *Cliente:* ${nomeCliente}\n`;
            if (telefone) msg += `📞 *Telefone:* ${telefone}\n`;
            msg += `🏪 *Retirada na loja*\n`;
            msg += `📅 *Data:* ${dataEntrega}\n`;
            msg += `⏰ *Horário:* ${horaEntrega}\n\n`;
            msg += `🧁 *ITENS DO PEDIDO*\n\n`;

            <?php foreach ($cart_items as $item): ?>
            msg += `• <?php echo addslashes($item['nome']); ?> x<?php echo $item['quantity']; ?> — R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?>\n`;
            <?php endforeach; ?>

            <?php if (!empty($cart_personalizado)): ?>
            msg += `\n🎨 *Personalizados:*\n`;
            <?php foreach ($cart_personalizado as $p): ?>
            msg += `• <?php echo addslashes(ucfirst($p['tipo'])); ?> — <?php echo addslashes($p['sabor']); ?><?php echo !empty($p['tema']) ? ' | Tema: ' . addslashes($p['tema']) : ''; ?> (Qtd: <?php echo $p['quantity']; ?>)\n`;
            <?php endforeach; ?>
            <?php endif; ?>

            if (observacoes) msg += `\n📝 *Obs:* ${observacoes}\n`;
            msg += `\n💰 *Total produtos:* R$ <?php echo number_format($total, 2, ',', '.'); ?>`;
            msg += `\n✅ Pedido registrado no sistema!`;

            const numeroWpp = '<?php echo WHATSAPP_NUMBER; ?>';
            window.open(`https://wa.me/${numeroWpp}?text=${encodeURIComponent(msg)}`, '_blank');

            setTimeout(() => {
                window.location.href = '<?php echo BASEURL; ?>minha_conta.php?sucesso=1';
            }, 1500);

        } catch (err) {
            console.error(err);
            alertEl.className  = 'alert alert-danger';
            alertEl.textContent = 'Erro de conexão ao finalizar pedido. Tente novamente.';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Confirmar Pedido';
        }
    });
    </script>
</body>
</html>