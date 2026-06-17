<!-- MODAL AVALIAÇÃO -->
<?php if (!empty($_SESSION['pedido_avaliar_id'])): ?>
<div class="modal fade" id="modalAvaliacao" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content p-0 border-0">
<div class="auth-card">
    <div class="auth-header">
        <h2>⭐ AVALIAR PEDIDO</h2>
    </div>
    <div class="auth-body">
        <p class="text-center text-muted mb-4">
            Seu pedido <strong>#<?php echo $_SESSION['pedido_avaliar_id']; ?></strong> foi entregue!<br>
            Conta pra gente como foi?
        </p>
        <form id="formAvaliacao" method="POST" action="<?php echo BASEURL; ?>paginas/avaliar.php?pedido=<?php echo $_SESSION['pedido_avaliar_id']; ?>">

            <p style="font-weight:600; margin-bottom:4px;">🎂 Qualidade do Produto</p>
            <div class="star-group" id="grupo-produto" style="display:flex;flex-direction:row-reverse;justify-content:center;gap:6px;margin-bottom:16px;">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" name="nota_produto" id="mprod<?php echo $i; ?>" value="<?php echo $i; ?>" style="display:none;">
                    <label for="mprod<?php echo $i; ?>" style="font-size:2rem;color:#ddd;cursor:pointer;" onmouseover="hoverStars('grupo-produto',<?php echo $i; ?>)" onmouseout="resetStars('grupo-produto')" onclick="selectStars('grupo-produto',<?php echo $i; ?>)">★</label>
                <?php endfor; ?>
            </div>

            <p style="font-weight:600; margin-bottom:4px;">💝 Atendimento</p>
            <div class="star-group" id="grupo-atend" style="display:flex;flex-direction:row-reverse;justify-content:center;gap:6px;margin-bottom:16px;">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" name="nota_atend" id="matend<?php echo $i; ?>" value="<?php echo $i; ?>" style="display:none;">
                    <label for="matend<?php echo $i; ?>" style="font-size:2rem;color:#ddd;cursor:pointer;" onmouseover="hoverStars('grupo-atend',<?php echo $i; ?>)" onmouseout="resetStars('grupo-atend')" onclick="selectStars('grupo-atend',<?php echo $i; ?>)">★</label>
                <?php endfor; ?>
            </div>

            <textarea name="comentario" class="form-control mb-3" rows="2" placeholder="Comentário (opcional)..."></textarea>

            <div id="erroAvaliacao" style="color:#ff4d4d;font-size:0.9rem;text-align:center;display:none;margin-bottom:10px;"></div>

            <div style="display:flex;gap:8px;">
                <button type="button" class="btn-enviar" style="background:#aaa;flex:1;" onclick="fecharModalAvaliacao()">Agora não</button>
                <button type="submit" class="btn-enviar" style="flex:2;">Enviar ⭐</button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
</div>

<script>
// Abre o modal automaticamente ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    var modal = new bootstrap.Modal(document.getElementById('modalAvaliacao'));
    modal.show();
});

function hoverStars(grupoId, nota) {
    const labels = document.getElementById(grupoId).querySelectorAll('label');
    labels.forEach((label, i) => {
        // labels estão em ordem reversa (5,4,3,2,1), index 0 = estrela 5
        label.style.color = (5 - i) <= nota ? '#f5a623' : '#ddd';
    });
}

function resetStars(grupoId) {
    const grupo = document.getElementById(grupoId);
    const checked = grupo.querySelector('input:checked');
    const labels = grupo.querySelectorAll('label');
    const notaSelecionada = checked ? parseInt(checked.value) : 0;
    labels.forEach((label, i) => {
        label.style.color = (5 - i) <= notaSelecionada ? '#f5a623' : '#ddd';
    });
}

function selectStars(grupoId, nota) {
    const grupo = document.getElementById(grupoId);
    const prefix = grupoId === 'grupo-produto' ? 'mprod' : 'matend';
    document.getElementById(prefix + nota).checked = true;
    resetStars(grupoId);
}

function fecharModalAvaliacao() {
    // Fecha o modal e marca sessão para não abrir de novo nessa visita
    fetch('<?php echo BASEURL; ?>paginas/avaliar_dispensar.php?pedido=<?php echo $_SESSION['pedido_avaliar_id']; ?>');
    bootstrap.Modal.getInstance(document.getElementById('modalAvaliacao')).hide();
}

document.getElementById('formAvaliacao').addEventListener('submit', function(e) {
    const prod = document.querySelector('input[name="nota_produto"]:checked');
    const atend = document.querySelector('input[name="nota_atend"]:checked');
    const erro = document.getElementById('erroAvaliacao');
    if (!prod || !atend) {
        e.preventDefault();
        erro.textContent = 'Por favor, selecione uma nota em cada item.';
        erro.style.display = 'block';
    }
});
</script>
<?php endif; ?>

<!-- MODAL LOGIN -->
<div class="modal fade" id="modalLogin" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content p-0 border-0">

<div class="auth-card">

<div class="auth-header">
    <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close">&times;</button>
    <h2 id="titulo">LOGIN</h2>
</div>
<div class="auth-body">
    <form id="loginForm" action="<?php echo BASEURL; ?>inc/valida.php" method="POST">
        <div id="loginError" style="color: #ff4d4d; font-size: 0.95rem; text-align: center; display: none; margin-bottom: 15px; padding: 10px; background-color: #ffe0e0; border-radius: 4px;"></div>
        
        <input type="email" name="email" placeholder="E-mail" required>
        
        <div class="input-group-auth">
            <input type="password" name="senha" id="passLogin" placeholder="Senha" required>
            <i class="fas fa-eye toggle-password" onclick="togglePassword('passLogin', this)"></i>
        </div>

        <button type="submit" class="btn-enviar">ENTRAR</button>
        <p class="login-link">Não tem conta? <span id="switchCadastro">Cadastrar</span></p>
    </form>

    <form id="cadastroForm" action="<?php echo BASEURL; ?>inc/cadastro.php" method="POST" style="display:none;" onsubmit="return validarSenha()">
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


    // VALIDAÇÃO E ENVIO DO LOGIN
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        loginError.style.display = 'none';
        loginError.textContent = '';

        const formData = new FormData(loginForm);
        
        try {
            const response = await fetch('<?php echo BASEURL; ?>inc/valida.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Login bem-sucedido, redireciona para URL apropriada
                //const redirectUrl = data.redirect_url || '<?php //echo BASEURL; ?>index.php';
                //window.location.href = redirectUrl;
                window.location.reload();

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
            const response = await fetch('<?php echo BASEURL; ?>inc/cadastro.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Cadastro bem-sucedido, redireciona

                 window.location.href = '<?php echo BASEURL; ?>index.php';
                
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