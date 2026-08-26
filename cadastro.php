<?php
if (!isset($_SESSION)) session_start();
require_once 'config.php';
require_once ABSPATH . 'inc/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$email_confirm = strtolower(trim($_POST['email_confirm'] ?? ''));
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

if (!$name || !$email || !$email_confirm || !$password || !$password_confirm) {
    echo json_encode([
        'success' => false,
        'message' => 'Preencha todos os campos do cadastro.'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Digite um e-mail válido.'
    ]);
    exit;
}

if ($email !== $email_confirm) {
    echo json_encode([
        'success' => false,
        'message' => 'Os e-mails não coincidem.'
    ]);
    exit;
}

if ($password !== $password_confirm) {
    echo json_encode([
        'success' => false,
        'message' => 'As senhas não coincidem.'
    ]);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode([
        'success' => false,
        'message' => 'A senha deve ter no mínimo 8 caracteres.'
    ]);
    exit;
}

if (!preg_match('/[A-Z]/', $password)) {
    echo json_encode([
        'success' => false,
        'message' => 'A senha deve conter pelo menos uma LETRA MAIÚSCULA.'
    ]);
    exit;
}

if (!preg_match('/[0-9]/', $password)) {
    echo json_encode([
        'success' => false,
        'message' => 'A senha deve conter pelo menos um NÚMERO.'
    ]);
    exit;
}

if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
    echo json_encode([
        'success' => false,
        'message' => 'A senha deve conter pelo menos um CARACTERE ESPECIAL (!@#$%^&* etc).'
    ]);
    exit;
}

$database = open_database();
if (!$database) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao conectar ao banco de dados.'
    ]);
    exit;
}

try {
    $check = $database->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
    $check->execute([$email]);

    if ($check->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Este e-mail já está cadastrado. Faça login ou use outro e-mail.'
        ]);
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $database->prepare('INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)');
    $insert->execute([$name, $email, $password_hash, 'cliente']);

    $_SESSION['id'] = $database->lastInsertId();
    $_SESSION['nome'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['tipo'] = 'cliente';
    $_SESSION['logado'] = true;

    echo json_encode([
        'success' => true,
        'message' => 'Cadastro realizado com sucesso! Você já está logado.'
    ]);
    exit;
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao cadastrar: ' . $e->getMessage()
    ]);
    exit;
}

