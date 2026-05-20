<?php
if (!isset($_SESSION)) session_start();
require_once '../config.php';
require_once ABSPATH . 'inc/database.php';

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASEURL . 'paginas/doces.php');
    exit;
}

// Usuário precisa estar logado
if (empty($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    $_SESSION['cart_message'] = 'Faça login para adicionar produtos ao carrinho.';
    $redirect = $_POST['redirect'] ?? BASEURL . 'paginas/doces.php';
    header('Location: ' . $redirect);
    exit;
}

$product_id = $_POST['product_id'] ?? null;
$quantity   = max(1, intval($_POST['quantity'] ?? 1));
$redirect   = $_POST['redirect'] ?? BASEURL . 'paginas/doces.php';

// PRODUTO PERSONALIZADO
if ($product_id === 'personalizado') {
    $tipo     = trim($_POST['tipo']     ?? '');
    $tema     = trim($_POST['tema']     ?? '');
    $sabor    = trim($_POST['sabor']    ?? '');
    $detalhes = trim($_POST['detalhes'] ?? '');

    // Validação básica
    if (empty($tipo) || empty($tema) || empty($sabor)) {
        $_SESSION['cart_message'] = 'Preencha todos os campos obrigatórios do produto personalizado.';
        header('Location: ' . $redirect);
        exit;
    }

    // Personalizados ficam em $_SESSION['cart_personalizado'] como lista
    // pois cada um pode ter configurações diferentes
    if (!isset($_SESSION['cart_personalizado'])) {
        $_SESSION['cart_personalizado'] = [];
    }

    $_SESSION['cart_personalizado'][] = [
        'tipo'      => htmlspecialchars($tipo),
        'tema'      => htmlspecialchars($tema),
        'sabor'     => htmlspecialchars($sabor),
        'detalhes'  => htmlspecialchars($detalhes),
        'quantity'  => $quantity,
        'added_at'  => date('Y-m-d H:i:s'),
    ];

    $_SESSION['cart_message'] = 'Produto personalizado adicionado ao    !';
    header('Location: ' . $redirect);
    exit;
}

//PRODUTO NORMAL (ID numérico)
$product_id = intval($product_id);

if ($product_id <= 0) {
    $_SESSION['cart_message'] = 'Produto inválido.';
    header('Location: ' . $redirect);
    exit;
}

// Verifica se o produto existe e está disponível
$produto = find_product($product_id);

if (!$produto) {
    $_SESSION['cart_message'] = 'Produto não encontrado.';
    header('Location: ' . $redirect);
    exit;
}

if (!$produto['disponivel']) {
    $_SESSION['cart_message'] = 'Este produto está indisponível no momento.';
    header('Location: ' . $redirect);
    exit;
}

// Inicializa carrinho se necessário
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Soma a quantidade se já estiver no carrinho
$current = isset($_SESSION['cart'][$product_id]) ? intval($_SESSION['cart'][$product_id]) : 0;
$_SESSION['cart'][$product_id] = $current + $quantity;

$_SESSION['cart_message'] = '"' . htmlspecialchars($produto['nome']) . '" adicionado ao carrinho!';
header('Location: ' . $redirect);
exit;
?>