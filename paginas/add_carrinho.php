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

// ─── PRODUTO PERSONALIZADO ────────────────────────────────────────────────────
if ($product_id === 'personalizado') {

    $tipo          = trim($_POST['tipo']          ?? '');
    $tema          = trim($_POST['tema']          ?? '');
    $sabor         = trim($_POST['sabor']         ?? '');
    $detalhes      = trim($_POST['detalhes']      ?? '');
    $tamanho       = trim($_POST['tamanho']       ?? '');
    $cor           = trim($_POST['cor']           ?? '');
    $data_desejada = trim($_POST['data_desejada'] ?? '');
    $restricoes    = $_POST['restricoes'] ?? [];

    if (empty($tipo)) {
        $_SESSION['cart_message'] = 'Selecione o tipo de produto personalizado.';
        header('Location: ' . $redirect);
        exit;
    }

    // Validação específica por tipo
    if ($tipo === 'doce') {
        if (empty($sabor)) {
            $_SESSION['cart_message'] = 'Por favor, informe o sabor do doce.';
            header('Location: ' . $redirect);
            exit;
        }
    } elseif ($tipo === 'salgado') {
        if (empty($sabor) || empty($tema)) {
            $_SESSION['cart_message'] = 'Por favor, selecione o tipo de salgado e o recheio.';
            header('Location: ' . $redirect);
            exit;
        }
    } elseif ($tipo === 'bolo') {
        if (empty($tema) || empty($sabor)) {
            $_SESSION['cart_message'] = 'Por favor, preencha o tema/ocasião e o sabor da massa do bolo.';
            header('Location: ' . $redirect);
            exit;
        }
    } else {
        $_SESSION['cart_message'] = 'Tipo de produto personalizado inválido.';
        header('Location: ' . $redirect);
        exit;
    }

    // Upload de imagem de referência
    $imagem_path = null;
    if (!empty($_FILES['imagem_referencia']['tmp_name'])) {
        $file    = $_FILES['imagem_referencia'];
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo   = finfo_open(FILEINFO_MIME_TYPE);
        $mime    = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed)) {
            $_SESSION['cart_message'] = 'Formato de imagem inválido. Use JPG, PNG ou WEBP.';
            header('Location: ' . $redirect);
            exit;
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['cart_message'] = 'Imagem muito grande. Máximo 5MB.';
            header('Location: ' . $redirect);
            exit;
        }

        $ext          = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nome_arquivo = 'pers_' . uniqid() . '.' . $ext;
        $destino      = ABSPATH . 'uploads/personalizados/' . $nome_arquivo;

        if (!is_dir(ABSPATH . 'uploads/personalizados/')) {
            mkdir(ABSPATH . 'uploads/personalizados/', 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $destino)) {
            $imagem_path = 'uploads/personalizados/' . $nome_arquivo;
        }
    }

    if (!isset($_SESSION['cart_personalizado'])) {
        $_SESSION['cart_personalizado'] = [];
    }

    $_SESSION['cart_personalizado'][] = [
        'tipo'          => htmlspecialchars($tipo,          ENT_QUOTES, 'UTF-8'),
        'tema'          => htmlspecialchars($tema,          ENT_QUOTES, 'UTF-8'),
        'sabor'         => htmlspecialchars($sabor,         ENT_QUOTES, 'UTF-8'),
        'detalhes'      => htmlspecialchars($detalhes,      ENT_QUOTES, 'UTF-8'),
        'tamanho'       => htmlspecialchars($tamanho,       ENT_QUOTES, 'UTF-8'),
        'cor'           => htmlspecialchars($cor,           ENT_QUOTES, 'UTF-8'),
        'data_desejada' => htmlspecialchars($data_desejada, ENT_QUOTES, 'UTF-8'),
        'restricoes'    => array_map(fn($r) => htmlspecialchars($r, ENT_QUOTES, 'UTF-8'), $restricoes),
        'imagem_path'   => $imagem_path,
        'quantity'      => $quantity,
        'added_at'      => date('Y-m-d H:i:s'),
    ];

    $_SESSION['cart_message'] = 'Produto personalizado adicionado ao carrinho!';
    header('Location: ' . $redirect);
    exit;
}

// ─── PRODUTO NORMAL (ID numérico) ─────────────────────────────────────────────
$product_id = intval($product_id);

if ($product_id <= 0) {
    $_SESSION['cart_message'] = 'Produto inválido.';
    header('Location: ' . $redirect);
    exit;
}

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

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$current = isset($_SESSION['cart'][$product_id]) ? intval($_SESSION['cart'][$product_id]) : 0;
$_SESSION['cart'][$product_id] = $current + $quantity;

$_SESSION['cart_message'] = '"' . htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') . '" adicionado ao carrinho!';
header('Location: ' . $redirect);
exit;