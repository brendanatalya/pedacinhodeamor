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

<footer>
  <div class="footer-container">

    <div class="footer-logo">
      <img src="imagens/logo.png" alt="Logo Pedacinho de Amor" style="max-width: 300px;">
      <p>Confeitaria artesanal, produtos feitos com o carinho de quem ama o que faz.</p>
      <p>CNPJ: 12.345.678/0001-90</p>

      <div class="footer-icones">
        <a href="#"><i class="fa-brands fa-instagram"></i></a>
        <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
      </div>
    </div>

    <div class="footer-contato">
      <h3>Contato</h3>
      <p><i class="fa-solid fa-location-dot"></i> Rua das Flores, 123<br>Centro · São Paulo</p>
      <p><i class="fa-solid fa-phone"></i> (11) 9 8765-4321</p>
      <p><i class="fa-regular fa-envelope"></i> ola@pedacinhodeamor.com.br</p>
    </div>

  </div>

  <div class="footer-copyright">
    <p>© 2026 Pedacinho de Amor. Todos os direitos reservados.</p>
    <p>Feito com <i class="fa-solid fa-heart" style="color: #8f1a5a;"></i> para adoçar seus momentos.</p>
  </div>
</footer>

