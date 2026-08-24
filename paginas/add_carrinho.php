<?php
// INICIAR SESSÃO PRIMEIRO
if (!isset($_SESSION)) session_start();

// VERIFICAR REQUISIÇÃO ANTES DE QUALQUER OUTPUT
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $redirect = $_POST['redirect'] ?? BASEURL . 'paginas/cardapio.php';
    header('Location: ' . $redirect);
    exit;
}

// ──────────────────────────────────────────────────────────────
// DETECTAR SE É UMA CHAMADA AJAX (fetch/XHR) OU UM <form> COMUM
// ──────────────────────────────────────────────────────────────
// Isso decide se a resposta é JSON (pra JS consumir) ou um
// redirecionamento de volta pra página (pra quando o form é
// enviado do jeito tradicional, sem JavaScript).
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

// redirect "cru" (sem BASEURL ainda, porque config.php não foi carregado)
$redirect = $_POST['redirect'] ?? null;

ob_start();

// Função para retornar a resposta com segurança, no formato certo pra cada caso
function retornarJSON($sucesso, $mensagem, $dados = []) {
    global $isAjax, $redirect;

    ob_clean();

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'sucesso' => $sucesso,
            'mensagem' => $mensagem,
            'dados' => $dados
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Form comum (sem JS): guarda a mensagem na sessão e volta pra página de origem
    if (!isset($_SESSION)) session_start();
    $_SESSION['cart_message'] = $mensagem;
    header('Location: ' . ($redirect ?: (defined('BASEURL') ? BASEURL . 'paginas/cardapio.php' : 'cardapio.php')));
    exit;
}

// Tratamento de erro global (NÃO tenta redirecionar)
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    ob_clean();
    retornarJSON(false, 'Erro: ' . $errstr . ' (' . basename($errfile) . ':' . $errline . ')');
});

// Tratamento de exceção não capturada
set_exception_handler(function($e) {
    ob_clean();
    retornarJSON(false, 'Erro: ' . $e->getMessage());
});

try {
    // ──────────────────────────────────────────────────────────────
    // CARREGAR CONFIGURAÇÃO
    // ──────────────────────────────────────────────────────────────
    $config_paths = [
        dirname(__DIR__) . '/config.php',
        '../config.php',
        __DIR__ . '/../config.php'
    ];
    
    $config_found = false;
    foreach ($config_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $config_found = true;
            break;
        }
    }
    
    if (!$config_found) {
        retornarJSON(false, 'config.php não encontrado. Verifique os paths do seu servidor.');
    }
    
    if (!defined('ABSPATH')) {
        retornarJSON(false, 'ABSPATH não definido em config.php');
    }
    
    // ──────────────────────────────────────────────────────────────
    // CARREGAR DATABASE
    // ──────────────────────────────────────────────────────────────
    $db_path = ABSPATH . 'inc/database.php';
    if (!file_exists($db_path)) {
        retornarJSON(false, 'database.php não encontrado em: ' . $db_path);
    }
    require_once $db_path;
    
    // ──────────────────────────────────────────────────────────────
    // VERIFICAR LOGIN
    // ──────────────────────────────────────────────────────────────
    if (empty($_SESSION['logado']) || $_SESSION['logado'] !== true) {
        retornarJSON(false, 'Você precisa estar logado para adicionar produtos ao carrinho.');
    }
    
    // ──────────────────────────────────────────────────────────────
    // CAPTURAR DADOS DO POST
    // ──────────────────────────────────────────────────────────────
    $product_id = $_POST['product_id'] ?? null;
    $quantity   = max(1, intval($_POST['quantity'] ?? $_POST['quantidade'] ?? 1));

    // Agora que config.php já carregou, aplica o default com BASEURL se não veio nenhum redirect
    if (!$redirect) {
        $redirect = BASEURL . 'paginas/cardapio.php';
    }
    
    // Validar quantity
    if ($quantity > 999) {
        retornarJSON(false, 'Quantidade máxima é 999 unidades.');
    }
    
    // ──────────────────────────────────────────────────────────────
    // PRODUTO PERSONALIZADO
    // ──────────────────────────────────────────────────────────────
    if ($product_id === 'personalizado') {
        
        $tipo = trim($_POST['tipo'] ?? '');
        if (empty($tipo) || !in_array($tipo, ['bolo', 'doce', 'salgado'])) {
            retornarJSON(false, 'Tipo de produto personalizado inválido.');
        }
        
        // Campos comuns a todos os tipos
        $dados = [
            'tipo' => htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8'),
            'tema' => htmlspecialchars(trim($_POST['tema'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'sabor' => htmlspecialchars(trim($_POST['sabor'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'detalhes' => htmlspecialchars(trim($_POST['detalhes'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'data_desejada' => trim($_POST['data_desejada'] ?? ''),
            'restricoes' => array_map(fn($v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8'), 
                                     $_POST['restricoes'] ?? []),
        ];
        
        // Validações por tipo
        switch ($tipo) {
            case 'bolo':
                if (empty($dados['tema']) || empty($dados['sabor'])) {
                    retornarJSON(false, 'Bolo: preencha tema/ocasião e sabor da massa.');
                }
                
                $dados['andares'] = max(1, intval($_POST['andares'] ?? 1));
                $dados['pessoas'] = max(1, intval($_POST['pessoas'] ?? 10));
                $dados['cobertura'] = htmlspecialchars(trim($_POST['cobertura'] ?? ''), ENT_QUOTES, 'UTF-8');
                $dados['recheios'] = htmlspecialchars(trim($_POST['recheios'] ?? ''), ENT_QUOTES, 'UTF-8');
                
                // Capturar sabor de cada camada
                $camadas = [];
                for ($i = 1; $i <= $dados['andares']; $i++) {
                    $sabor_camada = htmlspecialchars(trim($_POST['camada_' . $i] ?? ''), ENT_QUOTES, 'UTF-8');
                    if (!empty($sabor_camada)) {
                        $camadas[$i] = $sabor_camada;
                    }
                }
                if (!empty($camadas)) {
                    $dados['camadas_sabor'] = $camadas;
                }
                break;
            
            case 'doce':
                if (empty($dados['sabor'])) {
                    retornarJSON(false, 'Doce: informe o sabor.');
                }
                
                $dados['camadas'] = max(1, intval($_POST['camadas'] ?? 1));
                $dados['quantidade'] = $quantity;
                
                // Capturar sabor de cada camada
                $camadas = [];
                for ($i = 1; $i <= $dados['camadas']; $i++) {
                    $sabor_camada = htmlspecialchars(trim($_POST['camada_' . $i] ?? ''), ENT_QUOTES, 'UTF-8');
                    if (!empty($sabor_camada)) {
                        $camadas[$i] = $sabor_camada;
                    }
                }
                if (!empty($camadas)) {
                    $dados['camadas_sabor'] = $camadas;
                }
                break;
            
            case 'salgado':
                $tipo_salgado = htmlspecialchars(trim($_POST['tipo_salgado'] ?? ''), ENT_QUOTES, 'UTF-8');
                $recheio = htmlspecialchars(trim($_POST['recheio'] ?? ''), ENT_QUOTES, 'UTF-8');
                
                if (empty($tipo_salgado) || empty($recheio)) {
                    retornarJSON(false, 'Salgado: selecione o tipo e o recheio.');
                }
                
                $dados['tipo_salgado'] = $tipo_salgado;
                $dados['recheio'] = $recheio;
                $dados['tamanho'] = htmlspecialchars(trim($_POST['tamanho'] ?? ''), ENT_QUOTES, 'UTF-8');
                $dados['quantidade'] = $quantity;
                break;
        }
        
        // ─────────────────────────────────────────────────────────
        // PROCESSAR UPLOAD DE IMAGEM (se houver)
        // ─────────────────────────────────────────────────────────
        $imagem_path = null;
        
        if (!empty($_FILES['imagem_referencia']['tmp_name'])) {
            $file = $_FILES['imagem_referencia'];
            
            // Validar se não há erro no upload
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $erros_upload = [
                    UPLOAD_ERR_INI_SIZE => 'Arquivo maior que o permitido pelo servidor.',
                    UPLOAD_ERR_FORM_SIZE => 'Arquivo maior que o permitido no formulário.',
                    UPLOAD_ERR_PARTIAL => 'Upload foi parcialmente realizado.',
                    UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Diretório temporário não disponível.',
                    UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever no disco.',
                    UPLOAD_ERR_EXTENSION => 'Upload impedido por uma extensão do PHP.',
                ];
                $msg = $erros_upload[$file['error']] ?? 'Erro desconhecido no upload.';
                retornarJSON(false, 'Erro na imagem: ' . $msg);
            }
            
            // Validar tipo MIME
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            
            if (function_exists('mime_content_type')) {
                $mime = mime_content_type($file['tmp_name']);
            } else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
            }
            
            if (!in_array($mime, $allowed_types)) {
                retornarJSON(false, 'Formato de imagem inválido. Use JPG, PNG ou WEBP. Tipo detectado: ' . $mime);
            }
            
            // Validar tamanho (5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                retornarJSON(false, 'Imagem muito grande. Máximo 5MB.');
            }
            
            // Criar diretório se não existir
            $upload_dir = ABSPATH . 'uploads/personalizados/';
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    retornarJSON(false, 'Não foi possível criar diretório para armazenar imagem.');
                }
            }
            
            // Salvar arquivo com nome único
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $nome_arquivo = 'pers_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
            $destino = $upload_dir . $nome_arquivo;
            
            if (!move_uploaded_file($file['tmp_name'], $destino)) {
                retornarJSON(false, 'Falha ao salvar imagem. Verifique as permissões.');
            }
            
            $imagem_path = 'uploads/personalizados/' . $nome_arquivo;
        }
        
        // ─────────────────────────────────────────────────────────
        // ADICIONAR AO CARRINHO
        // ─────────────────────────────────────────────────────────
        if (!isset($_SESSION['cart_personalizado'])) {
            $_SESSION['cart_personalizado'] = [];
        }
        
        $item = [
            'id' => 'pers_' . bin2hex(random_bytes(4)) . '_' . time(),
            'imagem_path' => $imagem_path,
            'quantity' => $quantity,
            'added_at' => date('Y-m-d H:i:s'),
        ];
        
        // Mesclar dados personalizados
        $item = array_merge($item, $dados);
        
        $_SESSION['cart_personalizado'][] = $item;
        
        // Calcular contagem total
        $total_count = array_sum($_SESSION['cart'] ?? []) + 
                      count($_SESSION['cart_personalizado']);
        
        retornarJSON(true, 'Produto personalizado adicionado ao carrinho!', [
            'cart_count' => $total_count,
            'redirect' => $redirect
        ]);
    }
    
    // ──────────────────────────────────────────────────────────────
    // PRODUTO NORMAL (ID numérico)
    // ──────────────────────────────────────────────────────────────
    $product_id = intval($product_id);
    
    if ($product_id <= 0) {
        retornarJSON(false, 'ID do produto inválido.');
    }
    
    // Verificar se produto existe
    $produto = find_product($product_id);
    
    if (!$produto) {
        retornarJSON(false, 'Produto não encontrado no banco de dados.');
    }
    
    if (!isset($produto['disponivel']) || !$produto['disponivel']) {
        retornarJSON(false, 'Este produto está indisponível no momento.');
    }
    
    // Inicializar carrinho se necessário
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Adicionar ou incrementar quantidade
    $current = isset($_SESSION['cart'][$product_id]) ? intval($_SESSION['cart'][$product_id]) : 0;
    $new_qty = $current + $quantity;
    
    // Validar quantidade total
    if ($new_qty > 999) {
        retornarJSON(false, 'Limite de quantidade para este produto é 999.');
    }
    
    $_SESSION['cart'][$product_id] = $new_qty;
    
    // Calcular contagem total do carrinho
    $total_count = array_sum($_SESSION['cart'] ?? []) + 
                  count($_SESSION['cart_personalizado'] ?? []);
    
    $nome_produto = htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8');
    $msg = sprintf('"%s" adicionado ao carrinho! (Qtd: +%d)', $nome_produto, $quantity);
    
    retornarJSON(true, $msg, [
        'cart_count' => $total_count,
        'redirect' => $redirect,
        'product_id' => $product_id,
        'new_quantity' => $new_qty
    ]);
    
} catch (Exception $e) {
    ob_clean();
    retornarJSON(false, 'Erro no servidor: ' . $e->getMessage());
}

// Segurança: se chegou aqui, algo deu errado
ob_end_clean();
retornarJSON(false, 'Erro desconhecido - nenhuma ação foi executada.');
?>