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

    $tipo = trim($_POST['tipo'] ?? '');

    if (empty($tipo)) {
        $_SESSION['cart_message'] = 'Selecione o tipo de produto personalizado.';
        header('Location: ' . $redirect);
        exit;
    }

    // Helper: sanitiza um campo de texto simples
    $s = fn(string $key): string => htmlspecialchars(trim($_POST[$key] ?? ''), ENT_QUOTES, 'UTF-8');

    // Helper: sanitiza um array de checkboxes/tags (enviados como name="campo[]")
    $arr = function (string $key): array {
        $raw = $_POST[$key] ?? [];
        if (!is_array($raw)) return [];
        return array_map(
            fn($v) => htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8'),
            array_filter($raw, fn($v) => $v !== '')
        );
    };

    // Monta o item de acordo com o tipo escolhido
    $item = [
        'tipo'     => htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8'),
        'quantity' => $quantity,
        'added_at' => date('Y-m-d H:i:s'),
    ];

    switch ($tipo) {

        case 'bolo':
            $item['tamanho']    = $s('tamanho');       // "20 fatias" etc.
            $item['massa']      = $s('massa');
            $item['recheio']    = $arr('recheio');      // pode ter mais de um
            $item['cobertura']  = $s('cobertura');
            $item['tema']       = $s('tema');
            $item['texto_bolo'] = $s('texto_bolo');
            $item['restricoes'] = $arr('restricoes');
            $item['obs']        = $s('obs');

            if (empty($item['tamanho']) || empty($item['massa']) || empty($item['cobertura'])) {
                $_SESSION['cart_message'] = 'Preencha tamanho, massa e cobertura do bolo.';
                header('Location: ' . $redirect);
                exit;
            }
            break;

        case 'doce':
            $item['tipo_doce']  = $s('tipo_doce');
            $item['sabor']      = $arr('sabor');
            $item['embalagem']  = $s('embalagem');
            $item['ocasiao']    = $s('ocasiao');
            $item['obs']        = $s('obs');

            if (empty($item['tipo_doce'])) {
                $_SESSION['cart_message'] = 'Selecione o tipo de doce.';
                header('Location: ' . $redirect);
                exit;
            }
            break;

        case 'salgado':
            $item['tipo_salgado'] = $s('tipo_salgado');
            $item['preparo']      = $s('preparo');
            $item['recheio']      = $arr('recheio');
            $item['data_evento']  = $s('data_evento');
            $item['obs']          = $s('obs');

            if (empty($item['tipo_salgado'])) {
                $_SESSION['cart_message'] = 'Selecione o tipo de salgado.';
                header('Location: ' . $redirect);
                exit;
            }
            break;

        default:
            $_SESSION['cart_message'] = 'Tipo de produto inválido.';
            header('Location: ' . $redirect);
            exit;
    }

    // Personalizados ficam em $_SESSION['cart_personalizado'] (lista separada
    // pois cada item tem configuração própria e não tem ID numérico de produto)
    if (!isset($_SESSION['cart_personalizado'])) {
        $_SESSION['cart_personalizado'] = [];
    }

    $_SESSION['cart_personalizado'][] = $item;
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
?>