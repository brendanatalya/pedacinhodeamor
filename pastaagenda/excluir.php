<?php
// excluir.php - exclui registro por id
include_once 'conexao.php';

if (!isset($_GET['id'])) {
    header('Location: agenda.php');
    exit;
}

$id = $_GET['id'];
if (!ctype_digit($id)) {
    header('Location: agenda.php');
    exit;
}

$stmt = $conn->prepare('DELETE FROM agenda WHERE id = ?');
if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}

header('Location: agenda.php');
exit;

?>
