<?php
if (!isset($_SESSION)) session_start();
require_once '../config.php';
require_once ABSPATH . 'inc/database.php';

// Estado de autenticação limpo
$usuario_logado = !empty($_SESSION['logado']) && $_SESSION['logado'] === true;

// Validação e cálculo resiliente do contador de itens na sessão
$itens_no_carrinho = (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) ? array_sum($_SESSION['cart']) : 0;

// Sanitização e isolamento da URI atual para evitar vetores de XSS refletido no redirect do form
$redirect_uri = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalizados - Pedacinho de Amor</title>
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
                <li><a href="sobrenos.html">Sobre Nós</a></li>
                <li><a href="doces.php">Doces</a></li>
                <li><a href="salgados.php">Salgados</a></li>
                <li><a href="personalizados.php" class="active">Personalizados</a></li>
                <li>
                    <a href="carrinho.php">
                        <i class="fas fa-shopping-cart"></i>
                        Carrinho (<?php echo $itens_no_carrinho; ?>)
                    </a>
                </li>
                <?php if ($usuario_logado): ?>
                    <li><a href="../inc/logout.php">Sair</a></li>
                <?php else: ?>
                    <li><a class="btn btn-primary text-white" href="../index.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section class="work-section">
            <div class="work-container">
                <div class="work-text">
                    <h2>Como trabalhamos?</h2>
                    <p>Cada doce, bolo e salgado é preparado de forma artesanal, com ingredientes selecionados e muito carinho.</p>
                    <p>Nossos produtos buscam entregar qualidade e aconchego; cada detalhe importa: um recheio cremoso, a textura macia de nossas tortas e o cuidado na decoração de cada sobremesa.</p>
                    <p>Produzimos em pequenas quantidades para garantir qualidade e sabor autêntico, mantendo sempre o nosso toque caseiro.</p>
                </div>
                <div class="work-image">
                    <img src="imagens/doce3.webp" alt="Como trabalhamos Pedacinho de Amor">
                </div>
            </div>
        </section>

        <div class="container my-5">
    <div class="text-center mb-5">
        <h2 class="section-title">Monte seu Produto Personalizado</h2>
        <p class="text-muted">Escolha o tipo, tema, sabor e detalhes — e adicione ao carrinho!</p>
    </div>

    <?php if (!$usuario_logado): ?>
        <div class="alert alert-warning text-center col-md-8 mx-auto mb-4">
            <i class="fas fa-lock me-2"></i>
            Faça <a href="../index.php" class="alert-link">login</a> para adicionar produtos ao carrinho.
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="">
                <form action="add_carrinho.php" method="POST">
                    <input type="hidden" name="product_id" value="personalizado">
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_uri, ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="tipo" class="custom-label">Tipo de Produto</label>
                            <select class="form-select custom-input" id="tipo" name="tipo" required>
                                <option value="">Selecione...</option>
                                <option value="doce">Doce</option>
                                <option value="salgado">Salgado</option>
                                <option value="bolo">Bolo</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="quantity" class="custom-label">Quantidade</label>
                            <input type="number" class="form-control custom-input" id="quantity" name="quantity" min="1" value="1" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="tema" class="custom-label">Tema</label>
                        <input type="text" class="form-control custom-input" id="tema" name="tema" placeholder="Ex: Aniversário, Casamento, Tema infantil" required>
                    </div>

                    <div class="mb-4">
                        <label for="sabor" class="custom-label">Sabor Principal</label>
                        <input type="text" class="form-control custom-input" id="sabor" name="sabor" placeholder="Ex: Chocolate, Baunilha, Frutas vermelhas" required>
                    </div>

                    <div class="mb-4">
                        <label for="detalhes" class="custom-label">Detalhes Especiais</label>
                        <textarea class="form-control custom-input" id="detalhes" name="detalhes" rows="4" placeholder="Descreva cores, decorações, restrições alimentares, etc."></textarea>
                    </div>

                    <button type="submit" class="btn btn-custom-submit" <?php echo !$usuario_logado ? 'disabled' : ''; ?>
                        <?php echo html_entity_decode('Adicionar ao Carrinho'); ?>>
                        <i class="fas fa-shopping-cart me-2"></i>
                        Adicionar ao Carrinho
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>