<?php
if (!isset($_SESSION)) session_start();
require_once '../config.php';
require_once ABSPATH . 'inc/database.php';

$usuario_logado = !empty($_SESSION['logado']) && $_SESSION['logado'] === true;

$itens_normais = (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) ? array_sum($_SESSION['cart']) : 0;
$itens_pers    = (isset($_SESSION['cart_personalizado']) && is_array($_SESSION['cart_personalizado'])) ? count($_SESSION['cart_personalizado']) : 0;
$itens_no_carrinho = $itens_normais + $itens_pers;

$redirect_uri = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
$add_carrinho_url = './add_carrinho.php';

// ──────────────────────────────────────────────────────────────
// DADOS DE OPÇÕES POR CATEGORIA
// ──────────────────────────────────────────────────────────────

// BOLO
$opcoes_bolo = [
    'sabores' => [
        'chocolate' => '🍫 Chocolate',
        'baunilha' => '✨ Baunilha',
        'red_velvet' => '❤️ Red Velvet',
        'cenoura' => '🥕 Cenoura',
        'limao' => '🍋 Limão',
        'morango' => '🍓 Morango',
        'cafe' => '☕ Café',
        'banana' => '🍌 Banana',
        'abacaxi' => '🍍 Abacaxi'
    ],
    'coberturas' => [
        'chantilly' => 'Chantilly',
        'pasta_americana' => 'Pasta Americana',
        'ganache' => 'Ganache',
        'naked_cake' => 'Naked Cake',
        'espelho' => 'Espelho',
        'cobertura_fruta' => 'Cobertura com Frutas'
    ]
];

// DOCE
$opcoes_doce = [
    'sabores' => [
        'brigadeiro' => '🍫 Brigadeiro',
        'beijinho' => 'Beijinho',
        'cajuzinho' => 'Cajuzinho',
        'broinhas' => 'Broinhas',
        'olho_de_sogra' => 'Olho de Sogra',
        'doce_leite' => '🍯 Doce de Leite',
        'morango_champanhe' => '🍓 Morango com Champanhe',
        'brownie' => '🍫 Brownie',
        'torta' => '🎂 Torta',
        'bombom' => '🎁 Bombom',
        'trufa' => '✨ Trufa',
        'fudge' => 'Fudge'
    ],
    'tipos_presentacao' => [
        'individual' => 'Individual (Unidade)',
        'pote' => 'Pote (Varios)',
        'bandeja' => 'Bandeja',
        'caixa' => 'Caixa Especial'
    ]
];

// SALGADO
$opcoes_salgado = [
    'tipos' => [
        'coxinha' => '🍗 Coxinha',
        'esfiha' => '🥟 Esfiha',
        'empada' => '🥧 Empada',
        'bolinha_queijo' => '🧀 Bolinha de Queijo',
        'enroladinho' => '🌮 Enroladinho',
        'quiche' => '🍳 Quiche',
        'pastel' => '📦 Pastel',
        'acaraje' => 'Acarajé',
        'churro_salgado' => '✨ Churro Salgado',
        'cone_salgado' => '🌽 Cone Salgado'
    ],
    'recheios' => [
        'frango_simples' => 'Frango Simples',
        'frango_catupiry' => 'Frango com Catupiry',
        'carne' => 'Carne Moída',
        'palmito' => 'Palmito',
        'queijo' => 'Queijo',
        'espinafre' => 'Espinafre',
        'mix' => 'Mix de Recheios'
    ]
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalizados - Pedacinho de Amor</title>
    <link rel="icon" type="image/x-icon" href="../imagens/icon.png">
    <link rel="stylesheet" href="../css_pda/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/style_pda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .cards-container {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }

        .card-selector {
            width: 150px;
            padding: 20px;
            border: 3px solid #ddd;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .card-selector:hover:not(.disabled) {
            border-color: #f5a623;
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(245, 166, 35, 0.2);
        }

        .card-selector.active {
            border-color: #f5a623;
            background: #fff8f0;
            box-shadow: 0 5px 20px rgba(245, 166, 35, 0.3);
        }

        .card-selector.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f5f5f5;
            pointer-events: none;
        }

        .card-selector.completed {
            border-color: #2e6930;
            background: #eafbea;
            cursor: not-allowed;
            pointer-events: none;
        }

        .card-selector i {
            font-size: 40px;
            display: block;
            margin-bottom: 10px;
        }

        .card-selector.completed i::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            font-size: 16px;
            color: #2e6930;
        }

        .card-selector span {
            display: block;
            font-weight: 600;
            color: #333;
        }

        .step-label {
            display: block;
            font-size: 12px;
            color: #999;
            margin-top: 4px;
        }

        .skip-item-box {
            background: #fff8f0;
            border: 1px dashed #f5a623;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }

        .skip-item-box label {
            font-weight: 600;
            color: #7a2f2f;
            margin-left: 6px;
        }

        .form-fields.skipped {
            opacity: 0.35;
            pointer-events: none;
            filter: grayscale(40%);
        }

        .form-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            min-height: 500px;
            display: none;
        }

        .form-container.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .form-container h3 {
            color: #7a2f2f;
            margin-bottom: 25px;
            font-weight: 600;
            border-bottom: 2px solid #f5a623;
            padding-bottom: 12px;
        }

        .custom-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .custom-input {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 10px;
            transition: border-color 0.3s ease;
        }

        .custom-input:focus {
            border-color: #f5a623;
            box-shadow: 0 0 0 0.2rem rgba(245, 166, 35, 0.25);
        }

        .camadas-container {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            border-left: 4px solid #f5a623;
        }

        .camada-input {
            margin-bottom: 15px;
            padding: 10px;
            background: white;
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        .camada-input label {
            font-weight: 600;
            color: #7a2f2f;
            margin-bottom: 8px;
        }

        .navigation-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: space-between;
        }

        .btn-nav {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
        }

        .btn-voltar {
            background: #e0e0e0;
            color: #333;
        }

        .btn-voltar:hover:not(:disabled) {
            background: #d0d0d0;
        }

        .btn-voltar:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-proximo {
            background: #f5a623;
            color: white;
            flex: 1;
        }

        .btn-proximo:hover:not(:disabled) {
            background: #e09400;
        }

        .btn-concluir {
            background: #2e6930;
            color: white;
            flex: 1;
        }

        .btn-concluir:hover {
            background: #246620;
        }

        .restricoes-group {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 15px;
        }

        .modal-sucesso {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .modal-sucesso.active {
            display: flex;
        }

        .modal-sucesso-content {
            background: white;
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            max-width: 400px;
        }

        .modal-sucesso-content i {
            font-size: 60px;
            color: #2e6930;
            margin-bottom: 20px;
        }

        .modal-sucesso-content h3 {
            color: #2e6930;
            margin-bottom: 15px;
        }

        .modal-sucesso-content ul {
            text-align: left;
            list-style: none;
            padding: 0;
            margin: 0 0 20px;
        }

        .modal-sucesso-content ul li {
            padding: 6px 0;
            color: #333;
            border-bottom: 1px solid #eee;
        }

        .btn-modal-close {
            background: #f5a623;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-modal-close:hover {
            background: #e09400;
        }

        .toast-container-custom {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast-custom {
            background: #2e6930;
            color: white;
            padding: 14px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideInToast 0.3s ease;
            min-width: 260px;
        }

        .toast-custom.erro {
            background: #b23a3a;
        }

        @keyframes slideInToast {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes fadeOutToast {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        .info-card {
            background: #f0f8ff;
            border-left: 4px solid #0066cc;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: #333;
        }

        .info-card i {
            color: #0066cc;
            margin-right: 6px;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
                min-height: 400px;
            }

            .cards-container {
                gap: 10px;
                margin-bottom: 30px;
            }

            .card-selector {
                width: 100px;
                padding: 15px;
            }

            .card-selector i {
                font-size: 30px;
                margin-bottom: 6px;
            }

            .card-selector span {
                font-size: 0.9rem;
            }

            .step-label {
                font-size: 10px;
            }
        }
    </style>
</head>
<body>

    <?php include_once ABSPATH . 'inc/header.php'; ?>

    <main>
        <?php if (isset($_SESSION['cart_message'])): ?>
            <div class="alert alert-info text-center container mt-3">
                <?php echo htmlspecialchars($_SESSION['cart_message']); unset($_SESSION['cart_message']); ?>
            </div>
        <?php endif; ?>

        <section class="doces-hero" style="background-image:url('../imagens/doce3.webp');">
            <div class="doces-hero__overlay"></div>
            <div class="doces-hero__content">
                <h1>🎨 PERSONALIZADOS</h1>
                <p>Monte seu produto do jeito que você quiser!</p>
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
                <div class="col-md-10">

                    <!-- CARDS SELETORES -->
                    <div class="cards-container">
                        <div class="card-selector active" data-tipo="bolo">
                            <i class="fas fa-birthday-cake"></i>
                            <span>Bolo</span>
                            <span class="step-label">Etapa 1 de 3</span>
                        </div>
                        <div class="card-selector disabled" data-tipo="doce">
                            <i class="fas fa-candy-cane"></i>
                            <span>Doce</span>
                            <span class="step-label">Etapa 2 de 3</span>
                        </div>
                        <div class="card-selector disabled" data-tipo="salgado">
                            <i class="fas fa-drumstick-bite"></i>
                            <span>Salgado</span>
                            <span class="step-label">Etapa 3 de 3</span>
                        </div>
                    </div>

                    <!-- FORMULÁRIOS -->
                    <!-- ══════════════════════════════════════════════════════════════════════════════════ -->
                    <!-- BOLO -->
                    <!-- ══════════════════════════════════════════════════════════════════════════════════ -->
                    <div class="form-container active" id="form-bolo">
                        <h3>🎂 Personalizar Bolo</h3>
                        
                        <div class="info-card">
                            <i class="fas fa-info-circle"></i>
                            Os bolos são confeccionados sob encomenda. Preço será orçado conforme o tamanho e detalhes!
                        </div>

                        <form id="form-bolo-submit">
                            <input type="hidden" name="tipo" value="bolo">
                            <input type="hidden" name="product_id" value="personalizado">
                            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_uri, ENT_QUOTES, 'UTF-8'); ?>">

                            <div class="skip-item-box form-check">
                                <input class="form-check-input" type="checkbox" id="skip-bolo" onchange="toggleSkip('bolo', this)">
                                <label class="form-check-label" for="skip-bolo">Não desejo este item</label>
                            </div>

                            <div class="form-fields" id="fields-bolo">
                                <!-- INFORMAÇÕES BÁSICAS -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Tema / Ocasião *</label>
                                        <input type="text" class="form-control custom-input" name="tema" placeholder="Ex: Aniversário 1 ano, Casamento" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Sabor da Massa *</label>
                                        <select class="form-select custom-input" name="sabor" required>
                                            <option value="">Selecione um sabor...</option>
                                            <?php foreach ($opcoes_bolo['sabores'] as $valor => $label): ?>
                                                <option value="<?php echo $valor; ?>"><?php echo $label; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- TAMANHO E COBERTURA -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Número de Andares *</label>
                                        <select class="form-select custom-input" name="andares" id="andares-bolo" onchange="gerarCamadas('bolo')" required>
                                            <option value="">Selecione...</option>
                                            <option value="1">1 andar</option>
                                            <option value="2">2 andares</option>
                                            <option value="3">3 andares</option>
                                            <option value="4">4 andares</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Número de Pessoas *</label>
                                        <select class="form-select custom-input" name="pessoas" required>
                                            <option value="">Selecione...</option>
                                            <option value="10">Até 10 pessoas</option>
                                            <option value="20">Até 20 pessoas</option>
                                            <option value="30">Até 30 pessoas</option>
                                            <option value="50">Até 50 pessoas</option>
                                            <option value="80">Até 80 pessoas</option>
                                            <option value="100">Até 100 pessoas</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- COBERTURA E RECHEIO -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Cobertura</label>
                                        <select class="form-select custom-input" name="cobertura">
                                            <option value="">Selecione...</option>
                                            <?php foreach ($opcoes_bolo['coberturas'] as $valor => $label): ?>
                                                <option value="<?php echo $valor; ?>"><?php echo $label; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Recheios</label>
                                        <input type="text" class="form-control custom-input" name="recheios" placeholder="Ex: Ninho com morango, Brigadeiro">
                                    </div>
                                </div>

                                <!-- CAMADAS DINÂMICAS -->
                                <div id="camadas-bolo-container"></div>

                                <!-- RESTRIÇÕES ALIMENTARES -->
                                <div class="mb-3">
                                    <label class="custom-label d-block">Restrições Alimentares</label>
                                    <div class="restricoes-group">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="restricoes[]" value="sem_gluten">
                                            <label class="form-check-label">Sem glúten</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="restricoes[]" value="sem_lactose">
                                            <label class="form-check-label">Sem lactose</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="restricoes[]" value="vegano">
                                            <label class="form-check-label">Vegano</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- DATA E IMAGEM -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Data Desejada *</label>
                                        <input type="date" class="form-control custom-input" name="data_desejada" min="<?php echo date('Y-m-d', strtotime('+3 days')); ?>" required>
                                        <small class="text-muted">Prazo mínimo: 3 dias</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Imagem de Referência</label>
                                        <input type="file" class="form-control custom-input" name="imagem_referencia" accept="image/jpeg,image/png,image/webp">
                                    </div>
                                </div>

                                <!-- DETALHES ESPECIAIS -->
                                <div class="mb-3">
                                    <label class="custom-label">Detalhes Especiais</label>
                                    <textarea class="form-control custom-input" name="detalhes" rows="3" placeholder="Mensagem no bolo, cores, decorações especiais..."></textarea>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- ══════════════════════════════════════════════════════════════════════════════════ -->
                    <!-- DOCE -->
                    <!-- ══════════════════════════════════════════════════════════════════════════════════ -->
                    <div class="form-container" id="form-doce">
                        <h3>🍬 Personalizar Doce</h3>
                        
                        <div class="info-card">
                            <i class="fas fa-info-circle"></i>
                            Doces são confeccionados sob encomenda. Pedido mínimo pode variar conforme o tipo!
                        </div>

                        <form id="form-doce-submit">
                            <input type="hidden" name="tipo" value="doce">
                            <input type="hidden" name="product_id" value="personalizado">
                            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_uri, ENT_QUOTES, 'UTF-8'); ?>">

                            <div class="skip-item-box form-check">
                                <input class="form-check-input" type="checkbox" id="skip-doce" onchange="toggleSkip('doce', this)">
                                <label class="form-check-label" for="skip-doce">Não desejo este item</label>
                            </div>

                            <div class="form-fields" id="fields-doce">
                                <!-- TIPO DE DOCE -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Tipo de Doce *</label>
                                        <select class="form-select custom-input" name="sabor" required>
                                            <option value="">Selecione um doce...</option>
                                            <?php foreach ($opcoes_doce['sabores'] as $valor => $label): ?>
                                                <option value="<?php echo $valor; ?>"><?php echo $label; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Tema / Ocasião</label>
                                        <input type="text" class="form-control custom-input" name="tema" placeholder="Ex: Festa junina, Natal">
                                    </div>
                                </div>

                                <!-- QUANTIDADE E APRESENTAÇÃO -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Quantidade *</label>
                                        <input type="number" class="form-control custom-input" name="quantidade" min="1" max="999" value="1" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Tipo de Apresentação</label>
                                        <select class="form-select custom-input" name="tamanho">
                                            <option value="">Selecione...</option>
                                            <?php foreach ($opcoes_doce['tipos_presentacao'] as $valor => $label): ?>
                                                <option value="<?php echo $valor; ?>"><?php echo $label; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- CAMADAS (se aplicável) -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Número de Camadas (se aplicável)</label>
                                        <select class="form-select custom-input" name="camadas" id="camadas-doce" onchange="gerarCamadas('doce')">
                                            <option value="">Não tem camadas</option>
                                            <option value="1">1 camada</option>
                                            <option value="2">2 camadas</option>
                                            <option value="3">3 camadas</option>
                                            <option value="4">4 camadas</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- CAMADAS DINÂMICAS -->
                                <div id="camadas-doce-container"></div>

                                <!-- RESTRIÇÕES ALIMENTARES -->
                                <div class="mb-3">
                                    <label class="custom-label d-block">Restrições Alimentares</label>
                                    <div class="restricoes-group">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="restricoes[]" value="sem_gluten">
                                            <label class="form-check-label">Sem glúten</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="restricoes[]" value="sem_lactose">
                                            <label class="form-check-label">Sem lactose</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="restricoes[]" value="vegano">
                                            <label class="form-check-label">Vegano</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- DATA E IMAGEM -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Data Desejada *</label>
                                        <input type="date" class="form-control custom-input" name="data_desejada" min="<?php echo date('Y-m-d', strtotime('+2 days')); ?>" required>
                                        <small class="text-muted">Prazo mínimo: 2 dias</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Imagem de Referência</label>
                                        <input type="file" class="form-control custom-input" name="imagem_referencia" accept="image/jpeg,image/png,image/webp">
                                    </div>
                                </div>

                                <!-- DETALHES ESPECIAIS -->
                                <div class="mb-3">
                                    <label class="custom-label">Detalhes Especiais</label>
                                    <textarea class="form-control custom-input" name="detalhes" rows="3" placeholder="Decorações, embalagem, mensagem..."></textarea>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- ══════════════════════════════════════════════════════════════════════════════════ -->
                    <!-- SALGADO -->
                    <!-- ══════════════════════════════════════════════════════════════════════════════════ -->
                    <div class="form-container" id="form-salgado">
                        <h3>🥐 Personalizar Salgado</h3>
                        
                        <div class="info-card">
                            <i class="fas fa-info-circle"></i>
                            Salgados são feitos sob encomenda. Quantidade mínima pode variar conforme o tipo!
                        </div>

                        <form id="form-salgado-submit">
                            <input type="hidden" name="tipo" value="salgado">
                            <input type="hidden" name="product_id" value="personalizado">
                            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_uri, ENT_QUOTES, 'UTF-8'); ?>">

                            <div class="skip-item-box form-check">
                                <input class="form-check-input" type="checkbox" id="skip-salgado" onchange="toggleSkip('salgado', this)">
                                <label class="form-check-label" for="skip-salgado">Não desejo este item</label>
                            </div>

                            <div class="form-fields" id="fields-salgado">
                                <!-- TIPO E RECHEIO -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Tipo de Salgado *</label>
                                        <select class="form-select custom-input" name="tipo_salgado" required>
                                            <option value="">Selecione um salgado...</option>
                                            <?php foreach ($opcoes_salgado['tipos'] as $valor => $label): ?>
                                                <option value="<?php echo $valor; ?>"><?php echo $label; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Recheio *</label>
                                        <select class="form-select custom-input" name="recheio" required>
                                            <option value="">Selecione o recheio...</option>
                                            <?php foreach ($opcoes_salgado['recheios'] as $valor => $label): ?>
                                                <option value="<?php echo $valor; ?>"><?php echo $label; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- QUANTIDADE E TAMANHO -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Quantidade *</label>
                                        <input type="number" class="form-control custom-input" name="quantidade" min="1" max="999" value="1" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Tamanho</label>
                                        <select class="form-select custom-input" name="tamanho">
                                            <option value="">Selecione...</option>
                                            <option value="mini">Mini (coquetel)</option>
                                            <option value="medio">Médio</option>
                                            <option value="grande">Grande</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- RESTRIÇÕES ALIMENTARES -->
                                <div class="mb-3">
                                    <label class="custom-label d-block">Restrições Alimentares</label>
                                    <div class="restricoes-group">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="restricoes[]" value="sem_gluten">
                                            <label class="form-check-label">Sem glúten</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="restricoes[]" value="sem_lactose">
                                            <label class="form-check-label">Sem lactose</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="restricoes[]" value="vegano">
                                            <label class="form-check-label">Vegano</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- DATA E IMAGEM -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Data Desejada *</label>
                                        <input type="date" class="form-control custom-input" name="data_desejada" min="<?php echo date('Y-m-d', strtotime('+2 days')); ?>" required>
                                        <small class="text-muted">Prazo mínimo: 2 dias</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Imagem de Referência</label>
                                        <input type="file" class="form-control custom-input" name="imagem_referencia" accept="image/jpeg,image/png,image/webp">
                                    </div>
                                </div>

                                <!-- DETALHES ESPECIAIS -->
                                <div class="mb-3">
                                    <label class="custom-label">Detalhes Especiais</label>
                                    <textarea class="form-control custom-input" name="detalhes" rows="3" placeholder="Eventos, quantidade por tipo, embalagem..."></textarea>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- BOTÕES DE NAVEGAÇÃO -->
                    <div class="navigation-buttons">
                        <button class="btn-nav btn-voltar" id="btn-voltar" disabled onclick="voltarTipo()">
                            <i class="fas fa-arrow-left me-2"></i> Voltar
                        </button>
                        <button class="btn-nav btn-proximo" id="btn-proximo" onclick="proximoTipo()">
                            Próximo <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- MODAL DE SUCESSO -->
    <div class="modal-sucesso" id="modalSucesso">
        <div class="modal-sucesso-content">
            <i class="fas fa-check-circle"></i>
            <h3 id="modalSucessoTitulo">Produto Adicionado!</h3>
            <p>Os itens abaixo foram adicionados ao carrinho:</p>
            <ul id="modalSucessoLista"></ul>
            <button class="btn-modal-close" onclick="fecharModalSucesso()">Ir para o Carrinho</button>
        </div>
    </div>

    <!-- CONTAINER DE TOASTS -->
    <div class="toast-container-custom" id="toastContainer"></div>

    <?php include '../inc/modal.php'; 
        include(FOOTER_TEMPLATE);
    ?>

    <script>
        // Estados dos itens
        const tipos = ['bolo', 'doce', 'salgado'];
        const nomesTipo = { bolo: 'Bolo', doce: 'Doce', salgado: 'Salgado' };
        let tipoAtual = 'bolo';
        let itemsState = {
            bolo: { skip: false },
            doce: { skip: false },
            salgado: { skip: false }
        };

        // ──────────────────────────────────────────────────────────────
        // NAVEGAÇÃO ENTRE TIPOS
        // ──────────────────────────────────────────────────────────────
        function irParaEtapa(index) {
            if (index < 0 || index >= tipos.length) return;

            const novoTipo = tipos[index];
            const formAtualContainer = document.getElementById(`form-${tipoAtual}`);
            const novoFormContainer = document.getElementById(`form-${novoTipo}`);

            if (formAtualContainer) formAtualContainer.classList.remove('active');
            if (novoFormContainer) novoFormContainer.classList.add('active');

            tipoAtual = novoTipo;
            atualizarCards(index);
            atualizarBotoes();
        }

        // ──────────────────────────────────────────────────────────────
        // ATUALIZAR CARDS VISUAIS
        // ──────────────────────────────────────────────────────────────
        function atualizarCards(indexAtual) {
            document.querySelectorAll('.card-selector').forEach((card, idx) => {
                card.classList.remove('active', 'completed', 'disabled', 'skipped');

                if (idx === indexAtual) {
                    card.classList.add('active');
                } else if (idx < indexAtual) {
                    const tipo = tipos[idx];
                    card.classList.add(itemsState[tipo].skip ? 'skipped' : 'completed');
                } else {
                    card.classList.add('disabled');
                }
            });
        }

        // ──────────────────────────────────────────────────────────────
        // TOGGLE "NÃO DESEJO ESTE ITEM"
        // ──────────────────────────────────────────────────────────────
        function toggleSkip(tipo, checkbox) {
            itemsState[tipo].skip = checkbox.checked;
            const fieldsDiv = document.getElementById(`fields-${tipo}`);
            if (fieldsDiv) {
                fieldsDiv.classList.toggle('skipped', checkbox.checked);
            }

            const indexAtual = tipos.indexOf(tipoAtual);
            atualizarCards(indexAtual);
        }

        // ──────────────────────────────────────────────────────────────
        // NAVEGAR
        // ──────────────────────────────────────────────────────────────
        function proximoTipo() {
            const indexAtual = tipos.indexOf(tipoAtual);
            const form = document.getElementById(`form-${tipoAtual}-submit`);

            if (!form) {
                console.warn('Form não encontrado para tipo: ' + tipoAtual);
                return;
            }

            if (!itemsState[tipoAtual].skip && !form.checkValidity()) {
                form.reportValidity();
                return;
            }

            if (indexAtual < tipos.length - 1) {
                irParaEtapa(indexAtual + 1);
            } else {
                finalizarPedido();
            }
        }

        function voltarTipo() {
            const indexAtual = tipos.indexOf(tipoAtual);
            if (indexAtual > 0) {
                irParaEtapa(indexAtual - 1);
            }
        }

        function atualizarBotoes() {
            const indexAtual = tipos.indexOf(tipoAtual);
            const btnVoltar = document.getElementById('btn-voltar');
            const btnProximo = document.getElementById('btn-proximo');

            if (!btnVoltar || !btnProximo) {
                console.warn('Botões de navegação não encontrados');
                return;
            }

            btnVoltar.disabled = indexAtual === 0;

            if (indexAtual === tipos.length - 1) {
                btnProximo.innerHTML = '<i class="fas fa-check me-2"></i> Concluído';
                btnProximo.classList.remove('btn-proximo');
                btnProximo.classList.add('btn-concluir');
            } else {
                btnProximo.innerHTML = 'Próximo <i class="fas fa-arrow-right ms-2"></i>';
                btnProximo.classList.remove('btn-concluir');
                btnProximo.classList.add('btn-proximo');
            }
        }

        // ──────────────────────────────────────────────────────────────
        // GERAR CAMADAS DINÂMICAS
        // ──────────────────────────────────────────────────────────────
        function gerarCamadas(tipo) {
            const selectCamadas = tipo === 'bolo' 
                ? document.getElementById('andares-bolo') 
                : document.getElementById('camadas-doce');
            const container = document.getElementById(`camadas-${tipo}-container`);

            if (!selectCamadas || !container) {
                console.warn('Elemento não encontrado para gerarCamadas(' + tipo + ')');
                return;
            }

            const numCamadas = parseInt(selectCamadas.value);
            container.innerHTML = '';

            if (numCamadas > 0) {
                const title = document.createElement('div');
                title.innerHTML = '<label class="custom-label mt-3 d-block">Sabor de Cada Camada</label>';
                container.appendChild(title);

                for (let i = 1; i <= numCamadas; i++) {
                    const div = document.createElement('div');
                    div.className = 'camada-input';
                    div.innerHTML = `
                        <label>Camada ${i}</label>
                        <input type="text" class="form-control" name="camada_${i}" placeholder="Sabor da camada ${i}">
                    `;
                    container.appendChild(div);
                }
            }
        }

        // ──────────────────────────────────────────────────────────────
        // TOAST
        // ──────────────────────────────────────────────────────────────
        function mostrarToast(mensagem, tipoToast = 'sucesso') {
            const container = document.getElementById('toastContainer');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'toast-custom' + (tipoToast === 'erro' ? ' erro' : '');
            toast.innerHTML = `<i class="fas ${tipoToast === 'erro' ? 'fa-circle-exclamation' : 'fa-circle-check'}"></i><span>${mensagem}</span>`;
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'fadeOutToast 0.4s ease forwards';
                setTimeout(() => toast.remove(), 400);
            }, 3500);
        }

        // ──────────────────────────────────────────────────────────────
        // FINALIZAR PEDIDO
        // ──────────────────────────────────────────────────────────────
        async function finalizarPedido() {
            if (!<?php echo json_encode($usuario_logado); ?>) {
                alert('Por favor, faça login primeiro!');
                window.location.href = '<?php echo BASEURL; ?>index.php';
                return;
            }

            const tiposParaEnviar = tipos.filter(t => !itemsState[t].skip);

            if (tiposParaEnviar.length === 0) {
                alert('Marque "Não desejo este item" apenas nos itens que não quer, ou preencha ao menos um item para continuar.');
                return;
            }

            const btnProximo = document.getElementById('btn-proximo');
            const btnVoltar = document.getElementById('btn-voltar');

            if (!btnProximo || !btnVoltar) {
                console.warn('Botões não encontrados');
                alert('Erro ao enviar pedido. Por favor, recarregue a página.');
                return;
            }

            btnProximo.disabled = true;
            btnVoltar.disabled = true;
            btnProximo.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Enviando...';

            const itensAdicionados = [];
            const itensComErro = [];

            for (const tipo of tiposParaEnviar) {
                const form = document.getElementById(`form-${tipo}-submit`);
                const formData = new FormData(form);
                try {
                    const response = await fetch('<?php echo $add_carrinho_url; ?>', {
                        method: 'POST',
                        body: formData
                    });

                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('Resposta inválida do servidor');
                    }

                    const data = await response.json();

                    if (data.sucesso) {
                        itensAdicionados.push(nomesTipo[tipo]);
                        mostrarToast(nomesTipo[tipo] + ' adicionado ao carrinho!');
                    } else {
                        itensComErro.push(nomesTipo[tipo] + ': ' + data.mensagem);
                        mostrarToast(nomesTipo[tipo] + ': ' + data.mensagem, 'erro');
                    }
                } catch (erro) {
                    console.error('Erro:', erro);
                    itensComErro.push(nomesTipo[tipo] + ': ' + erro.message);
                    mostrarToast(nomesTipo[tipo] + ': ' + erro.message, 'erro');
                }
            }

            btnProximo.disabled = false;
            btnVoltar.disabled = false;
            btnProximo.innerHTML = '<i class="fas fa-check me-2"></i> Concluído';

            if (itensAdicionados.length > 0) {
                mostrarModalSucesso(itensAdicionados, itensComErro);
            } else {
                alert('Não foi possível adicionar os itens ao carrinho:\n' + itensComErro.join('\n'));
            }
        }

        function mostrarModalSucesso(itensAdicionados, itensComErro) {
            const lista = document.getElementById('modalSucessoLista');
            if (!lista) {
                window.location.href = '<?php echo BASEURL; ?>index.php?page=carrinho';
                return;
            }

            lista.innerHTML = '';

            itensAdicionados.forEach(nome => {
                const li = document.createElement('li');
                li.innerHTML = `<i class="fas fa-check-circle"></i> ${nome}`;
                lista.appendChild(li);
            });

            itensComErro.forEach(msg => {
                const li = document.createElement('li');
                li.style.color = '#b23a3a';
                li.innerHTML = `<i class="fas fa-circle-exclamation" style="color:#b23a3a;"></i> ${msg}`;
                lista.appendChild(li);
            });

            const modalTitulo = document.getElementById('modalSucessoTitulo');
            if (modalTitulo) {
                modalTitulo.textContent =
                    itensComErro.length > 0 ? 'Alguns itens foram adicionados' : 'Itens adicionados ao carrinho!';
            }

            const modal = document.getElementById('modalSucesso');
            if (modal) {
                modal.classList.add('active');
            }
        }

        function fecharModalSucesso() {
            window.location.href = '<?php echo BASEURL; ?>index.php?page=carrinho';
        }

        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            atualizarCards(0);
            atualizarBotoes();
        });
    </script>

</body>
</html>