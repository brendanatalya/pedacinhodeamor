<?php 
if (!isset($_SESSION)) session_start();

// Verificar se usuário é admin
if (empty($_SESSION['logado']) || $_SESSION['tipo'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

include '../../config.php';
require_once(DBAPI);

header('Content-Type: application/json; charset=utf-8');

try {
    $conn = open_database();
    
    // Pedidos de hoje
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM pedidos 
        WHERE DATE(data_pedido) = CURDATE()
    ");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $pedidos_hoje = $result['total'];
    
    // Pedidos pendentes
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM pedidos 
        WHERE status = 'pendente'
    ");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $pedidos_pendentes = $result['total'];
    
    // Total de produtos
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM produtos");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_produtos = $result['total'];
    
    // Total de clientes
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'cliente'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_clientes = $result['total'];
    
    // Últimos pedidos
    $stmt = $conn->prepare("
        SELECT 
            p.id, 
            p.total, 
            p.status,
            p.data_pedido,
            u.nome
        FROM pedidos p
        INNER JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY p.data_pedido DESC
        LIMIT 5
    ");
    $stmt->execute();
    $ultimos_pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    close_database($conn);
    
    echo json_encode([
        'pedidos_hoje' => $pedidos_hoje,
        'pedidos_pendentes' => $pedidos_pendentes,
        'total_produtos' => $total_produtos,
        'total_clientes' => $total_clientes,
        'ultimos_pedidos' => $ultimos_pedidos
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
