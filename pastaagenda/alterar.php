<?php
// alterar.php - exibe formulário para editar e processa atualização
include_once 'conexao.php';

// Processa atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $data = $_POST['data'] ?? '';
    $hora = $_POST['hora'] ?? '';
    $status = $_POST['status'] ?? '';

    if (!ctype_digit($id) || empty($data) || empty($hora) || empty($status)) {
        header('Location: agenda.php');
        exit;
    }

    $stmt = $conn->prepare('UPDATE agenda SET `data` = ?, `hora` = ?, `status` = ? WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('sssi', $data, $hora, $status, $id);
        $stmt->execute();
        $stmt->close();
    }

    header('Location: agenda.php');
    exit;
}

// Mostrar formulário com valores atuais
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: agenda.php');
    exit;
}

$id = $_GET['id'];
$stmt = $conn->prepare('SELECT id, `data`, `hora`, `status` FROM agenda WHERE id = ?');
if (!$stmt) {
    header('Location: agenda.php');
    exit;
}
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
    header('Location: agenda.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Alterar Horário</title>
    <style>body{font-family:Arial,Helvetica,sans-serif;padding:1rem}label{display:block;margin-top:.5rem}input,select{padding:.4rem;width:200px}</style>
</head>
<body>
    <h1>Alterar Horário</h1>
    <form method="POST" action="alterar.php">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
        <label>Data: <input type="date" name="data" value="<?php echo htmlspecialchars($row['data']); ?>" required></label>
        <label>Hora: <input type="time" name="hora" value="<?php echo htmlspecialchars($row['hora']); ?>" required></label>
        <label>Status:
            <select name="status">
                <option value="Disponível" <?php echo ($row['status']==='Disponível')?'selected':''; ?>>Disponível</option>
                <option value="Indisponível" <?php echo ($row['status']==='Indisponível')?'selected':''; ?>>Indisponível</option>
            </select>
        </label>
        <div style="margin-top:.8rem"><button type="submit">Salvar</button> <a href="agenda.php">Cancelar</a></div>
    </form>
</body>
</html>
