<?php
// Arquivo de conexão com o banco MySQL (ajuste as credenciais conforme seu ambiente XAMPP)
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'pedacinho'; // altere para o nome do seu banco

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    // Não expor detalhes sensíveis em produção
    die('Falha na conexão com o banco de dados: ' . $conn->connect_error);
}
$conn->set_charset('utf8');

?>
