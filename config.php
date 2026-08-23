<?php

/** O nome do banco de dados */
define("DB_NAME", "pedacinhodeamor");

/** Usuário do banco de dados MySQL */
define('DB_USER', 'root');

/** Senha do banco de dados MySQL */
define('DB_PASSWORD', '');

/** nome do host do MySQL */
define('DB_HOST', 'localhost');

/** DSN para conexão PDO */
define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8');

/** caminho absoluto para a pasta do sistema **/
if (!defined('ABSPATH'))
    define('ABSPATH', dirname(__FILE__) . '/');

/** caminho no server para o sistema **/
if (!defined('BASEURL'))
    define('BASEURL', '/pedacinhodeamor/');

/** URL absoluta do sistema para links em e-mails e redirects **/
if (!defined('APP_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('APP_URL', $protocol . '://' . $host . BASEURL);
}

/** caminho do arquivo de banco de dados **/
if (!defined('DBAPI'))
    define('DBAPI', ABSPATH . 'inc/database.php');

/** caminhos dos templates de header e footer **/
define('HEADER_TEMPLATE', ABSPATH . 'inc/header.php');
define('FOOTER_TEMPLATE', ABSPATH . 'inc/footer.php');

/** número WhatsApp da confeitaria (só números, com DDI) **/
define('WHATSAPP_NUMBER', '5515988329726');

// Credenciais do Google Cloud Console
define('GOOGLE_CLIENT_ID', '1040096064250-n7smmpsm97r6u31kk28rss8ugfpmkvtl.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'http://localhost/pedacinhodeamor/inc/google/google_callback.php');

// ===== SMTP CONFIGURATION (Carrega do .env se existir) =====
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    define('SMTP_HOST', $env['SMTP_HOST'] ?? 'smtp.gmail.com');
    define('SMTP_USER', $env['SMTP_USER'] ?? '');
    define('SMTP_PASS', $env['SMTP_PASS'] ?? '');
    define('SMTP_PORT', $env['SMTP_PORT'] ?? 587);
    define('SMTP_SECURE', $env['SMTP_SECURE'] ?? 'tls');
} else {
    // Fallback com valores padrão (ou deixa vazio)
    define('SMTP_HOST', 'smtp.gmail.com');
    define('SMTP_USER', ''); // Deixa vazio aqui, não coloca no código!
    define('SMTP_PASS', '');
    define('SMTP_PORT', 587);
    define('SMTP_SECURE', 'tls');
}

?>