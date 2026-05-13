<?php 
    include ("../config.php");
    include (HEADER_TEMPLATE);
?>
    <div id="actions" class="mt-5 mb-5">
        <form action="valida.php" method="post">
            <div class="row">
                <div class="form-floating col-12 mb-2">
                    <input type="text" class="form-control" id="log" name="login" placeholder="Usuário">
                    <label for="log">Usuário</label>
                </div>
                <div class="form-floating col-12 mb-2">
                    <input type="password" class="form-control" id="pass" name="senha" placeholder="Senha">
                    <label for="pass">Senha</label>
                </div>
                <div class="col-12 mb-2">
                    <button type="submit" class="btn btn-info mb-4"><i class="fa-solid fa-user"></i>Entrar</button>
                    <a href="<?php echo BASEURL;?>" class="btn btn-dark mb-4"><i class="fa-solid fa-x"></i> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
<?php include (FOOTER_TEMPLATE); ?>