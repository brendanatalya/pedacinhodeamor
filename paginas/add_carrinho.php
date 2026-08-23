<?php
// INICIAR SESSÃO E SETAR HEADER JSON PRIMEIRO
if (!isset($_SESSION)) session_start();
header('Content-Type: application/json; charset=utf-8');
ob_start(); // Buffer output para garantir JSON puro

// Função para retornar JSON com segurança
function retornarJSON($sucesso, $mensagem, $dados = []) {
    ob_clean(); // Limpar qualquer output anterior
    echo json_encode([
        'sucesso' => $sucesso,
        'mensagem' => $mensagem,
        'dados' => $dados
    ]);
    exit;
}

// Tratamento de erro geral
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    ob_clean();
    retornarJSON(false, 'Erro no servidor: ' . $errstr . ' em ' . basename($errfile) . ':' . $errline);
});

// Tratamento de exceção não capturada
set_exception_handler(function($e) {
    ob_clean();
    retornarJSON(false, 'Exceção: ' . $e->getMessage());
});

try {
    // Verificar se config.php existe
    $config_path = dirname(__DIR__) . '/config.php';
    if (!file_exists($config_path)) {
        $config_path = '../config.php';
    }
    if (!file_exists($config_path)) {
        retornarJSON(false, 'Arquivo de configuração não encontrado em: ' . $config_path);
    }
    
    require_once $config_path;
    
    // Verificar se ABSPATH foi definido
    if (!defined('ABSPATH')) {
        retornarJSON(false, 'Constante ABSPATH não definida em config.php');
    }
    
    // Verificar se database.php existe
    $db_path = ABSPATH . 'inc/database.php';
    if (!file_exists($db_path)) {
        retornarJSON(false, 'Arquivo database.php não encontrado em: ' . $db_path);
    }
    
    require_once $db_path;

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (!empty($_POST['redirect'])) {
        header('Location: ' . $_POST['redirect']);
    } else {
        header('Location: ' . BASEURL . 'paginas/cardapio.php');
    }
    exit;
}

// Usuário precisa estar logado
if (empty($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    
    if ($isAjax) {
        retornarJSON(false, 'Faça login para adicionar produtos ao carrinho.');
    } else {
        $_SESSION['cart_message'] = 'Faça login para adicionar produtos ao carrinho.';
        $redirect = $_POST['redirect'] ?? BASEURL . 'paginas/cardapio.php';
        header('Location: ' . $redirect);
    }
    exit;
}

$product_id = $_POST['product_id'] ?? null;
$quantity   = max(1, intval($_POST['quantity'] ?? $_POST['quantidade'] ?? 1));
$redirect   = $_POST['redirect'] ?? BASEURL . 'paginas/cardapio.php';
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

// ─── PRODUTO PERSONALIZADO ────────────────────────────────────────────────────
if ($product_id === 'personalizado') {

    $tipo          = trim($_POST['tipo'] ?? '');
    $tema          = trim($_POST['tema'] ?? '');
    $sabor         = trim($_POST['sabor'] ?? '');
    $detalhes      = trim($_POST['detalhes'] ?? '');
    $data_desejada = trim($_POST['data_desejada'] ?? '');
    $restricoes    = $_POST['restricoes'] ?? [];

    // Campos específicos por tipo
    $dados_personalizados = [
        'tipo' => $tipo,
        'tema' => $tema,
        'sabor' => $sabor,
        'detalhes' => $detalhes,
        'data_desejada' => $data_desejada,
        'restricoes' => $restricoes,
    ];

    // BOLO
    if ($tipo === 'bolo') {
        if (empty($tema) || empty($sabor)) {
            $msg = $isAjax 
                ? 'Por favor, preencha o tema/ocasião e o sabor da massa do bolo.'
                : 'Por favor, preencha o tema/ocasião e o sabor da massa do bolo.';
            
            if ($isAjax) {
                retornarJSON(false, $msg);
            } else {
                $_SESSION['cart_message'] = $msg;
                header('Location: ' . $redirect);
                exit;
            }
        }

        $dados_personalizados['andares'] = intval($_POST['andares'] ?? 1);
        $dados_personalizados['pessoas'] = intval($_POST['pessoas'] ?? 10);
        $dados_personalizados['cobertura'] = trim($_POST['cobertura'] ?? '');
        $dados_personalizados['recheios'] = trim($_POST['recheios'] ?? '');
        
        // Capturar sabor de cada camada
        $camadas = [];
        for ($i = 1; $i <= $dados_personalizados['andares']; $i++) {
            $camada_sabor = trim($_POST['camada_' . $i] ?? '');
            if (!empty($camada_sabor)) {
                $camadas[$i] = $camada_sabor;
            }
        }
        $dados_personalizados['camadas_sabor'] = $camadas;
    }
    // DOCE
    elseif ($tipo === 'doce') {
        if (empty($sabor)) {
            $msg = 'Por favor, informe o sabor do doce.';
            if ($isAjax) {
                retornarJSON(false, $msg);
            } else {
                $_SESSION['cart_message'] = $msg;
                header('Location: ' . $redirect);
                exit;
            }
        }

        $dados_personalizados['camadas'] = intval($_POST['camadas'] ?? 1);
        $dados_personalizados['quantidade'] = $quantity;
        
        // Capturar sabor de cada camada
        $camadas = [];
        for ($i = 1; $i <= $dados_personalizados['camadas']; $i++) {
            $camada_sabor = trim($_POST['camada_' . $i] ?? '');
            if (!empty($camada_sabor)) {
                $camadas[$i] = $camada_sabor;
            }
        }
        $dados_personalizados['camadas_sabor'] = $camadas;
    }
    // SALGADO
    elseif ($tipo === 'salgado') {
        $tipo_salgado = trim($_POST['tipo_salgado'] ?? '');
        $recheio = trim($_POST['recheio'] ?? '');

        if (empty($tipo_salgado) || empty($recheio)) {
            $msg = 'Por favor, selecione o tipo de salgado e o recheio.';
            if ($isAjax) {
                retornarJSON(false, $msg);
            } else {
                $_SESSION['cart_message'] = $msg;
                header('Location: ' . $redirect);
                exit;
            }
        }

        $dados_personalizados['tipo_salgado'] = $tipo_salgado;
        $dados_personalizados['recheio'] = $recheio;
        $dados_personalizados['tamanho'] = trim($_POST['tamanho'] ?? '');
        $dados_personalizados['quantidade'] = $quantity;
    } else {
        $msg = 'Tipo de produto personalizado inválido.';
        if ($isAjax) {
            retornarJSON(false, $msg);
        } else {
            $_SESSION['cart_message'] = $msg;
            header('Location: ' . $redirect);
            exit;
        }
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
            $msg = 'Formato de imagem inválido. Use JPG, PNG ou WEBP.';
            if ($isAjax) {
                retornarJSON(false, $msg);
            } else {
                $_SESSION['cart_message'] = $msg;
                header('Location: ' . $redirect);
                exit;
            }
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            $msg = 'Imagem muito grande. Máximo 5MB.';
            if ($isAjax) {
                retornarJSON(false, $msg);
            } else {
                $_SESSION['cart_message'] = $msg;
                header('Location: ' . $redirect);
                exit;
            }
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

    // Montar item do carrinho
    $item_carrinho = [
        'id' => uniqid('pers_'),
        'tipo' => htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8'),
        'imagem_path' => $imagem_path,
        'quantity' => $quantity,
        'added_at' => date('Y-m-d H:i:s'),
    ];

    // Adicionar campos específicos sanitizados
    foreach ($dados_personalizados as $chave => $valor) {
        if (is_array($valor)) {
            $item_carrinho[$chave] = array_map(fn($v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8'), $valor);
        } else {
            $item_carrinho[$chave] = htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
        }
    }

    $_SESSION['cart_personalizado'][] = $item_carrinho;

    $msg = 'Produto personalizado adicionado ao carrinho!';
    
    if ($isAjax) {
        retornarJSON(true, $msg, [
            'cart_count' => count($_SESSION['cart_personalizado']) + (isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0)
        ]);
    } else {
        $_SESSION['cart_message'] = $msg;
        header('Location: ' . $redirect);
        exit;
    }
}

// ─── PRODUTO NORMAL (ID numérico) ─────────────────────────────────────────────
$product_id = intval($product_id);

if ($product_id <= 0) {
    $msg = 'Produto inválido.';
    if ($isAjax) {
        retornarJSON(false, $msg);
    } else {
        $_SESSION['cart_message'] = $msg;
        header('Location: ' . $redirect);
        exit;
    }
}

$produto = find_product($product_id);

if (!$produto) {
    $msg = 'Produto não encontrado.';
    if ($isAjax) {
        retornarJSON(false, $msg);
    } else {
        $_SESSION['cart_message'] = $msg;
        header('Location: ' . $redirect);
        exit;
    }
}

if (!$produto['disponivel']) {
    $msg = 'Este produto está indisponível no momento.';
    if ($isAjax) {
        retornarJSON(false, $msg);
    } else {
        $_SESSION['cart_message'] = $msg;
        header('Location: ' . $redirect);
        exit;
    }
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$current = isset($_SESSION['cart'][$product_id]) ? intval($_SESSION['cart'][$product_id]) : 0;
$_SESSION['cart'][$product_id] = $current + $quantity;

$msg = '"' . htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') . '" adicionado ao carrinho!';

if ($isAjax) {
    retornarJSON(true, $msg, [
        'cart_count' => array_sum($_SESSION['cart']) + (isset($_SESSION['cart_personalizado']) ? count($_SESSION['cart_personalizado']) : 0)
    ]);
} else {
    $_SESSION['cart_message'] = $msg;
    header('Location: ' . $redirect);
    exit;
}

} catch (Exception $e) {
    ob_clean();
    retornarJSON(false, 'Erro no servidor: ' . $e->getMessage());
}

// Se chegou aqui sem sair, limpar buffer (segurança)
ob_end_clean();
retornarJSON(false, 'Erro desconhecido - nenhuma ação foi executada');
?>