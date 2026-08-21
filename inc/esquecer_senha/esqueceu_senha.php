<?php
session_start();
require_once '../../config.php';
require_once DBAPI;

// Headers
header('Content-Type: application/json; charset=utf-8');

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Método não permitido']));
}

// Valida email
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);

if (!$email) {
    // Retorna sucesso mesmo assim (segurança - evita enumeração de usuários)
    http_response_code(200);
    exit(json_encode(['success' => true, 'message' => 'Se o e-mail estiver cadastrado, você receberá instruções de recuperação.']));
}

try {
    $database = open_database();

    // 1. Verifica se o email existe no banco
    $sql = $database->prepare("SELECT id, nome, email FROM usuarios WHERE email = ? LIMIT 1");
    $sql->execute([$email]);
    $usuario = $sql->fetch(PDO::FETCH_ASSOC);

    // Segurança: retorna sucesso mesmo se email não existir
    if (!$usuario) {
        close_database($database);
        http_response_code(200);
        exit(json_encode(['success' => true, 'message' => 'Se o e-mail estiver cadastrado, você receberá instruções de recuperação.']));
    }

    // 2. Gera token único (32 bytes = 64 caracteres em hex)
    $token = bin2hex(random_bytes(32));
    $expiracao = date('Y-m-d H:i:s', strtotime('+2 hours'));

    // 3. Salva token no banco
    $update = $database->prepare("UPDATE usuarios SET token_recuperacao = ?, token_expiracao = ? WHERE id = ?");
    $update->execute([$token, $expiracao, $usuario['id']]);

    close_database($database);

    // 4. Prepara PHPMailer
    require_once '../phpmailer/Exception.php';
    require_once '../phpmailer/PHPMailer.php';
    require_once '../phpmailer/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer();

    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // Remetente
    $mail->setFrom(SMTP_USER, 'Pedacinho de Amor');
    $mail->addAddress($email);

    // Monta URL absoluta do sistema a partir da configuração global
    $linkRecuperacao = APP_URL . 'inc/esquecer_senha/redefinir_senha.php?token=' . urlencode($token);

    // 5. Conteúdo do email
    $mail->isHTML(true);
    $mail->Subject = '🔑 Recuperação de Senha - Pedacinho de Amor';
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; color: #333;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h2 style='color: #7a2f2f; margin: 0;'>🔑 Recuperação de Senha</h2>
            </div>
            
            <p>Olá <strong>" . htmlspecialchars($usuario['nome']) . "</strong>,</p>
            <p>Recebemos uma solicitação para redefinir sua senha na <strong>Pedacinho de Amor</strong>.</p>
            
            <p style='margin: 30px 0; text-align: center;'>
                <a href='" . htmlspecialchars($linkRecuperacao) . "' style='display: inline-block; background: #f5a623; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; font-weight: bold;'>
                    Redefinir Senha
                </a>
            </p>
            
            <p style='font-size: 0.9rem; color: #666;'>
                <strong>Ou copie e cole este link no seu navegador:</strong><br>
                <code style='background: #f5f5f5; padding: 8px; border-radius: 3px; display: block; word-break: break-all; margin-top: 8px; font-size: 0.85rem;'>" . htmlspecialchars($linkRecuperacao) . "</code>
            </p>
            
            <hr style='border: none; border-top: 1px solid #ddd; margin: 30px 0;'>
            
            <p style='font-size: 0.85rem; color: #999;'>
                ⏰ <strong>Este link é válido por 2 horas.</strong><br>
                Se você não solicitou esta alteração, ignore este e-mail e sua senha permanecerá segura.
            </p>
            
            <p style='text-align: center; font-size: 0.85rem; color: #999; margin-top: 30px;'>
                💝 Pedacinho de Amor
            </p>
        </div>
    ";

    $mail->send();
    
    http_response_code(200);
    exit(json_encode(['success' => true, 'message' => 'Se o e-mail estiver cadastrado, você receberá instruções de recuperação.']));

} catch (Exception $e) {
    // Log do erro
    error_log('Erro ao enviar email de recuperação: ' . $e->getMessage());
    
    // Retorna sucesso mesmo assim (segurança)
    http_response_code(200);
    exit(json_encode(['success' => true, 'message' => 'Se o e-mail estiver cadastrado, você receberá instruções de recuperação.']));
}
?>