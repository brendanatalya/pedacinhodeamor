<?php
// Carrega conexão se existir, senão alerta ao usuário
if (!file_exists(__DIR__ . '/conexao.php')) {
    echo "<p style='color:red; text-align:center;'>Arquivo de conexão 'conexao.php' não encontrado. Crie o arquivo com as credenciais do seu banco (veja conexao.php.template).</p>";
    exit;
}
include_once 'conexao.php';
if (!isset($conn) || $conn === null) {
    echo "<p style='color:red; text-align:center;'>Erro: conexão com o banco não inicializada. Verifique 'conexao.php'.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Agenda Simples</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        h1 { text-align: center; }
        table { width: 60%; margin: auto; border-collapse: collapse; background: white; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        .disponivel { background: #c8f7c5; }
        .indisponivel { background: #f7c5c5; }
        form { text-align: center; margin-bottom: 20px; }
        input, select { padding: 5px; }
    </style>
</head>
<body>

<h1>Agenda</h1>

<!-- Formulário para adicionar horário -->
<form method="POST" action="salvar.php">
    Data: <input type="date" name="data" required>
    Hora: <input type="time" name="hora" required>
    Status:
    <select name="status">
        <option value="Disponível">Disponível</option>
        <option value="Indisponível">Indisponível</option>
    </select>
    <button type="submit">Adicionar</button>
</form>

<!-- Lista de horários -->
<table>
    <tr>
        <th>Data</th>
        <th>Hora</th>
        <th>Status</th>
        <th>Ação</th>
    </tr>
<?php
// Buscar registros
$result = $conn->query("SELECT * FROM agenda ORDER BY data, hora");
if ($result === false) {
    echo "<tr><td colspan='4' style='color:red'>Erro na consulta ao banco.</td></tr>";
} elseif ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Normaliza a classe a partir do status (evita espaços/acentos problemáticos)
        $statusLower = mb_strtolower($row['status']);
        $classe = (strpos($statusLower, 'dispon') !== false) ? 'disponivel' : 'indisponivel';
        echo "<tr class='" . $classe . "'>";
        echo "<td>" . htmlspecialchars($row['data']) . "</td>";
        echo "<td>" . htmlspecialchars($row['hora']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>\n";
        echo "<a href='alterar.php?id=" . urlencode($row['id']) . "'>Alterar</a> | ";
        echo "<a href='excluir.php?id=" . urlencode($row['id']) . "' onclick='return confirm(\"Excluir este horário?\")'>Excluir</a>";
        echo "</td>";
        echo "</tr>\n";
    }
} else {
    echo "<tr><td colspan='4'>Nenhum horário encontrado.</td></tr>";
}
?>
</table>

</body>
</html>
