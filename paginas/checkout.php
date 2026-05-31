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
        
        if (!$produto) {
            throw new Exception("Produto #$product_id não encontrado.");
        }
        if (!$produto['disponivel']) {
            throw new Exception("O produto '{$produto['nome']}' não está disponível.");
}
        
        $quantidade = max(1, intval($qty));
        $preco_unitario = floatval($produto['preco']);
        $subtotal = $quantidade * $preco_unitario;
        
       $itens_pedido[] = [
    'id_produto' => $product_id,
    'quantidade' => $quantidade,
    'preco_unitario' => $preco_unitario,
    'subtotal' => $subtotal,
    'observacoes' => $_POST['observacoes_item'][$product_id] ?? null,
    'sabor_massa' => $_POST['sabor_massa'][$product_id] ?? null,
    'sabor_recheio' => $_POST['sabor_recheio'][$product_id] ?? null,
    'topping' => $_POST['topping'][$product_id] ?? null,
    'decoracao' => $_POST['decoracao'][$product_id] ?? null,
];
        
        $total += $subtotal;
    }
    
$stmt->execute([
    $usuario_id,
    $total,
    'pendente',
    $observacoes,
    $tipo_entrega == 'retirada' ? 'personalizado' : 'normal',
    count($cart),
    $data_entrega,
    'WhatsApp',
    $tipo_entrega,
    $hora_entrega
]);
    $id_pedido = $conn->lastInsertId();
    
   foreach ($itens_pedido as $item) {

    $observacao_item = '';

    if (!empty($item['sabor_massa'])) {
        $observacao_item .= "Massa: {$item['sabor_massa']} | ";
    }

    if (!empty($item['sabor_recheio'])) {
        $observacao_item .= "Recheio: {$item['sabor_recheio']} | ";
    }

    if (!empty($item['topping'])) {
        $observacao_item .= "Topping: {$item['topping']} | ";
    }

    if (!empty($item['decoracao'])) {
        $observacao_item .= "Decoração: {$item['decoracao']} | ";
    }

    if (!empty($item['observacoes'])) {
        $observacao_item .= "Obs: {$item['observacoes']}";
    }

    $stmt = $conn->prepare("
        INSERT INTO itens_pedido (
            id_pedido,
            id_produto,
            qtd,
            preco_unitario,
            subtotal,
            observacao
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $id_pedido,
        $item['id_produto'],
        $item['quantidade'],
        $item['preco_unitario'],
        $item['subtotal'],
        $observacao_item
    ]);
}

    // Para cada item do pedido, descontar do estoque
foreach ($itens_pedido as $item) {
    $stmt = $conn->prepare("
        SELECT pi.id_ingrediente, pi.qtd_necessaria
        FROM produto_ingrediente pi
        WHERE pi.id_produto = ?
    ");
    $stmt->execute([$item['id_produto']]);
    $ingredientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($ingredientes as $ing) {
        $qtd_total = $ing['qtd_necessaria'] * $item['quantidade'];

        $stmt2 = $conn->prepare("
            UPDATE estoque_ingredientes
            SET qtd_estoque = qtd_estoque - ?
            WHERE id = ? AND qtd_estoque >= ?
        ");
        $stmt2->execute([$qtd_total, $ing['id_ingrediente'], $qtd_total]);

        if ($stmt2->rowCount() === 0) {
            throw new Exception("Estoque insuficiente para um dos ingredientes.");
        }
    }
}
    // Limpar carrinho da sessão
    unset($_SESSION['cart']);
    
    close_database($conn);
    
    // Retornar sucesso com ID do pedido
    echo json_encode([
        'success' => true,
        'message' => 'Pedido criado com sucesso!',
        'id_pedido' => $id_pedido,
        'redirect_url' => BASEURL . 'minha_conta.php'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao criar pedido: ' . $e->getMessage()
    ]);
}
?>
