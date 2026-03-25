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
      initialDate: '2023-01-01',

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

      
      events: [
        {
          title: 'All Day Event',
          start: '2023-01-01'
        },
        {
          title: 'Long Event',
          start: '2023-01-07',
          end: '2023-01-10'
        },
        {
          groupId: 999,
          title: 'Repeating Event',
          start: '2023-01-09T16:00:00'
        },
        {
          groupId: 999,
          title: 'Repeating Event',
          start: '2023-01-16T16:00:00'
        },
        {
          title: 'Conference',
          start: '2023-01-11',
          end: '2023-01-13'
        },
        {
          title: 'Meeting',
          start: '2023-01-12T10:30:00',
          end: '2023-01-12T12:30:00'
        },
        {
          title: 'Lunch',
          start: '2023-01-12T12:00:00'
        },
        {
          title: 'Meeting',
          start: '2023-01-12T14:30:00'
        },
        {
          title: 'Happy Hour',
          start: '2023-01-12T17:30:00'
        },
        {
          title: 'Dinner',
          start: '2023-01-12T20:00:00'
        },
        {
          title: 'Birthday Party',
          start: '2023-01-13T07:00:00'
        },
        {
          title: 'Click for Google',
          url: 'http://google.com/',
          start: '2023-01-28'
        }
      ]
    });

    calendar.render();
  });