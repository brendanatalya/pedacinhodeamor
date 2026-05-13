            <hr>
        </main>

        
        <footer class="container">
            <?php $data = new DateTime("now", new DateTimeZone("America/Sao_Paulo"))?>
            <p>&copy;2025 à <?php echo $data->format("Y") ?>- Brenda Natalya e Maria Luísa</p>
        </footer>

        <script src="<?php echo BASEURL; ?>js/jquery-3.7.1.min.js"></script>
        <script src="<?php echo BASEURL; ?>js/bootstrap/bootstrap.bundle.min.js"></script>
        <script src="<?php echo BASEURL; ?>js/awesome/all.min.js"></script>
        <script src="<?php echo BASEURL; ?>js/jquery.mask.js"></script>
        <script src="<?php echo BASEURL; ?>js/main.js"></script>

        <script>
            //mascaras
            $('#telefone').mask('00 00000000');
        </script>
    </body>
</html>