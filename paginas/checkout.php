<?php
if (!isset($_SESSION)) session_start();
include '../config.php';
require_once ABSPATH . 'inc/database.php';

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

$usuario_id     = $_SESSION['id'];
$cart           = $_SESSION['cart'] ?? [];
$personalizados = $_SESSION['cart_personalizado'] ?? [];

if (empty($cart) && empty($personalizados)) {
    echo json_encode(['success' => false, 'message' => 'Carrinho vazio']);
    exit;
}

try {
    $conn = open_database();

    $data_entrega = $_POST['data_entrega'] ?? '';
    $hora_entrega = $_POST['hora_entrega'] ?? '';
    $tipo_entrega = $_POST['tipo_entrega'] ?? 'retirada';
    $observacoes  = $_POST['observacoes'] ?? '';

    if (!$data_entrega) {
        throw new Exception('Data de retirada é obrigatória');
    }

    // Converter data BR para SQL (caso venha DD/MM/YYYY)
    $data_parts = explode('/', $data_entrega);
    if (count($data_parts) === 3) {
        $data_entrega = $data_parts[2] . '-' . $data_parts[1] . '-' . $data_parts[0];
    }

    // ─── 1) Validar produtos e montar itens ───────────────────────────────────
    $total        = 0;
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

        $quantidade     = max(1, intval($qty));
        $preco_unitario = floatval($produto['preco']);
        $subtotal       = $quantidade * $preco_unitario;

        $itens_pedido[] = [
            'id_produto'     => $product_id,
            'nome_produto'   => $produto['nome'],
            'quantidade'     => $quantidade,
            'preco_unitario' => $preco_unitario,
            'subtotal'       => $subtotal,
            'observacoes'    => $_POST['observacoes_item'][$product_id] ?? null,
            'sabor_massa'    => $_POST['sabor_massa'][$product_id] ?? null,
            'sabor_recheio'  => $_POST['sabor_recheio'][$product_id] ?? null,
            'topping'        => $_POST['topping'][$product_id] ?? null,
            'decoracao'      => $_POST['decoracao'][$product_id] ?? null,
        ];

        $total += $subtotal;
    }

    // ─── 2) Inserir pedido ────────────────────────────────────────────────────
    $stmt = $conn->prepare("
        INSERT INTO pedidos (
            id_cliente, valor_total, status, observacao, tipo,
            qtd_itens, data_entrega, forma_pagamento, tipo_entrega, hora_entrega
        ) VALUES (?, ?, 'pendente', ?, ?, ?, ?, 'WhatsApp', ?, ?)
    ");
    $stmt->execute([
        $usuario_id,
        $total,
        $observacoes,
        (!empty($personalizados)) ? 'personalizado' : 'normal',
        count($cart),
        $data_entrega,
        $tipo_entrega,
        $hora_entrega
    ]);

    $id_pedido = $conn->lastInsertId();

    // ─── 3) Inserir itens do pedido ───────────────────────────────────────────
    foreach ($itens_pedido as $item) {
        $observacao_item = '';
        if (!empty($item['sabor_massa']))   $observacao_item .= "Massa: {$item['sabor_massa']} | ";
        if (!empty($item['sabor_recheio'])) $observacao_item .= "Recheio: {$item['sabor_recheio']} | ";
        if (!empty($item['topping']))       $observacao_item .= "Topping: {$item['topping']} | ";
        if (!empty($item['decoracao']))     $observacao_item .= "Decoração: {$item['decoracao']} | ";
        if (!empty($item['observacoes']))   $observacao_item .= "Obs: {$item['observacoes']}";

        $stmt = $conn->prepare("
            INSERT INTO itens_pedido (id_pedido, id_produto, qtd, preco_unitario, subtotal, observacao)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $id_pedido,
            $item['id_produto'],
            $item['quantidade'],
            $item['preco_unitario'],
            $item['subtotal'],
            rtrim($observacao_item, ' | ')
        ]);
    }

    // ─── 4) Descontar estoque de ingredientes ─────────────────────────────────
    // Se o produto não tiver ingredientes vinculados, ignora sem erro.
    // Se estoque estiver baixo, avisa o admin mas NÃO cancela o pedido.
    $avisos_estoque = [];

    foreach ($itens_pedido as $item) {
        $stmt = $conn->prepare("
            SELECT pi.id_ingrediente, pi.qtd_necessaria, ei.nome, ei.unidade, ei.qtd_estoque
            FROM produto_ingrediente pi
            INNER JOIN estoque_ingredientes ei ON ei.id = pi.id_ingrediente
            WHERE pi.id_produto = ?
        ");
        $stmt->execute([$item['id_produto']]);
        $ingredientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($ingredientes as $ing) {
            $qtd_necessaria = $ing['qtd_necessaria'] * $item['quantidade'];

            if ($ing['qtd_estoque'] >= $qtd_necessaria) {
                // Estoque suficiente → desconta normalmente
                $stmt2 = $conn->prepare("
                    UPDATE estoque_ingredientes
                    SET qtd_estoque = qtd_estoque - ?
                    WHERE id = ?
                ");
                $stmt2->execute([$qtd_necessaria, $ing['id_ingrediente']]);
            } else {
                // Estoque insuficiente → zera e registra aviso
                $conn->prepare("
                    UPDATE estoque_ingredientes SET qtd_estoque = 0 WHERE id = ?
                ")->execute([$ing['id_ingrediente']]);

                $avisos_estoque[] = "⚠️ {$ing['nome']}: estoque insuficiente para \"{$item['nome_produto']}\"";
            }
        }
    }

    // ─── 5) Montar mensagem WhatsApp ──────────────────────────────────────────
    $msg_wpp  = "🛒 *Novo Pedido #$id_pedido*\n";
    $msg_wpp .= "📅 Entrega: " . date('d/m/Y', strtotime($data_entrega));
    if ($hora_entrega) $msg_wpp .= " às $hora_entrega";
    $msg_wpp .= "\n🚚 Tipo: " . ucfirst($tipo_entrega) . "\n\n";

    if (!empty($itens_pedido)) {
        $msg_wpp .= "🧁 *Itens:*\n";
        foreach ($itens_pedido as $item) {
            $msg_wpp .= "  • {$item['quantidade']}x {$item['nome_produto']} — R$ " . number_format($item['subtotal'], 2, ',', '.') . "\n";
        }
        $msg_wpp .= "\n";
    }

    if (!empty($personalizados)) {
        $msg_wpp .= "🎨 *Personalizados:*\n";
        foreach ($personalizados as $idx => $p) {
            $msg_wpp .= "  " . ($idx + 1) . ") " . ucfirst($p['tipo']) . " — " . $p['tema'] . "\n";
            $msg_wpp .= "     Sabor: " . $p['sabor'] . "\n";
            if (!empty($p['tamanho']))       $msg_wpp .= "     Tamanho: {$p['tamanho']}\n";
            if (!empty($p['cor']))           $msg_wpp .= "     Cor: {$p['cor']}\n";
            if (!empty($p['restricoes']))    $msg_wpp .= "     Restrições: " . implode(', ', $p['restricoes']) . "\n";
            if (!empty($p['data_desejada'])) $msg_wpp .= "     Data: " . date('d/m/Y', strtotime($p['data_desejada'])) . "\n";
            if (!empty($p['detalhes']))      $msg_wpp .= "     Detalhes: {$p['detalhes']}\n";
            if (!empty($p['imagem_path']))   $msg_wpp .= "     📎 Imagem enviada separadamente\n";
            $msg_wpp .= "     Qtd: {$p['quantity']}\n\n";
        }
    }

    if ($observacoes)            $msg_wpp .= "📝 Obs: $observacoes\n";
    if (!empty($avisos_estoque)) $msg_wpp .= "\n🔴 *Atenção estoque:*\n" . implode("\n", $avisos_estoque) . "\n";

    $msg_wpp .= "\n💰 *Total: R$ " . number_format($total, 2, ',', '.') . "*";

    // ─── 6) Limpar sessão ─────────────────────────────────────────────────────
    unset($_SESSION['cart'], $_SESSION['cart_personalizado']);

    close_database($conn);

    // ─── 7) Retornar resposta ─────────────────────────────────────────────────
    $numero_wpp = WHATSAPP_NUMBER;
    $link_wpp   = 'https://wa.me/' . $numero_wpp . '?text=' . rawurlencode($msg_wpp);

    echo json_encode([
        'success'        => true,
        'message'        => 'Pedido criado com sucesso!',
        'pedido_id'      => $id_pedido,
        'whatsapp'       => $link_wpp,
        'avisos_estoque' => $avisos_estoque,
    ]);

} catch (Exception $e) {
    if (isset($conn)) $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao criar pedido: ' . $e->getMessage()
    ]);
}