<?php
if (!isset($_SESSION)) session_start();
require_once 'config.php';
require_once ABSPATH . 'inc/database.php';

$usuario_logado = !empty($_SESSION['logado']) && $_SESSION['logado'] === true;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalizados - Pedacinho de Amor</title>
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
                <li><a href="personalizados.php" class="active">Personalizados</a></li>
                <li><a href="carrinho.php"><i class="fas fa-shopping-cart"></i> Carrinho (<?php echo array_sum($_SESSION['cart'] ?? []); ?>)</a></li>
                <?php if ($usuario_logado): ?>
                    <li><a href="inc/logout.php">Sair</a></li>
                <?php else: ?>
                    <li><a class="btn btn-primary text-white" href="index.php">Login</a></li>
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
                    <p>Nossos produtos buscam entregar qualidade e aconchego, cada detalhe importa em nossa produção: um recheio cremoso, a textura macia de nossas tortas e o cuidado na decoração de cada sobremesa.</p>
                    <p>Produzimos em pequenas quantidades para garantir qualidade e sabor autêntico, mantendo sempre o nosso toque caseiro.</p>
                </div>
                <div class="work-image">
                    <img src="imagens/doce3.webp" alt="Como trabalhamos Pedacinho de Amor">
                </div>
            </div>
        </section>

        <div class="container my-5">
            <h2 class="text-center mb-4">Produtos Personalizados</h2>
            <p class="text-center mb-5">Crie seu produto único! Escolha o tipo, tema, sabor e detalhes especiais.</p>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-body">
                            <form id="personalizado-form" action="processar_personalizado.php" method="POST">
                                <div class="mb-3">
                                    <label for="tipo" class="form-label">Tipo de Produto</label>
                                    <select class="form-select" id="tipo" name="tipo" required>
                                        <option value="">Selecione...</option>
                                        <option value="doce">Doce</option>
                                        <option value="salgado">Salgado</option>
                                        <option value="bolo">Bolo</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="tema" class="form-label">Tema</label>
                                    <input type="text" class="form-control" id="tema" name="tema" placeholder="Ex: Aniversário, Casamento, Tema infantil" required>
                                </div>
                                <div class="mb-3">
                                    <label for="sabor" class="form-label">Sabor Principal</label>
                                    <input type="text" class="form-control" id="sabor" name="sabor" placeholder="Ex: Chocolate, Baunilha, Frutas vermelhas" required>
                                </div>
                                <div class="mb-3">
                                    <label for="quantidade" class="form-label">Quantidade</label>
                                    <input type="number" class="form-control" id="quantidade" name="quantidade" min="1" value="1" required>
                                </div>
                                <div class="mb-3">
                                    <label for="detalhes" class="form-label">Detalhes Especiais</label>
                                    <textarea class="form-control" id="detalhes" name="detalhes" rows="4" placeholder="Descreva cores, decorações, restrições alimentares, etc."></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">Solicitar Orçamento</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="js/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>