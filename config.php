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
define('GOOGLE_REDIRECT_URI', 'http://localhost/pedacinhodeamor/inc/google/google_callback.php');// Substitua pelo URL de redirecionamento correto, no caso dominio real
 
// ===== CONFIGURAÇÃO SMTP PARA RECUPERAÇÃO DE SENHA =====
define('SMTP_HOST', 'sandbox.smtp.mailtrap.io');
define('SMTP_USER', 'fe1c8b3e9cae63');        // ← SEU EMAIL DO GMAIL
define('SMTP_PASS', '912f11fb3dea83');      // ← APP PASSWORD (16 caracteres)

?>