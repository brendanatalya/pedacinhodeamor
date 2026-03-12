<?php
// login.php - processa autenticação
session_start();
include_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html'); exit;
}

$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    $msg = urlencode('Preencha email e senha.');
    header("Location: login.html?msg={$msg}&type=login&status=err"); exit;
}

$stmt = $conn->prepare('SELECT id, name, password_hash FROM users WHERE email = ? LIMIT 1');
if (!$stmt) {
    $msg = urlencode('Erro interno.');
    header("Location: login.html?msg={$msg}&type=login&status=err"); exit;
}
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password_hash'])) {
    $msg = urlencode('Email ou senha inválidos.');
    header("Location: login.html?msg={$msg}&type=login&status=err"); exit;
}

// Autenticado
$_SESSION['user'] = ['id' => $user['id'], 'name' => $user['name'], 'email' => $email];
$msg = urlencode("Bem-vindo, {$user['name']}!");
header("Location: index.html?login=ok&msg={$msg}");
exit;

?>
