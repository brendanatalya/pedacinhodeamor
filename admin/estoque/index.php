<?php
if (!isset($_SESSION)) session_start();

if (empty($_SESSION['logado']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: ' . dirname(dirname(dirname(__DIR__))) . '/index.php');
    exit;
}

include dirname(__DIR__, 2) . '/config.php';
require_once(DBAPI);

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    try {
        $conn = open_database();

        if ($acao === 'adicionar') {
            $stmt = $conn->prepare("
                INSERT INTO estoque_ingredientes (nome, unidade, qtd_estoque, qtd_minima)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['nome'],
                $_POST['unidade'],
                $_POST['qtd_estoque'],
                $_POST['qtd_minima']
            ]);
            $mensagem = 'Ingrediente adicionado com sucesso!';
            $tipo_mensagem = 'success';
        }

        elseif ($acao === 'editar') {
            $stmt = $conn->prepare("
                UPDATE estoque_ingredientes
                SET nome = ?, unidade = ?, qtd_estoque = ?, qtd_minima = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $_POST['nome'],
                $_POST['unidade'],
                $_POST['qtd_estoque'],
                $_POST['qtd_minima'],
                $_POST['id']
            ]);
            $mensagem = 'Ingrediente atualizado com sucesso!';
            $tipo_mensagem = 'success';
        }

        elseif ($acao === 'deletar') {
            $stmt = $conn->prepare("DELETE FROM estoque_ingredientes WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $mensagem = 'Ingrediente removido!';
            $tipo_mensagem = 'success';
        }

        close_database($conn);
    } catch (Exception $e) {
        $mensagem = 'Erro: ' . $e->getMessage();
        $tipo_mensagem = 'danger';
    }
}

// Buscar ingrediente para edição
$ingrediente_edicao = null;
if (isset($_GET['editar'])) {
    $conn = open_database();
    $stmt = $conn->prepare("SELECT * FROM estoque_ingredientes WHERE id = ?");
    $stmt->execute([$_GET['editar']]);
    $ingrediente_edicao = $stmt->fetch(PDO::FETCH_ASSOC);
    close_database($conn);
}

// Buscar todos ingredientes
$conn = open_database();
$stmt = $conn->prepare("SELECT * FROM estoque_ingredientes ORDER BY nome ASC");
$stmt->execute();
$ingredientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
close_database($conn);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estoque - Admin</title>
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/style_pda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background-color: #f8f9fa;">
<div class="container-fluid p-4">

    <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show">
            <?php echo htmlspecialchars($mensagem); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">

        <!-- FORMULÁRIO -->
        <div class="col-md-4">
            <div class="form-section">
                <h5><?php echo $ingrediente_edicao ? 'Editar Ingrediente' : 'Adicionar Ingrediente'; ?></h5>
                <form method="POST">
                    <input type="hidden" name="acao" value="<?php echo $ingrediente_edicao ? 'editar' : 'adicionar'; ?>">
                    <?php if ($ingrediente_edicao): ?>
                        <input type="hidden" name="id" value="<?php echo $ingrediente_edicao['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" required
                            value="<?php echo $ingrediente_edicao ? htmlspecialchars($ingrediente_edicao['nome']) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Unidade</label>
                        <select name="unidade" class="form-control" required>
                            <?php foreach (['g', 'kg', 'ml', 'l', 'un'] as $u): ?>
                                <option value="<?php echo $u; ?>"
                                    <?php echo ($ingrediente_edicao && $ingrediente_edicao['unidade'] === $u) ? 'selected' : ''; ?>>
                                    <?php echo $u; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantidade em Estoque</label>
                        <input type="number" name="qtd_estoque" class="form-control" step="0.001" min="0" required
                            value="<?php echo $ingrediente_edicao ? $ingrediente_edicao['qtd_estoque'] : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantidade Mínima <small class="text-muted">(alerta de estoque baixo)</small></label>
                        <input type="number" name="qtd_minima" class="form-control" step="0.001" min="0" required
                            value="<?php echo $ingrediente_edicao ? $ingrediente_edicao['qtd_minima'] : ''; ?>">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-save"></i>
                        <?php echo $ingrediente_edicao ? 'Atualizar' : 'Adicionar'; ?>
                    </button>

                    <?php if ($ingrediente_edicao): ?>
                        <a href="<?php echo BASEURL; ?>admin/estoque/" class="btn btn-secondary w-100">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- LISTA -->
        <div class="col-md-8">
            <div class="form-section">
                <h5>Ingredientes em Estoque (<?php echo count($ingredientes); ?>)</h5>

                <?php
                $alertas = array_filter($ingredientes, fn($i) => $i['qtd_estoque'] <= $i['qtd_minima']);
                if (!empty($alertas)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Estoque baixo:</strong>
                        <?php echo implode(', ', array_column($alertas, 'nome')); ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nome</th>
                                <th>Unidade</th>
                                <th>Qtd Estoque</th>
                                <th>Qtd Mínima</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ingredientes as $i): ?>
                                <?php $baixo = $i['qtd_estoque'] <= $i['qtd_minima']; ?>
                                <tr class="<?php echo $baixo ? 'table-warning' : ''; ?>">
                                    <td><small>#<?php echo $i['id']; ?></small></td>
                                    <td><strong><?php echo htmlspecialchars($i['nome']); ?></strong></td>
                                    <td><?php echo $i['unidade']; ?></td>
                                    <td class="<?php echo $baixo ? 'text-danger fw-bold' : ''; ?>">
                                        <?php echo number_format($i['qtd_estoque'], 3, ',', '.'); ?>
                                    </td>
                                    <td><?php echo number_format($i['qtd_minima'], 3, ',', '.'); ?></td>
                                    <td>
                                        <?php if ($baixo): ?>
                                            <span class="badge bg-danger rounded-pill">Baixo</span>
                                        <?php else: ?>
                                            <span class="badge bg-success rounded-pill">OK</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?editar=<?php echo $i['id']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Remover este ingrediente?')">
                                            <input type="hidden" name="acao" value="deletar">
                                            <input type="hidden" name="id" value="<?php echo $i['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($ingredientes)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-box-open me-2"></i> Nenhum ingrediente cadastrado.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASEURL; ?>js/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>