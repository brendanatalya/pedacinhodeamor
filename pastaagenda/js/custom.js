//executar o código quando o HTML estiver completamente carregado
document.addEventListener('DOMContentLoaded', function() {

    //receber o SELETOR calendar do atributo id 
    var calendarEl = document.getElementById('calendar');

    //instanciar o calendário e atribuir a variavel calendar
    var calendar = new FullCalendar.Calendar(calendarEl, {

      //criar o cabeçalho do calendário
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
      },
      
      //definir o idioma do calendário para português do Brasil
      locale: 'pt-br',

      //definir a data inicial do calendário
      //initialDate: '2023-01-01',

      //permitir a navegação entre os dias e semanas clicando nos nomes
      navLinks: true, 

      //permitir clicar e arrastar o mouse sobre um ou varios dias no calendario
      selectable: true,

      //indicare visualmente a area selecionada antes que o usuário solte o mouse para confirmar a seleção
      selectMirror: true,

      //permitir arrastar e redimensionar os eventos diretamente no calendário
      editable: true,

      //numero maximo de eventos em um determindao dia , se for true, o numero de eventos 
      // sera limitadoa altura da celula do dia
      dayMaxEvents: true, 

      // ajustar para o arquivo que existe no projeto (listar_evento.php)
      events: 'listar_evento.php'
    });

    calendar.render();
  });