<?php
if (!isset($_SESSION)) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

include dirname(__DIR__, 2) . '/config.php';
require_once(DBAPI);

// Verificar login admin
if (
    empty($_SESSION['logado']) ||
    $_SESSION['tipo'] !== 'admin'
) {

    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Acesso negado.'
    ]);

    exit;
}

try {

    $conn = open_database();

    /*
    | PEDIDOS DE HOJE
    */
    $stmt = $conn->prepare("
    SELECT COUNT(*) AS valor_total
    FROM pedidos
    WHERE data_pedido >= CURDATE()
      AND data_pedido < CURDATE() + INTERVAL 1 DAY
    ");


    $stmt->execute();

    $pedidos_hoje = $stmt->fetch(PDO::FETCH_ASSOC)['valor_total'] ?? 0;

    /*
    | PEDIDOS PENDENTES
    */
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS valor_total
        FROM pedidos
        WHERE status = 'pendente'
    ");

    $stmt->execute();

    $pedidos_pendentes = $stmt->fetch(PDO::FETCH_ASSOC)['valor_total'] ?? 0;

    /*
    | TOTAL PRODUTOS
    */
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS valor_total
        FROM produtos
    ");

    $stmt->execute();

    $total_produtos = $stmt->fetch(PDO::FETCH_ASSOC)['valor_total'] ?? 0;

    /*
    | TOTAL CLIENTES
    */
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS valor_total
        FROM usuarios
        WHERE tipo = 'cliente'
    ");

    $stmt->execute();

    $total_clientes = $stmt->fetch(PDO::FETCH_ASSOC)['valor_total'] ?? 0;

    /*
    | ÚLTIMOS PEDIDOS
    */
    $stmt = $conn->prepare("
        SELECT
            p.id,
            p.valor_total,
            p.status,
            p.data_pedido,
            u.nome
        FROM pedidos p
        INNER JOIN usuarios u
            ON p.id_cliente = u.id
        ORDER BY p.data_pedido DESC
        LIMIT 5
    ");

    $stmt->execute();

    $ultimos_pedidos = array_map(function ($p) {
        return [
            'id'          => (int)$p['id'],
            'total'       => number_format((float)$p['valor_total'], 2, '.', ''),
            'status'      => $p['status'],
            'data_pedido' => $p['data_pedido'],
            'nome'        => $p['nome'],
        ];
    }, $stmt->fetchAll(PDO::FETCH_ASSOC));

    // Fechar conexão
    close_database($conn);

    /*
    | RESPOSTA
    */
    echo json_encode([
        'success' => true,

        'dashboard' => [

            'pedidos_hoje' => (int)$pedidos_hoje,

            'pedidos_pendentes' => (int)$pedidos_pendentes,

            'total_produtos' => (int)$total_produtos,

            'total_clientes' => (int)$total_clientes,

            'ultimos_pedidos' => $ultimos_pedidos
        ]

    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([

        'success' => false,

        'message' => 'Erro interno do servidor.',

        // REMOVA EM PRODUÇÃO
        'error' => $e->getMessage()

    ], JSON_UNESCAPED_UNICODE);
}


?>