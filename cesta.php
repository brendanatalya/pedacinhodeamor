<?php
if (!isset($_SESSION)) session_start();
require_once 'config.php';
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
<title>Pedacinho de Amor - Cesta</title>
<link rel="stylesheet" href="css_pda/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="css_pda/style_pda.css">
<link rel="stylesheet" href="fontawesome/css/all.min.css">
</head>
<body>
<header>
<h1>Pedacinho de Amor</h1>
<nav>
<ul>
<li><a href="index.php">Home</a></li>
<li><a href="produtos.php?tipo=doce">Doces</a></li>
<li><a href="produtos.php?tipo=salgado">Salgados</a></li>
<li><a href="produtos.php?tipo=personalizado">Personalizados</a></li>
<?php if ($usuario_logado): ?>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            👤 Olá, <?php echo htmlspecialchars($_SESSION['nome']); ?>
        </a>
        <ul class="dropdown-menu" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="inc/logout.php">Sair</a></li>
        </ul>
    </li>
<?php else: ?>
    <li><a class="btn btn-primary text-white" href="index.php">Login</a></li>
<?php endif; ?>
<li><a href="cesta.php">Cesta</a></li>
</ul>
</nav>
</header>

<div class="container my-4">
    <?php if ($cartMessage): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($cartMessage); ?></div>
    <?php endif; ?>

    <?php if (!$usuario_logado): ?>
        <div class="alert alert-warning">Faça login para finalizar seu pedido.</div>
    <?php endif; ?>

    <?php if (empty($availableItems) && empty($unavailableItems)): ?>
        <div class="alert alert-secondary">Seu carrinho está vazio.</div>
        <a href="produtos.php" class="btn btn-primary">Ver produtos</a>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2>SUA CESTA</h2>
                        <span class="text-muted">Resumo</span>
                    </div>

                    <?php if (!empty($availableItems)): ?>
                        <?php foreach ($availableItems as $produto): ?>
                            <div class="card mb-3">
                                <div class="row g-0 align-items-center">
                                    <?php if (!empty($produto['imagem_referencia'])): ?>
                                        <div class="col-md-4">
                                            <img src="<?php echo htmlspecialchars($produto['imagem_referencia']); ?>" class="img-fluid rounded-start" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                                        </div>
                                    <?php endif; ?>
                                    <div class="col">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                                            <p class="card-text text-muted">Prazo de entrega estimado: 3-5 dias úteis</p>
                                            <p class="card-text"><strong>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></strong></p>
                                            <form action="update_carrinho.php" method="POST" class="row g-2 align-items-center">
                                                <input type="hidden" name="product_id" value="<?php echo $produto['id']; ?>">
                                                <div class="col-auto">
                                                    <label class="form-label">Qtd</label>
                                                    <input type="number" name="quantity" value="<?php echo $produto['quantity']; ?>" min="1" class="form-control" style="width: 90px;">
                                                </div>
                                                <div class="col-auto mt-4">
                                                    <button type="submit" name="action" value="update" class="btn btn-sm btn-outline-primary">Atualizar</button>
                                                    <button type="submit" name="action" value="remove" class="btn btn-sm btn-outline-danger">Remover</button>
                                                </div>
                                            </form>
                                            <p class="card-text mt-2"><small class="text-muted">Subtotal: R$ <?php echo number_format($produto['subtotal'], 2, ',', '.'); ?></small></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($unavailableItems)): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="text-danger">Itens indisponíveis</h5>
                                <form action="update_carrinho.php" method="POST">
                                    <button type="submit" name="action" value="remove_unavailable" class="btn btn-sm btn-outline-danger">Excluir todos os itens indisponíveis</button>
                                </form>
                            </div>
                            <?php foreach ($unavailableItems as $produto): ?>
                                <div class="card mb-3 border-danger bg-light">
                                    <div class="row g-0 align-items-center">
                                        <?php if (!empty($produto['imagem_referencia'])): ?>
                                            <div class="col-md-4">
                                                <img src="<?php echo htmlspecialchars($produto['imagem_referencia']); ?>" class="img-fluid rounded-start" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                                            </div>
                                        <?php endif; ?>
                                        <div class="col">
                                            <div class="card-body">
                                                <h5 class="card-title text-muted"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                                                <span class="badge bg-danger">Indisponível</span>
                                                <p class="card-text text-muted">Quantidade reservada: <?php echo $produto['quantity']; ?></p>
                                                <p class="card-text text-muted"><small>Este produto está indisponível no momento e não será contabilizado no total.</small></p>
                                                <form action="update_carrinho.php" method="POST" class="mb-0">
                                                    <input type="hidden" name="product_id" value="<?php echo $produto['id']; ?>">
                                                    <button type="submit" name="action" value="remove" class="btn btn-sm btn-outline-danger">Remover</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card p-4">
                    <h4>Resumo do pedido</h4>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total de itens</span>
                        <span>R$ <?php echo number_format($total, 2, ',', '.'); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Estimativa de frete</span>
                        <span>R$ <?php echo $total > 0 ? number_format($frete, 2, ',', '.') : '0,00'; ?></span>
                    </div>
                    <div class="mb-3">
                        <strong>Prazo de entrega</strong>
                        <p class="mb-0 text-muted">3-5 dias úteis após confirmação de pagamento.</p>
                    </div>
                    <div class="mb-3">
                        <strong>Observação</strong>
                        <p class="mb-0 text-muted">O prazo de entrega pode variar de acordo com a forma de pagamento.</p>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold mb-3">
                        <span>Total a pagar</span>
                        <span>R$ <?php echo number_format($total > 0 ? $total + $frete : 0, 2, ',', '.'); ?></span>
                    </div>
                    <button class="btn btn-success w-100" <?php echo (!$usuario_logado || $total <= 0) ? 'disabled' : ''; ?>>Finalizar compra</button>
                    <?php if (!$usuario_logado): ?>
                        <p class="text-muted small mt-2">Faça login para poder finalizar o pedido.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="js/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
