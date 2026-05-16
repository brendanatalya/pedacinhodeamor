<?php 
if (!isset($_SESSION)) session_start();
include '../config.php';
require_once ABSPATH . 'inc/database.php';

// Verificar se usuário está logado
if (empty($_SESSION['logado']) || $_SESSION['tipo'] !== 'cliente') {
    header('Location: ' . BASEURL . 'index.php');
    exit;
}

$usuario_id = $_SESSION['id'];
$cart = $_SESSION['cart'] ?? [];

// Buscar dados do usuário
$conn = open_database();
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
close_database($conn);

// Buscar produtos do carrinho
$cart_items = [];
$total = 0;
$frete = 12.28;

foreach ($cart as $product_id => $qty) {
    $produto = find_product(intval($product_id));
    if (!$produto || !$produto['disponivel']) {
        unset($_SESSION['cart'][$product_id]);
        continue;
    }
    
    $produto['quantity'] = max(1, intval($qty));
    $produto['subtotal'] = $produto['quantity'] * floatval($produto['preco']);
    $cart_items[] = $produto;
    $total += $produto['subtotal'];
}

$total_com_frete = $total + $frete;

if (empty($cart_items)) {
    header('Location: ' . BASEURL . 'paginas/carrinho.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - Pedacinho de Amor</title>
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>/css_pda/style_pda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>
<body>
    <?php include '../inc/header.php'; ?>

    <div class="container mt-5 mb-5">
        <h2 class="mb-4">
            <i class="fas fa-cash-register"></i> Finalizar Compra
        </h2>

        <form id="checkoutForm" method="POST" action="checkout.php">
            <div class="row">
                <!-- LADO ESQUERDO - FORMULÁRIO -->
                <div class="col-md-8">
                    
                    <!-- DADOS DE ENTREGA -->
                    <div class="checkout-section">
                        <h5 class="section-title">
                            <i class="fas fa-map-marker-alt"></i> Dados de Entrega/Retirada
                        </h5>

                        <div class="form-group mb-3">
                            <label class="form-label">Tipo de Entrega *</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="tipo_entrega" id="retirada" value="retirada" checked>
                                <label class="btn btn-outline-primary" for="retirada">
                                    <i class="fas fa-shopping-bag"></i> Retirada na Loja
                                </label>

                                <input type="radio" class="btn-check" name="tipo_entrega" id="entrega" value="entrega">
                                <label class="btn btn-outline-primary" for="entrega">
                                    <i class="fas fa-truck"></i> Entrega
                                </label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Data de Entrega/Retirada *</label>
                                    <input type="date" name="data_entrega" class="form-control" required
                                        min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Horário *</label>
                                    <input type="time" name="hora_entrega" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0" id="endereco-entrega" style="display: none;">
                            <label class="form-label">Confirmar Endereço de Entrega</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($usuario['endereco']); ?>" disabled>
                            <small class="form-text text-muted">Se desejar alterar, acesse sua conta</small>
                        </div>
                    </div>

                    <!-- PERSONALIZAÇÕES DOS PRODUTOS -->
                    <div class="checkout-section">
                        <h5 class="section-title">
                            <i class="fas fa-gift"></i> Personalizações
                        </h5>

                        <?php foreach ($cart_items as $item): ?>
                            <div class="form-section">
                                <h6><?php echo htmlspecialchars($item['nome']); ?></h6>
                                <p class="text-muted">Quantidade: <?php echo $item['quantity']; ?></p>

                                <?php if (in_array($item['tipo'], ['bolo', 'personalizado'])): ?>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="form-label">Sabor da Massa</label>
                                                <input type="text" name="sabor_massa[<?php echo $item['id']; ?>]" 
                                                    class="form-control" placeholder="Ex: Chocolate, Baunilha...">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="form-label">Sabor do Recheio</label>
                                                <input type="text" name="sabor_recheio[<?php echo $item['id']; ?>]" 
                                                    class="form-control" placeholder="Ex: Morango, Brigadeiro...">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="form-label">Topping</label>
                                                <input type="text" name="topping[<?php echo $item['id']; ?>]" 
                                                    class="form-control" placeholder="Ex: Morango, Confete...">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="form-label">Decoração</label>
                                                <input type="text" name="decoracao[<?php echo $item['id']; ?>]" 
                                                    class="form-control" placeholder="Ex: Escrito de Parabéns...">
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="form-group mb-0">
                                    <label class="form-label">Observações</label>
                                    <textarea name="observacoes_item[<?php echo $item['id']; ?>]" 
                                        class="form-control" rows="2" placeholder="Observações sobre este item..."></textarea>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- OBSERVAÇÕES GERAIS -->
                    <div class="checkout-section">
                        <h5 class="section-title">
                            <i class="fas fa-sticky-note"></i> Observações Gerais
                        </h5>

                        <div class="form-group">
                            <label class="form-label">Observações do Pedido</label>
                            <textarea name="observacoes" class="form-control" rows="3" 
                                placeholder="Deixe sua mensagem aqui..."></textarea>
                        </div>
                    </div>

                </div>

                <!-- LADO DIREITO - RESUMO -->
                <div class="col-md-4">
                    
                    <!-- RESUMO DO PEDIDO -->
                    <div class="checkout-section" style="position: sticky; top: 20px;">
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

                            <div class="resumo-item">
                                <span>Subtotal:</span>
                                <span>R$ <?php echo number_format($total, 2, ',', '.'); ?></span>
                            </div>

                            <div class="resumo-item" id="frete-item">
                                <span>Frete/Taxa:</span>
                                <span id="frete-value">R$ <?php echo number_format($frete, 2, ',', '.'); ?></span>
                                <input type="hidden" name="frete" value="<?php echo $frete; ?>">
                            </div>

                            <div class="resumo-item total">
                                <span>Total:</span>
                                <span>R$ <?php echo number_format($total_com_frete, 2, ',', '.'); ?></span>
                            </div>
                        </div>

                        <hr>

                        <!-- DADOS DO CLIENTE -->
                        <h6 class="section-title">Dados do Cliente</h6>
                        <p class="small">
                            <strong><?php echo htmlspecialchars($usuario['nome']); ?></strong><br>
                            <?php echo htmlspecialchars($usuario['email']); ?><br>
                            <?php echo htmlspecialchars($usuario['telefone']); ?><br>
                            <?php echo htmlspecialchars($usuario['endereco']); ?>
                        </p>

                        <button type="submit" class="btn btn-primary w-100 btn-lg" id="submitBtn">
                            <i class="fas fa-check-circle"></i> Confirmar Pedido
                        </button>

                        <a href="<?php echo BASEURL; ?>paginas/carrinho.php" class="btn btn-secondary w-100 mt-2">
                            <i class="fas fa-arrow-left"></i> Voltar ao Carrinho
                        </a>
                    </div>

                </div>
            </div>
        </form>

    </div>

    <?php include '../inc/footer.php'; ?>

    <script src="<?php echo BASEURL; ?>js/bootstrap/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar/Ocultar endereço de entrega
        document.querySelectorAll('input[name="tipo_entrega"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                const enderecoDiv = document.getElementById('endereco-entrega');
                if (e.target.value === 'entrega') {
                    enderecoDiv.style.display = 'block';
                } else {
                    enderecoDiv.style.display = 'none';
                }
            });
        });

document.getElementById('checkoutForm').addEventListener('submit', async (e) => {

    e.preventDefault();

    const form = document.getElementById('checkoutForm');
    const formData = new FormData(form);

    try {

        // SALVA O PEDIDO NO BANCO
        const response = await fetch('checkout.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (!data.success) {
            alert(data.message);
            return;
        }

        // =========================
        // DADOS DO CLIENTE
        // =========================

        const nomeCliente = `<?php echo htmlspecialchars($usuario['nome']); ?>`;
        const telefone = `<?php echo htmlspecialchars($usuario['telefone']); ?>`;
        const endereco = `<?php echo htmlspecialchars($usuario['endereco']); ?>`;

        const tipoEntrega =
            document.querySelector('input[name="tipo_entrega"]:checked').value;

        const dataEntrega =
            document.querySelector('input[name="data_entrega"]').value;

        const horaEntrega =
            document.querySelector('input[name="hora_entrega"]').value;

        const observacoes =
            document.querySelector('textarea[name="observacoes"]').value;

        // =========================
        // MONTA MENSAGEM
        // =========================

        let mensagem = `🎂 *NOVO PEDIDO - PEDACINHO DE AMOR* \n\n`;

        mensagem += `📌 *Pedido:* #${data.pedido_id}\n\n`;

        mensagem += `👤 *Cliente:* ${nomeCliente}\n`;
        mensagem += `📞 *Telefone:* ${telefone}\n`;

        if (tipoEntrega === 'entrega') {
            mensagem += `🚚 *Entrega:* ${endereco}\n`;
        } else {
            mensagem += `🏪 *Retirada na loja*\n`;
        }

        mensagem += `📅 *Data:* ${dataEntrega}\n`;
        mensagem += `⏰ *Horário:* ${horaEntrega}\n\n`;

        mensagem += `🧁 *ITENS DO PEDIDO*\n\n`;

        <?php foreach ($cart_items as $item): ?>

            mensagem += `• <?php echo addslashes($item['nome']); ?> `;
            mensagem += `x<?php echo $item['quantity']; ?> `;
            mensagem += `- R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?>\n`;

        <?php endforeach; ?>

        mensagem += `\n💰 *TOTAL:* R$ <?php echo number_format($total_com_frete, 2, ',', '.'); ?>\n\n`;

        if (observacoes) {
            mensagem += `📝 *Observações:* ${observacoes}\n\n`;
        }

        mensagem += `✅ Pedido registrado no sistema!`;

        // =========================
        // WHATSAPP
        // =========================

        const numeroWhatsapp = '5515988329726';

        const link =
            `https://wa.me/${numeroWhatsapp}?text=${encodeURIComponent(mensagem)}`;

        // abre whatsapp
        window.open(link, '_blank');

        // redireciona cliente
        setTimeout(() => {

           window.location.href = '/pedacinhodeamor/paginas/minha_conta.php?sucesso=1';

        }, 1500);

    } catch (error) {

        console.error(error);

        alert('Erro ao finalizar pedido.');

    }

});

        // Validar datas mínimas
        const dataInput = document.querySelector('input[name="data_entrega"]');
        if (dataInput) {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const year = tomorrow.getFullYear();
            const month = String(tomorrow.getMonth() + 1).padStart(2, '0');
            const day = String(tomorrow.getDate()).padStart(2, '0');
            dataInput.min = `${year}-${month}-${day}`;
        }
    </script>
</body>
</html>
