<?php 
if (!isset($_SESSION)) session_start();
include '../config.php';
require_once ABSPATH . 'inc/database.php';

// Verificar se usuário está logado
if (empty($_SESSION['logado']) || $_SESSION['tipo'] !== 'cliente') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Você precisa estar logado para continuar']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASEURL);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$usuario_id = $_SESSION['id'];
$cart = $_SESSION['cart'] ?? [];

// Validar carrinho
if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Carrinho vazio']);
    exit;
}

try {
    $conn = open_database();
    
    // Buscar dados do pedido
    $data_entrega = $_POST['data_entrega'] ?? '';
    $hora_entrega = $_POST['hora_entrega'] ?? '';
    $tipo_entrega = $_POST['tipo_entrega'] ?? 'retirada';
    $observacoes = $_POST['observacoes'] ?? '';
    
    // Validar data de entrega
    if (!$data_entrega) {
        throw new Exception('Data de entrega é obrigatória');
    }
    
    // Converter data do formato BR para SQL
    $data_parts = explode('/', $data_entrega);
    if (count($data_parts) === 3) {
        $data_entrega = $data_parts[2] . '-' . $data_parts[1] . '-' . $data_parts[0];
    }
    
    // Validar cada produto no carrinho
    $total = 0;
    $itens_pedido = [];
    
    foreach ($cart as $product_id => $qty) {
        $stmt = $conn->prepare("SELECT * FROM produtos WHERE id = ?");
        $stmt->execute([$product_id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$produto || !$produto['disponivel']) {
            throw new Exception("O produto '{$produto['nome']}' não está mais disponível");
        }
        
        $quantidade = max(1, intval($qty));
        $preco_unitario = floatval($produto['preco']);
        $subtotal = $quantidade * $preco_unitario;
        
        $itens_pedido[] = [
            'produto_id' => $product_id,
            'quantidade' => $quantidade,
            'preco_unitario' => $preco_unitario,
            'subtotal' => $subtotal,
            'sabor_massa' => $_POST['sabor_massa'][$product_id] ?? null,
            'sabor_recheio' => $_POST['sabor_recheio'][$product_id] ?? null,
            'topping' => $_POST['topping'][$product_id] ?? null,
            'decoracao' => $_POST['decoracao'][$product_id] ?? null,
            'observacoes' => $_POST['observacoes_item'][$product_id] ?? null
        ];
        
        $total += $subtotal;
    }
    
    // Adicionar frete (opcional)
    $frete = isset($_POST['frete']) ? floatval($_POST['frete']) : 0;
    $total += $frete;
    
    // Criar pedido
    $stmt = $conn->prepare("
        INSERT INTO pedidos (usuario_id, status, data_entrega, tipo_entrega, observacoes, total)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$usuario_id, 'pendente', $data_entrega, $tipo_entrega, $observacoes, $total]);
    $pedido_id = $conn->lastInsertId();
    
    // Criar itens do pedido
    foreach ($itens_pedido as $item) {
        $stmt = $conn->prepare("
            INSERT INTO itens_pedido 
            (pedido_id, produto_id, quantidade, preco_unitario, subtotal, sabor_massa, sabor_recheio, topping, decoracao, observacoes, disponivel)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $pedido_id,
            $item['produto_id'],
            $item['quantidade'],
            $item['preco_unitario'],
            $item['subtotal'],
            $item['sabor_massa'],
            $item['sabor_recheio'],
            $item['topping'],
            $item['decoracao'],
            $item['observacoes'],
            1
        ]);
    }
    
    // Criar agendamento
    $stmt = $conn->prepare("
        INSERT INTO agendamentos (pedido_id, usuario_id, data_agendada, tipo, status, observacoes)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $localizacao = $tipo_entrega === 'entrega' ? 'A definir' : 'Loja';
    $stmt->execute([
        $pedido_id,
        $usuario_id,
        $data_entrega,
        $tipo_entrega,
        'agendado',
        "Horário: $hora_entrega"
    ]);
    $agendamento_id = $conn->lastInsertId();
    
    // Limpar carrinho da sessão
    unset($_SESSION['cart']);
    
    close_database($conn);
    
    // Retornar sucesso com ID do pedido
    echo json_encode([
        'success' => true,
        'message' => 'Pedido criado com sucesso!',
        'pedido_id' => $pedido_id,
        'agendamento_id' => $agendamento_id,
        'redirect_url' => BASEURL . 'agenda_cliente.php?ver=' . $agendamento_id
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao criar pedido: ' . $e->getMessage()
    ]);
}
?>
