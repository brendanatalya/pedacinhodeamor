document.addEventListener("DOMContentLoaded", function () {
    var msgCookies = document.getElementById("cookies-msg");

    if (!msgCookies) return;

    if (localStorage.getItem("lgpd") === "sim" || localStorage.getItem("lgpd") === "nao") {
        msgCookies.classList.remove("mostrar");
    } else {
        msgCookies.classList.add("mostrar");
    }

    window.aceito = function () {
        localStorage.setItem("lgpd", "sim");
        msgCookies.classList.remove("mostrar");
    };

    window.recusar = function () {
        localStorage.setItem("lgpd", "nao");
        msgCookies.classList.remove("mostrar");
    };
});