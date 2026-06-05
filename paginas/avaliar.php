<?php
session_start();
include '../config.php';
require_once ABSPATH . 'inc/database.php';

if (empty($_SESSION['logado'])) {
    header('Location: ' . BASEURL . 'index.php'); exit;
}

$id_pedido = intval($_GET['pedido'] ?? 0);
$conn = open_database();

// Verificar se pedido pertence ao cliente e foi entregue
$stmt = $conn->prepare("
    SELECT id FROM pedidos 
    WHERE id = ? AND id_cliente = ? AND status = 'entregue'
");
$stmt->execute([$id_pedido, $_SESSION['id']]);
$pedido = $stmt->fetch();

if (!$pedido) {
    header('Location: ' . BASEURL . 'minha_conta.php'); exit;
}

// Verificar se já avaliou
$stmt = $conn->prepare("SELECT id FROM avaliacoes WHERE id_pedido = ?");
$stmt->execute([$id_pedido]);
if ($stmt->fetch()) {
    header('Location: ' . BASEURL . 'minha_conta.php?ja_avaliado=1'); exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nota_produto = intval($_POST['nota_produto'] ?? 0);
    $nota_atend   = intval($_POST['nota_atend'] ?? 0);
    $comentario   = trim($_POST['comentario'] ?? '');

    if ($nota_produto < 1 || $nota_produto > 5 || $nota_atend < 1 || $nota_atend > 5) {
        $erro = "Por favor, selecione uma nota de 1 a 5 em cada item.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO avaliacoes (id_pedido, id_cliente, nota_produto, nota_atend, comentario)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$id_pedido, $_SESSION['id'], $nota_produto, $nota_atend, $comentario]);
        close_database($conn);
        header('Location: ' . BASEURL . 'minha_conta.php?avaliado=1'); exit;
    }
}

close_database($conn);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliar Pedido #<?php echo $id_pedido; ?> | Pedacinho de Amor</title>
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/style_pda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .star-group { display: flex; flex-direction: row-reverse; justify-content: center; gap: 6px; }
        .star-group input { display: none; }
        .star-group label {
            font-size: 2.4rem;
            color: #ddd;
            cursor: pointer;
            transition: color 0.15s;
        }
        .star-group label:hover,
        .star-group label:hover ~ label,
        .star-group input:checked ~ label {
            color: #f5a623;
        }
        .avaliacao-card {
            background: #fff;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .avaliacao-titulo {
            font-family: 'Comfortaa', cursive;
            color: #c9567b;
        }
        .nota-label {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: #555;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body class="bg-confeitaria">

<?php include '../inc/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

            <div class="avaliacao-card">

                <h3 class="avaliacao-titulo text-center mb-1">
                    <i class="fas fa-star me-2"></i>Avaliar Pedido
                </h3>
                <p class="text-center text-muted mb-4">Pedido <strong>#<?php echo $id_pedido; ?></strong></p>

                <?php if ($erro): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-circle-xmark me-2"></i><?php echo htmlspecialchars($erro); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">

                    <!-- NOTA DO PRODUTO -->
                    <div class="mb-4 text-center">
                        <p class="nota-label"><i class="fas fa-cake-candles me-2"></i>Qualidade do Produto</p>
                        <div class="star-group" id="grupo-produto">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="nota_produto" id="prod<?php echo $i; ?>" value="<?php echo $i; ?>">
                                <label for="prod<?php echo $i; ?>" title="<?php echo $i; ?> estrela<?php echo $i > 1 ? 's' : ''; ?>">
                                    <i class="fas fa-star"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- NOTA DO ATENDIMENTO -->
                    <div class="mb-4 text-center">
                        <p class="nota-label"><i class="fas fa-heart me-2"></i>Atendimento</p>
                        <div class="star-group" id="grupo-atend">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="nota_atend" id="atend<?php echo $i; ?>" value="<?php echo $i; ?>">
                                <label for="atend<?php echo $i; ?>" title="<?php echo $i; ?> estrela<?php echo $i > 1 ? 's' : ''; ?>">
                                    <i class="fas fa-star"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- COMENTÁRIO -->
                    <div class="mb-4">
                        <label class="nota-label" for="comentario">
                            <i class="fas fa-comment me-2"></i>Comentário <small class="text-muted fw-normal">(opcional)</small>
                        </label>
                        <textarea name="comentario" id="comentario" class="form-control" rows="3"
                            placeholder="Conte como foi sua experiência..."></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?php echo BASEURL; ?>minha_conta.php" class="btn btn-secondary w-50">
                            <i class="fas fa-arrow-left me-2"></i>Voltar
                        </a>
                        <button type="submit" class="btn btn-warning w-50 fw-bold">
                            <i class="fas fa-paper-plane me-2"></i>Enviar Avaliação
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<?php include '../inc/footer.php'; ?>

<script src="<?php echo BASEURL; ?>js/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>