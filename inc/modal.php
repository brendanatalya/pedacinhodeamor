<!-- MODAL -->
<div class="modal fade" id="modalLogin" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content p-0 border-0">

<div class="auth-card">

<div class="auth-header">
    <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close">&times;</button>
    <h2 id="titulo">LOGIN</h2>
</div>
<div class="auth-body">
    <form id="loginForm" action="inc/valida.php" method="POST">
        <input type="email" name="email" placeholder="E-mail" required>
        
        <div class="input-group-auth">
            <input type="password" name="senha" id="passLogin" placeholder="Senha" required>
            <i class="fas fa-eye toggle-password" onclick="togglePassword('passLogin', this)"></i>
        </div>

        <button type="submit" class="btn-enviar">ENTRAR</button>
        <p class="login-link">Não tem conta? <span id="switchCadastro">Cadastrar</span></p>
    </form>

    <form id="cadastroForm" action="#" method="POST" style="display:none;" onsubmit="return validarSenha()">
        <p id="msgErro" style="color: #ff4d4d; font-size: 1.05rem; text-align: center; display: none; margin-bottom: 10px;"></p>
        <input type="text" name="name" placeholder="Nome completo" required>
        <input type="email" name="email" placeholder="E-mail" required>
        <input type="email" name="email_confirm" placeholder="Confirmar E-mail" required>
        
        <div class="input-group-auth">
            <input type="password" name="password" id="passCad" placeholder="Senha" required>
            <i class="fas fa-eye toggle-password" onclick="togglePassword('passCad', this)"></i>
        </div>

        <div class="input-group-auth">
            <input type="password" name="password_confirm" id="passCadConfirm" placeholder="Confirmar senha" required>
            <i class="fas fa-eye toggle-password" onclick="togglePassword('passCadConfirm', this)"></i>
        </div>

        <button type="submit" class="btn-enviar">CADASTRAR</button>
        <p class="login-link">Já tem conta? <span id="switchLogin">Login</span></p>
    </form>
</div>
</div>
</div>
</div>
</div>

<script>
    const loginForm = document.getElementById('loginForm');
    const cadastroForm = document.getElementById('cadastroForm');
    const titulo = document.getElementById('titulo');

    document.addEventListener('click', (e) => {
        if (e.target.id === 'switchCadastro') {
            loginForm.style.display = 'none';
            cadastroForm.style.display = 'block';
            titulo.textContent = 'CADASTRO';
        }
        if (e.target.id === 'switchLogin') {
            loginForm.style.display = 'block';
            cadastroForm.style.display = 'none';
            titulo.textContent = 'LOGIN';
        }
    });

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

    function validarSenha() {
    const senha = document.getElementById('passCad').value;
    const confirma = document.getElementById('passCadConfirm').value;
    const msgErro = document.getElementById('msgErro');

    if (senha !== confirma) {
        msgErro.textContent = "As senhas não coincidem!";
        msgErro.style.display = "block"; // Faz a mensagem aparecer
        
        // Opcional: dar um destaque visual no campo
        document.getElementById('passCadConfirm').style.borderColor = "#ff4d4d";
        
        return false; // Impede o envio do formulário
    }
    
    msgErro.style.display = "none"; // Esconde se estiver tudo certo
    return true;
}
</script>