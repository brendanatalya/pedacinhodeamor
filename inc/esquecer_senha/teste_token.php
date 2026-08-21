<?php
$token = $_GET['token'] ?? 'VAZIO';
echo "Token recebido: " . htmlspecialchars($token);
echo "<br>Data: " . date('Y-m-d H:i:s');
?>