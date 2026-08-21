<?php
session_start();
require_once '../../config.php';
require_once DBAPI;
include HEADER_TEMPLATE;

// Variaveis de status
$token = $_GET['token'] ?? '';
$erro = '';
$sucesso = false;
$token_valido = false;

// 1. Se GET com token, valida o token
if (!empty($token) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $database = open_database();

        // Busca usuario com este token e valida expiracao
        $sql = $database->prepare("
            SELECT id, email, nome
            FROM usuarios 
            WHERE token_recuperacao = ? 
            AND token_expiracao > NOW() 
            LIMIT 1
        ");
        
        if (!$sql->execute([$token])) {
            throw new Exception("Erro ao executar query: " . implode(", ", $sql->errorInfo()));
        }
        
        $usuario = $sql->fetch(PDO::FETCH_ASSOC);
        close_database($database);

        if (!$usuario) {
            $erro = 'Link de recuperação inválido ou expirado. Solicite um novo.';
        } else {
            $token_valido = true;
        }
    } catch (Exception $e) {
        $erro = 'Erro ao validar token: ' . $e->getMessage();
        error_log($e->getMessage());
    }
}

// 2. Se POST, processa a redefinição de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    // Validações
    if (empty($token)) {
        $erro = 'Token não fornecido.';
    } elseif (empty($nova_senha) || empty($confirmar_senha)) {
        $erro = 'Preencha todos os campos.';
    } elseif (strlen($nova_senha) < 8) {
        $erro = 'A senha deve ter no mínimo 8 caracteres.';
    } elseif ($nova_senha !== $confirmar_senha) {
        $erro = 'As senhas não coincidem.';
    } else {
        try {
            $database = open_database();

            // Valida token novamente
            $sql = $database->prepare("
                SELECT id 
                FROM usuarios 
                WHERE token_recuperacao = ? 
                AND token_expiracao > NOW() 
                LIMIT 1
            ");
            
            if (!$sql->execute([$token])) {
                throw new Exception("Erro ao validar token: " . implode(", ", $sql->errorInfo()));
            }
            
            $usuario = $sql->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                $erro = 'Link de recuperação inválido ou expirado.';
            } else {
                // Hash da nova senha
                $novo_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

                // Atualiza senha e limpa token
                $update = $database->prepare("
                    UPDATE usuarios 
                    SET senha = ?, 
                        token_recuperacao = NULL, 
                        token_expiracao = NULL 
                    WHERE id = ?
                ");
                
                if (!$update->execute([$novo_hash, $usuario['id']])) {
                    throw new Exception("Erro ao atualizar senha: " . implode(", ", $update->errorInfo()));
                }

                $sucesso = true;
            }
            
            close_database($database);
        } catch (Exception $e) {
            $erro = 'Erro ao redefinir senha: ' . $e->getMessage();
            error_log($e->getMessage());
        }
    }
}
?>

<div style="min-height: 60vh; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
    <div style="width: 100%; max-width: 450px; padding: 20px;">

        <?php if ($sucesso): ?>
            <!-- SUCESSO -->
            <div style="background: #e0f4de; border: 1px solid #2e6930; border-radius: 8px; padding: 30px; text-align: center;">
                <h2 style="color: #2e6930; margin-bottom: 15px;">✅ Senha Redefinida!</h2>
                <p style="color: #555; margin-bottom: 25px;">
                    Sua senha foi alterada com sucesso.<br>
                    Você pode fazer login com sua nova senha.
                </p>
                <a href="<?php echo BASEURL; ?>index.php" class="btn-enviar" style="display: inline-block;">
                    Voltar para Home
                </a>
            </div>

        <?php elseif ($token_valido): ?>
            <!-- FORMULARIO DE REDEFINIÇÃO -->
            <div class="auth-card">
                <div class="auth-header">
                    <h2>🔑 Redefinir Senha</h2>
                </div>
                <div class="auth-body">
                    <?php if (!empty($erro)): ?>
                        <div style="background: #ffe0e0; border: 1px solid #ff4d4d; color: #ff4d4d; padding: 12px; border-radius: 4px; margin-bottom: 20px; text-align: center;">
                            <?php echo htmlspecialchars($erro); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                        <div class="input-group-auth">
                            <input 
                                type="password" 
                                name="nova_senha" 
                                id="novaSenha" 
                                placeholder="Nova senha (mín. 8 caracteres)" 
                                required
                                minlength="8"
                            >
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('novaSenha', this)"></i>
                        </div>

                        <div class="input-group-auth">
                            <input 
                                type="password" 
                                name="confirmar_senha" 
                                id="confirmarSenha" 
                                placeholder="Confirmar nova senha" 
                                required
                                minlength="8"
                            >
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('confirmarSenha', this)"></i>
                        </div>

                        <button type="submit" class="btn-enviar">Redefinir Senha</button>
                    </form>

                    <p style="text-align: center; font-size: 0.9rem; color: #666; margin-top: 15px;">
                        Lembrou a senha? <a href="<?php echo BASEURL; ?>index.php" style="color: #f5a623; text-decoration: none;">Fazer login</a>
                    </p>
                </div>
            </div>

        <?php else: ?>
            <!-- LINK INVÁLIDO OU EXPIRADO -->
            <div style="background: #ffe0e0; border: 1px solid #ff4d4d; border-radius: 8px; padding: 30px; text-align: center;">
                <h2 style="color: #ff4d4d; margin-bottom: 15px;">❌ Link Inválido</h2>
                <p style="color: #555; margin-bottom: 25px;">
                    <?php echo htmlspecialchars($erro ?: 'Este link de recuperação não é válido ou já expirou.'); ?>
                </p>
                <a href="<?php echo BASEURL; ?>index.php" class="btn-enviar" style="background: #ff4d4d; display: inline-block;">
                    Voltar para Home
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>

<?php include FOOTER_TEMPLATE; ?>