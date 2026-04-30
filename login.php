
<?php
session_start();
include_once 'conexao.php';

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

// Validação básica
if (!$email || !$password) {
    $msg = urlencode('Preencha email e senha.');
    header("Location: index.html?status=err&msg={$msg}");
    exit;
}

// Busca usuário
$stmt = $conn->prepare('SELECT id, name, password_hash FROM users WHERE email = ? LIMIT 1');

if (!$stmt) {
    $msg = urlencode('Erro interno no servidor.');
    header("Location: index.html?status=err&msg={$msg}");
    exit;
}

$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

// Verifica login
if (!$user || !password_verify($password, $user['password_hash'])) {
    $msg = urlencode('Email ou senha inválidos.');
    header("Location: index.html?status=err&msg={$msg}");
    exit;
}

// Login OK
$_SESSION['user'] = [
    'id' => $user['id'],
    'name' => $user['name'],
    'email' => $email
];

$msg = urlencode("Bem-vindo, {$user['name']}!");
header("Location: index.html?status=ok&msg={$msg}");
exit;
?>