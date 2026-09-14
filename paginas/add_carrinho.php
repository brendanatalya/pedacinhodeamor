<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once '../config.php';
require_once ABSPATH . 'inc/database.php';

$isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

function responder_carrinho($sucesso, $mensagem, $redirect, $isAjax)
{
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['sucesso' => $sucesso, 'mensagem' => $mensagem]);
        exit;
    }

    $_SESSION['cart_message'] = $mensagem;
    header('Location: ' . $redirect);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder_carrinho(false, 'Método não permitido.', BASEURL . 'paginas/carrinho.php', $isAjax);
}

if (empty($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    responder_carrinho(false, 'Faça login para adicionar produtos ao carrinho.', BASEURL . 'index.php', $isAjax);
}

$redirect = $_POST['redirect'] ?? (BASEURL . 'paginas/carrinho.php');
if (!is_string($redirect) || strpos($redirect, BASEURL) !== 0) {
    $redirect = BASEURL . 'paginas/carrinho.php';
}

// Produtos personalizados não possuem um ID do banco: guardamos as escolhas na sessão.
if (($_POST['product_id'] ?? '') === 'personalizado') {
    $tipo = $_POST['tipo'] ?? '';
    if (!in_array($tipo, ['bolo', 'doce', 'salgado'], true)) {
        responder_carrinho(false, 'Tipo de produto personalizado inválido.', $redirect, $isAjax);
    }

    $tipoSalgado = trim((string) ($_POST['tipo_salgado'] ?? ''));
    $recheio = trim((string) ($_POST['recheio'] ?? ''));
    $sabor = trim((string) ($_POST['sabor'] ?? ''));

    if ($tipo === 'salgado') {
        $sabor = trim($tipoSalgado . ($recheio !== '' ? ' com ' . $recheio : ''));
    }

    $item = [
        'tipo' => $tipo,
        'tema' => trim((string) ($_POST['tema'] ?? '')),
        'sabor' => $sabor,
        'tipo_salgado' => $tipoSalgado,
        'recheio' => $recheio,
        'tamanho' => trim((string) ($_POST['tamanho'] ?? '')),
        'cor' => trim((string) ($_POST['cor'] ?? '')),
        'restricoes' => isset($_POST['restricoes']) && is_array($_POST['restricoes']) ? $_POST['restricoes'] : [],
        'data_desejada' => trim((string) ($_POST['data_desejada'] ?? '')),
        'detalhes' => trim((string) ($_POST['detalhes'] ?? '')),
        'quantity' => max(1, (int) ($_POST['quantity'] ?? $_POST['quantidade'] ?? 1)),
        'andares' => (int) ($_POST['andares'] ?? 0),
        'pessoas' => (int) ($_POST['pessoas'] ?? 0),
        'cobertura' => trim((string) ($_POST['cobertura'] ?? '')),
        'recheios' => trim((string) ($_POST['recheios'] ?? '')),
        'camadas_sabor' => [],
    ];

    foreach ($_POST as $campo => $valor) {
        if (preg_match('/^camada_\d+$/', $campo) && is_string($valor) && trim($valor) !== '') {
            $item['camadas_sabor'][] = trim($valor);
        }
    }

    if (isset($_FILES['imagem_referencia']) && $_FILES['imagem_referencia']['error'] !== UPLOAD_ERR_NO_FILE) {
        $imagem = $_FILES['imagem_referencia'];
        $tiposPermitidos = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if ($imagem['error'] !== UPLOAD_ERR_OK || $imagem['size'] > 5 * 1024 * 1024 || !is_uploaded_file($imagem['tmp_name'])) {
            responder_carrinho(false, 'Não foi possível enviar a imagem. Use um arquivo de até 5 MB.', $redirect, $isAjax);
        }

        $infoImagem = @getimagesize($imagem['tmp_name']);
        $mime = $infoImagem['mime'] ?? '';
        if (!isset($tiposPermitidos[$mime])) {
            responder_carrinho(false, 'A imagem deve estar no formato JPG, PNG ou WEBP.', $redirect, $isAjax);
        }

        $pastaImagens = ABSPATH . 'imagens/uploads/personalizados/';
        if (!is_dir($pastaImagens) && !mkdir($pastaImagens, 0755, true)) {
            responder_carrinho(false, 'Não foi possível preparar o envio da imagem.', $redirect, $isAjax);
        }

        $nomeImagem = 'personalizado_' . bin2hex(random_bytes(12)) . '.' . $tiposPermitidos[$mime];
        if (!move_uploaded_file($imagem['tmp_name'], $pastaImagens . $nomeImagem)) {
            responder_carrinho(false, 'Não foi possível salvar a imagem enviada.', $redirect, $isAjax);
        }

        $item['imagem_path'] = 'imagens/uploads/personalizados/' . $nomeImagem;
    }

    $_SESSION['cart_personalizado'] = $_SESSION['cart_personalizado'] ?? [];
    $_SESSION['cart_personalizado'][] = $item;
    responder_carrinho(true, 'Produto personalizado adicionado ao carrinho.', $redirect, $isAjax);
}

$productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
$quantity = max(1, (int) ($_POST['quantity'] ?? 1));
$produto = $productId ? find_product($productId) : null;

if (!$produto) {
    responder_carrinho(false, 'Produto não encontrado.', $redirect, $isAjax);
}

if (empty($produto['disponivel'])) {
    responder_carrinho(false, 'Este produto está indisponível.', $redirect, $isAjax);
}

$_SESSION['cart'] = $_SESSION['cart'] ?? [];
$_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $quantity;
responder_carrinho(true, 'Produto adicionado ao carrinho.', $redirect, $isAjax);
