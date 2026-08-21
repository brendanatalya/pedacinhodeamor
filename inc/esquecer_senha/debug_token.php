<?php
session_start();
require_once '../../config.php';
require_once DBAPI;

echo "<h1>🔍 DEBUG TOKEN</h1>";

$token = $_GET['token'] ?? '';

echo "<p><strong>Token recebido:</strong> $token</p>";

if (empty($token)) {
    echo "<p>❌ Token vazio!</p>";
    exit;
}

try {
    $database = open_database();

    // Busca o token EXATAMENTE como vem
    echo "<p>Procurando no banco...</p>";
    $sql = $database->prepare("
        SELECT id, nome, email, token_recuperacao, token_expiracao
        FROM usuarios 
        WHERE token_recuperacao = ?
    ");
    $sql->execute([$token]);
    $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);

    echo "<p>Resultados encontrados: " . count($resultado) . "</p>";

    if (count($resultado) > 0) {
        foreach ($resultado as $row) {
            echo "<p>✅ ENCONTRADO!</p>";
            echo "<p>Nome: " . $row['nome'] . "</p>";
            echo "<p>Email: " . $row['email'] . "</p>";
            echo "<p>Token BD: " . $row['token_recuperacao'] . "</p>";
            echo "<p>Token URL: " . $token . "</p>";
            echo "<p>São iguais? " . ($row['token_recuperacao'] === $token ? 'SIM ✅' : 'NÃO ❌') . "</p>";
            echo "<p>Expiração: " . $row['token_expiracao'] . "</p>";
            echo "<p>Agora: " . date('Y-m-d H:i:s') . "</p>";
            echo "<p>Expirado? " . (strtotime($row['token_expiracao']) < time() ? 'SIM ❌' : 'NÃO ✅') . "</p>";
        }
    } else {
        echo "<p>❌ Token NÃO encontrado no banco!</p>";
        
        // Lista todos os tokens do banco
        echo "<p><strong>Tokens no banco:</strong></p>";
        $sql2 = $database->prepare("SELECT id, nome, email, token_recuperacao FROM usuarios WHERE token_recuperacao IS NOT NULL");
        $sql2->execute();
        $todos = $sql2->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($todos as $row) {
            echo "<p>- " . $row['nome'] . ": " . $row['token_recuperacao'] . "</p>";
        }
    }

    close_database($database);

} catch (Exception $e) {
    echo "<p>❌ ERRO: " . $e->getMessage() . "</p>";
}
?>