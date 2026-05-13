<script>

const navToggle = document.querySelector(".nav-toggle");
const linksContainer = document.querySelector(".links-container");
const links = document.querySelector(".links");

navToggle.addEventListener("click", function(){

    const linksHeight = links.getBoundingClientRect().height;

    const containerHeight = linksContainer.getBoundingClientRect().height;

    if(containerHeight === 0){
        linksContainer.style.height = `${linksHeight + 80}px`;
    } else {
        linksContainer.style.height = 0;
    }

});

/* deixar a navbar fixa ao rolar a pagina */

const navbar = document.getElementById("nav");

window.addEventListener("scroll", function(){

    const scrollHeight = window.pageYOffset;

    if(scrollHeight > 80){
        navbar.classList.add("fixed-nav");
    } else {
        navbar.classList.remove("fixed-nav");
    }

});

</script>

<footer class="">
    <div class="d-flex justify-content-between headerfooter">
        <!--faixa de contato e info-->
        <div class="row">
            <!--foto e cnpj-->
            <div>
                <img src="imagens/logo.png" style="max-height: 100px; padding: 20px;" class="d-inline-block align-top img-fluid" alt="Logo">
                <p>Levando um pouquinho de amor para cada momento</p>
                <p>CNPJ: xxxxxxx</p>
            </div>
            
            <!--contato-->
            <div>
                <p>CONTATO</p>
                <p><i class="fa-brands fa-whatsapp"></i> (15) 12345-5678</p>
            </div>
            <!--contato-->
            <div>
                <p>REDES SOCIAIS</p>
                <div class="redessociais">
                    <p class=""><i class="fa-brands fa-instagram"></i></p>
                </div>
                <p class="redessociais"><i class="fa-brands fa-facebook"></i></p>
            </div>
        </div>
    </div>
</footer>