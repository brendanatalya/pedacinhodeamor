<?php
if (!isset($_SESSION)) session_start();
require_once '../config.php';
require_once ABSPATH . 'inc/database.php';

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASEURL . 'paginas/carrinho.php');
    exit;
}

// Usuário precisa estar logado
if (empty($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Location: ' . BASEURL . 'index.php');
    exit;
}

$action     = $_POST['action']     ?? '';
$product_id = $_POST['product_id'] ?? null;

// ─── REMOVER TODOS OS INDISPONÍVEIS ──────────────────────────────────────────
if ($action === 'remove_unavailable') {
    $cart = $_SESSION['cart'] ?? [];

    foreach ($cart as $pid => $qty) {
        $produto = find_product(intval($pid));
        if (!$produto || !$produto['disponivel']) {
            unset($_SESSION['cart'][$pid]);
        }
    }

    $_SESSION['cart_message'] = 'Itens indisponíveis removidos do carrinho.';
    header('Location: ' . BASEURL . 'paginas/carrinho.php');
    exit;
}

// ─── REMOVER PERSONALIZADO ────────────────────────────────────────────────────
if ($action === 'remove_personalizado') {
    $index = intval($_POST['index'] ?? -1);
    if (isset($_SESSION['cart_personalizado'][$index])) {
        array_splice($_SESSION['cart_personalizado'], $index, 1);
        $_SESSION['cart_message'] = 'Produto personalizado removido.';
    }
    header('Location: ' . BASEURL . 'paginas/carrinho.php');
    exit;
}

// ─── AÇÕES EM PRODUTO NORMAL ──────────────────────────────────────────────────
$product_id = intval($product_id);

if ($product_id <= 0) {
    header('Location: ' . BASEURL . 'paginas/carrinho.php');
    exit;
}

if ($action === 'remove') {
    unset($_SESSION['cart'][$product_id]);
    $_SESSION['cart_message'] = 'Produto removido do carrinho.';

} elseif ($action === 'update') {
    $quantity = intval($_POST['quantity'] ?? 0);

    if ($quantity <= 0) {
        // Quantidade zero ou negativa = remover
        unset($_SESSION['cart'][$product_id]);
        $_SESSION['cart_message'] = 'Produto removido do carrinho.';
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
        $_SESSION['cart_message'] = 'Quantidade atualizada.';
    }
}

header('Location: ' . BASEURL . 'paginas/carrinho.php');
exit;
?>