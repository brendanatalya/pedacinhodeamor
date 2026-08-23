<?php
session_start();
require_once '../../config.php';
require_once DBAPI;
include HEADER_TEMPLATE;

$token = $_GET['token'] ?? '';
$erro = '';
$sucesso = false;
$token_valido = false;
$usuario_info = null;

// VALIDA TOKEN NO GET
if (!empty($token) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $database = open_database();
        
        $sql = $database->prepare("
            SELECT id, email, nome, token_recuperacao, token_expiracao
            FROM usuarios 
            WHERE token_recuperacao = ?
            LIMIT 1
        ");
        
        $sql->execute([$token]);
        $usuario_info = $sql->fetch(PDO::FETCH_ASSOC);
        
        close_database($database);

        if (!$usuario_info) {
            $erro = 'Link inválido ou não encontrado.';
        } elseif (strtotime($usuario_info['token_expiracao']) < time()) {
            $erro = 'Link expirado. Solicite um novo.';
        } else {
            $token_valido = true;
        }
    } catch (Exception $e) {
        $erro = 'Erro: ' . $e->getMessage();
    }
}

// PROCESSA REDEFINIÇÃO NO POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_post = $_POST['token'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    // Primeiro: valida o token
    try {
        $database = open_database();

        $sql = $database->prepare("
            SELECT id, token_recuperacao, token_expiracao
            FROM usuarios 
            WHERE token_recuperacao = ? 
            LIMIT 1
        ");
        
        $sql->execute([$token_post]);
        $usuario_dados = $sql->fetch(PDO::FETCH_ASSOC);

        if (!$usuario_dados) {
            $erro = 'Token não encontrado.';
            $token_valido = false;
        } elseif (strtotime($usuario_dados['token_expiracao']) < time()) {
            $erro = 'Token expirado. Solicite um novo.';
            $token_valido = false;
        } else {
            // Token OK! Agora valida a senha
            $token_valido = true;
            $token = $token_post; // Atualiza o token para manter no formulário
            
            if (empty($nova_senha) || empty($confirmar_senha)) {
                $erro = 'Preencha todos os campos.';
            } elseif (strlen($nova_senha) < 8) {
                $erro = 'A senha deve ter no mínimo 8 caracteres.';
            } elseif (!preg_match('/[A-Z]/', $nova_senha)) {
                $erro = 'A senha deve conter pelo menos uma LETRA MAIÚSCULA.';
            } elseif (!preg_match('/[0-9]/', $nova_senha)) {
                $erro = 'A senha deve conter pelo menos um NÚMERO.';
            } elseif (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $nova_senha)) {
                $erro = 'A senha deve conter pelo menos um CARACTERE ESPECIAL (!@#$%^&* etc).';
            } elseif ($nova_senha !== $confirmar_senha) {
                $erro = 'As senhas não coincidem.';
            } else {
                // Se chegou aqui, tudo é válido - atualiza no banco
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                
                $update = $database->prepare("
                    UPDATE usuarios 
                    SET senha = ?, token_recuperacao = NULL, token_expiracao = NULL 
                    WHERE id = ?
                ");
                
                if ($update->execute([$senha_hash, $usuario_dados['id']])) {
                    $sucesso = true;
                } else {
                    $erro = 'Erro ao salvar nova senha. Tente novamente.';
                }
            }
        }
        
        close_database($database);
    } catch (Exception $e) {
        $erro = 'Erro: ' . htmlspecialchars($e->getMessage());
        $token_valido = false;
    }
}
?>

<style>
body { font-family: Arial, sans-serif; }
.reset-container { min-height: 60vh; display: flex; align-items: center; justify-content: center; background: #f8f9fa; padding: 20px; }
.reset-card { width: 100%; max-width: 450px; background: white; border-radius: 8px; padding: 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.reset-header h2 { color: #7a2f2f; margin: 0 0 20px 0; text-align: center; }
.input-group { margin-bottom: 20px; position: relative; }
.input-group input { width: 100%; padding: 12px 40px 12px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
.input-group input:focus { outline: none; border-color: #f5a623; }
.toggle-eye { position: absolute; right: 12px; top: 12px; cursor: pointer; color: #999; }
.btn-submit { width: 100%; padding: 12px; background: #f5a623; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; }
.btn-submit:hover { background: #e09400; }
.error { background: #ffe0e0; border: 1px solid #ff4d4d; color: #ff4d4d; padding: 12px; border-radius: 4px; margin-bottom: 20px; }
.success { background: #e0f4de; border: 1px solid #2e6930; color: #2e6930; padding: 30px; border-radius: 8px; text-align: center; }
.success a { display: inline-block; background: #2e6930; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; margin-top: 15px; }
.info-text { text-align: center; font-size: 0.9rem; color: #666; margin-top: 15px; }
.info-text a { color: #f5a623; text-decoration: none; }
</style>

<div class="reset-container">
    <div class="reset-card">

        <?php if ($sucesso): ?>
            <div class="success">
                <h2>✅ Senha Redefinida!</h2>
                <p>Sua senha foi alterada com sucesso.</p>
                <a href="<?php echo BASEURL; ?>index.php">Fazer Login</a>
            </div>

        <?php elseif ($token_valido): ?>
            <div class="reset-header">
                <h2>🔑 Redefinir Senha</h2>
            </div>

            <?php if (!empty($erro)): ?>
                <div class="error"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <div class="input-group">
                    <input type="password" name="nova_senha" id="novaSenha" placeholder="Nova senha" required minlength="8" oninput="validarSenha()">
                    <span class="toggle-eye" onclick="togglePassword('novaSenha', this)">👁️</span>
                </div>

                <!-- Requisitos de Senha -->
                <div id="requisitos" style="background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px; padding: 12px; margin-bottom: 20px; font-size: 13px;">
                    <p style="margin: 0 0 8px 0; font-weight: bold; color: #333;">Requisitos:</p>
                    <p id="req-chars" style="margin: 4px 0; color: #999;">❌ Mínimo 8 caracteres</p>
                    <p id="req-upper" style="margin: 4px 0; color: #999;">❌ Uma letra MAIÚSCULA (A-Z)</p>
                    <p id="req-number" style="margin: 4px 0; color: #999;">❌ Um número (0-9)</p>
                    <p id="req-special" style="margin: 4px 0; color: #999;">❌ Um caractere especial (!@#$%^&*)</p>
                </div>

                <div class="input-group">
                    <input type="password" name="confirmar_senha" id="confirmarSenha" placeholder="Confirmar senha" required minlength="8">
                    <span class="toggle-eye" onclick="togglePassword('confirmarSenha', this)">👁️</span>
                </div>

                <button type="submit" class="btn-submit" id="btnSubmit">Redefinir Senha</button>
            </form>

            <p class="info-text">
                Lembrou a senha? <a href="<?php echo BASEURL; ?>index.php">Fazer login</a>
            </p>

        <?php else: ?>
            <div style="background: #ffe0e0; border: 1px solid #ff4d4d; border-radius: 8px; padding: 30px; text-align: center;">
                <h2 style="color: #ff4d4d; margin: 0 0 15px 0;">❌ Link Inválido</h2>
                <p style="color: #555; margin: 0 0 20px 0;"><?php echo htmlspecialchars($erro ?: 'Este link não é válido ou expirou.'); ?></p>
                <a href="<?php echo BASEURL; ?>index.php" style="display: inline-block; background: #f5a623; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none;">Voltar</a>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);
    if (!input) return;
    input.type = input.type === "password" ? "text" : "password";
    icon.textContent = input.type === "password" ? "👁️" : "🙈";
}

function validarSenha() {
    const senha = document.getElementById('novaSenha').value;
    const btnSubmit = document.getElementById('btnSubmit');
    
    // Validações
    const temChars = senha.length >= 8;
    const temMaiuscula = /[A-Z]/.test(senha);
    const temNumero = /[0-9]/.test(senha);
    const temEspecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(senha);
    
    // Atualizar visuais
    atualizarRequisito('req-chars', temChars);
    atualizarRequisito('req-upper', temMaiuscula);
    atualizarRequisito('req-number', temNumero);
    atualizarRequisito('req-special', temEspecial);
    
    // Ativar/desativar botão
    const todosValidos = temChars && temMaiuscula && temNumero && temEspecial;
    btnSubmit.disabled = !todosValidos;
    btnSubmit.style.opacity = todosValidos ? '1' : '0.5';
    btnSubmit.style.cursor = todosValidos ? 'pointer' : 'not-allowed';
}

function atualizarRequisito(id, valido) {
    const elemento = document.getElementById(id);
    if (valido) {
        elemento.style.color = '#2e6930';
        elemento.textContent = elemento.textContent.replace('❌', '✅');
    } else {
        elemento.style.color = '#999';
        elemento.textContent = elemento.textContent.replace('✅', '❌');
    }
}

// Inicializar validação ao carregar
document.addEventListener('DOMContentLoaded', validarSenha);
</script>

<?php
// NÃO INCLUI O FOOTER PARA EVITAR ERROS DO CARROSSEL
// include FOOTER_TEMPLATE;
?>