<?php
if (!isset($_SESSION)) session_start();

header('Content-Type: application/json; charset=utf-8');

include dirname(__DIR__, 2) . '/config.php';
require_once(DBAPI);

if (empty($_SESSION['logado']) || $_SESSION['tipo'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID inválido.']);
    exit;
}

try {
    $conn = open_database();

    // Dados do pedido + cliente
    $stmt = $conn->prepare("
        SELECT
            p.id,
            p.valor_total,
            p.status,
            p.observacao,
            p.tipo,
            p.imagem_referencia,
            p.data_pedido,
            p.data_entrega,
            p.hora_entrega,
            p.forma_pagamento,
            p.tipo_entrega,
            u.nome AS cliente_nome,
            u.email AS cliente_email
        FROM pedidos p
        INNER JOIN usuarios u ON p.id_cliente = u.id
        WHERE p.id = :id
    ");
    $stmt->execute([':id' => $id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Pedido não encontrado.']);
        exit;
    }

    // Itens do pedido
    $stmt = $conn->prepare("
        SELECT
            ip.qtd,
            ip.preco_unitario,
            ip.subtotal,
            ip.observacao,
            pr.nome AS produto_nome
        FROM itens_pedido ip
        INNER JOIN produtos pr ON ip.id_produto = pr.id
        WHERE ip.id_pedido = :id
    ");
    $stmt->execute([':id' => $id]);
    $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

    close_database($conn);

    echo json_encode([
        'success' => true,
        'pedido'  => $pedido,
        'itens'   => $itens
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno.',
        'error'   => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>