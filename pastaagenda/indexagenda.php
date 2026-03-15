<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="css_pda/style_pda.css">
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="fontawesome/css/all.min.css">
   <title>Agenda Pedacinho</title>
</head>
<body>
    <header>
     <h1>Lista de Agendas</h1>
    </header>
   
    <!-- Relogio de cima-->
    <div class="relogio-container">
         <div id="relogio">
    </div>

    <!-- Seçao Agenda -->
        <div class="calendar">
            <div class="nav-btn-container">
                <button class="nav-btn"> Voltar </button>
                <h2 id="mesAno" style="margin: 0"></h2>
                <button class="nav-btn"> Próximo </button>
            </div>

            <div class="calendar-grid" id="calendario"></div>
        </div>

        <!-- Modal para add/edit,deletar-->
         <div id="seletorEventoWrapper">
            <label for="seletorEvento">
                <strong>Selecione</strong>


</body>
</html>