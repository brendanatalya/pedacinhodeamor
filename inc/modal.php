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
        <div id="loginError" style="color: #ff4d4d; font-size: 0.95rem; text-align: center; display: none; margin-bottom: 15px; padding: 10px; background-color: #ffe0e0; border-radius: 4px;"></div>
        
        <input type="email" name="email" placeholder="E-mail" required>
        
        <div class="input-group-auth">
            <input type="password" name="senha" id="passLogin" placeholder="Senha" required>
            <i class="fas fa-eye toggle-password" onclick="togglePassword('passLogin', this)"></i>
        </div>

        <button type="submit" class="btn-enviar">ENTRAR</button>
        <p class="login-link">Não tem conta? <span id="switchCadastro">Cadastrar</span></p>
    </form>

    <form id="cadastroForm" action="cadastro.php" method="POST" style="display:none;" onsubmit="return validarSenha()">
        <div id="cadastroError" style="color: #ff4d4d; font-size: 0.95rem; text-align: center; display: none; margin-bottom: 15px; padding: 10px; background-color: #ffe0e0; border-radius: 4px;"></div>
        
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
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const cadastroForm = document.getElementById('cadastroForm');
    const titulo = document.getElementById('titulo');
    const loginError = document.getElementById('loginError');
    const cadastroError = document.getElementById('cadastroError');

    if (!loginForm || !cadastroForm) return;

    document.addEventListener('click', (e) => {
        if (e.target.id === 'switchCadastro') {
            loginForm.style.display = 'none';
            cadastroForm.style.display = 'block';
            titulo.textContent = 'CADASTRO';
            loginError.style.display = 'none';
            cadastroError.style.display = 'none';
        }
        if (e.target.id === 'switchLogin') {
            loginForm.style.display = 'block';
            cadastroForm.style.display = 'none';
            titulo.textContent = 'LOGIN';
            loginError.style.display = 'none';
            cadastroError.style.display = 'none';
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

    // VALIDAÇÃO E ENVIO DO LOGIN
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        loginError.style.display = 'none';
        loginError.textContent = '';

        const formData = new FormData(loginForm);
        
        try {
            const response = await fetch('inc/valida.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Login bem-sucedido, redireciona para URL apropriada
                const redirectUrl = data.redirect_url || 'index.php';
                window.location.href = redirectUrl;
            } else {
                // Erro, exibe no modal
                loginError.textContent = data.message;
                loginError.style.display = 'block';
            }
        } catch (error) {
            loginError.textContent = 'Erro ao conectar com o servidor.';
            loginError.style.display = 'block';
        }
    });

    // VALIDAÇÃO E ENVIO DO CADASTRO
    cadastroForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        cadastroError.style.display = 'none';
        cadastroError.textContent = '';

        const senha = document.getElementById('passCad').value;
        const confirma = document.getElementById('passCadConfirm').value;

        if (senha !== confirma) {
            cadastroError.textContent = "As senhas não coincidem!";
            cadastroError.style.display = 'block';
            return false;
        }

        const formData = new FormData(cadastroForm);
        
        try {
            const response = await fetch('cadastro.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Cadastro bem-sucedido, redireciona
                window.location.href = 'index.php';
            } else {
                // Erro, exibe no modal
                cadastroError.textContent = data.message;
                cadastroError.style.display = 'block';
            }
        } catch (error) {
            cadastroError.textContent = 'Erro ao conectar com o servidor.';
            cadastroError.style.display = 'block';
        }
    });
}); // fim DOMContentLoaded
</script>