<?php
// salvar.php - insere novo horário na tabela `agenda`
include_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: agenda.php');
    exit;
}

$data = $_POST['data'] ?? '';
$hora = $_POST['hora'] ?? '';
$status = $_POST['status'] ?? '';

// validações simples
if (empty($data) || empty($hora) || empty($status)) {
    // poderia redirecionar com mensagem de erro
    header('Location: agenda.php');
    exit;
}

$stmt = $conn->prepare('INSERT INTO agenda (`data`, `hora`, `status`) VALUES (?, ?, ?)');
if ($stmt) {
    $stmt->bind_param('sss', $data, $hora, $status);
    $stmt->execute();
    $stmt->close();
}

header('Location: agenda.php');
exit;

?>
