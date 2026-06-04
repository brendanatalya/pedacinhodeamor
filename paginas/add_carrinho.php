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
    $tipo          = trim($_POST['tipo']          ?? '');
    $tema          = trim($_POST['tema']          ?? '');
    $sabor         = trim($_POST['sabor']         ?? '');
    $detalhes      = trim($_POST['detalhes']      ?? '');
    $tamanho       = trim($_POST['tamanho']       ?? '');
    $cor           = trim($_POST['cor']           ?? '');
    $data_desejada = trim($_POST['data_desejada'] ?? '');
    $restricoes    = $_POST['restricoes'] ?? [];

    // Validação específica por tipo (tema não é obrigatório para doce)
    if ($tipo === 'doce') {
        if (empty($sabor)) {
            $_SESSION['cart_message'] = 'Por favor, informe o sabor do doce.';
            header('Location: ' . $redirect);
            exit;
        }
    } elseif ($tipo === 'salgado') {
        // No formulário de salgado, 'sabor' = tipo de salgado e 'tema' = recheio
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

    // Upload de imagem
    $imagem_path = null;
    if (!empty($_FILES['imagem_referencia']['tmp_name'])) {
        $file      = $_FILES['imagem_referencia'];
        $allowed   = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo     = finfo_open(FILEINFO_MIME_TYPE);
        $mime      = finfo_file($finfo, $file['tmp_name']);
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

        $ext         = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nome_arquivo = 'pers_' . uniqid() . '.' . $ext;
        $destino      = ABSPATH . 'uploads/personalizados/' . $nome_arquivo;

        // Cria a pasta se não existir
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
        'tipo'          => htmlspecialchars($tipo),
        'tema'          => htmlspecialchars($tema),
        'sabor'         => htmlspecialchars($sabor),
        'detalhes'      => htmlspecialchars($detalhes),
        'tamanho'       => htmlspecialchars($tamanho),
        'cor'           => htmlspecialchars($cor),
        'data_desejada' => htmlspecialchars($data_desejada),
        'restricoes'    => array_map('htmlspecialchars', $restricoes),
        'imagem_path'   => $imagem_path,
        'quantity'      => $quantity,
        'added_at'      => date('Y-m-d H:i:s'),
    ];

    $_SESSION['cart_message'] = 'Produto personalizado adicionado ao carrinho!';
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