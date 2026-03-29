// Executar quando o documento HTML for completamente carregado
document.addEventListener('DOMContentLoaded', function () {

    // Receber o SELETOR calendar do atributo id
    var calendarEl = document.getElementById('calendar');

    // Receber o SELETOR da janela modal cadastrar
    const cadastrarModal = new bootstrap.Modal(document.getElementById("cadastrarModal"));

    // Instanciar FullCalendar.Calendar e atribuir a variável calendar
    var calendar = new FullCalendar.Calendar(calendarEl, {

        // Incluir o bootstrap 5
        themeSystem: 'bootstrap5',

        // Criar o cabeçalho do calendário
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },

        // Definir o idioma usado no calendário
        locale: 'pt-br',

        // Definir a data inicial
        //initialDate: '2023-01-12',
        //initialDate: '2023-10-12',

        // Permitir clicar nos nomes dos dias da semana 
        navLinks: true,

        // Permitir clicar e arrastar o mouse sobre um ou vários dias no calendário
        selectable: true,

        // Indicar visualmente a área que será selecionada antes que o usuário solte o botão do mouse para confirmar a seleção
        selectMirror: true,

        // Permitir arrastar e redimensionar os eventos diretamente no calendário.
        editable: true,

        // Número máximo de eventos em um determinado dia, se for true, o número de eventos será limitado à altura da célula do dia
        dayMaxEvents: true,

        // Chamar o arquivo PHP para recuperar os eventos
        events: 'listar_evento.php',

        // Identificar o clique do usuário sobre o evento
        eventClick: function (info) {

            // Receber o SELETOR da janela modal visualizar
            const visualizarModal = new bootstrap.Modal(document.getElementById("visualizarModal"));

            // Enviar para a janela modal os dados do evento
            document.getElementById("visualizar_id").innerText = info.event.id;
            document.getElementById("visualizar_title").innerText = info.event.title;
            document.getElementById("visualizar_start").innerText = info.event.start.toLocaleString();
            document.getElementById("visualizar_end").innerText = info.event.end !== null ? info.event.end.toLocaleString() : info.event.start.toLocaleString();

            // Abrir a janela modal visualizar
            visualizarModal.show();
        },
        // Abrir a janela modal cadastrar quando clicar sobre o dia no calendário
        select: function (info) {
            
            
            // Chamar a função para converter a data selecionada para ISO8601 e enviar para o formulário
            document.getElementById("cad_start").value = converterData(info.start);
            document.getElementById("cad_end").value = converterData(info.start);

            // Abrir a janela modal cadastrar
            cadastrarModal.show();
        }
    });

    // Renderizar o calendário
    calendar.render();

    // Converter a data
    function converterData(data) {

        // Converter a string em um objeto Date
        const dataObj = new Date(data);

        // Extrair o ano da data
        const ano = dataObj.getFullYear();

        // Obter o mês, mês começa de 0, padStart adiciona zeros à esquerda para garantir que o mês tenha dígitos
        const mes = String(dataObj.getMonth() + 1).padStart(2, '0');

        // Obter o dia do mês, padStart adiciona zeros à esquerda para garantir que o dia tenha dois dígitos
        const dia = String(dataObj.getDate()).padStart(2, '0');

        // Obter a hora, padStart adiciona zeros à esquerda para garantir que a hora tenha dois dígitos
        const hora = String(dataObj.getHours()).padStart(2, '0');

        // Obter minuto, padStart adiciona zeros à esquerda para garantir que o minuto tenha dois dígitos
        const minuto = String(dataObj.getMinutes()).padStart(2, '0');

        // Retornar a data
        return `${ano}-${mes}-${dia} ${hora}:${minuto}`;
    }
});