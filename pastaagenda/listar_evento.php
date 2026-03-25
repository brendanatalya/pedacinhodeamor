<?php

    // incluir o arquivo com a conexão com o banco de dados
    include_once "conexao.php";

    //QUERY para recuperar os eventos 
    $query_events = "SELECT id, title, color, start, end FROM events";

    //preparar a QUERY
    $result_events = $conn ->prepare ($query_events);

    //executar a QUERY
    $result_events ->execute();

    //criar um array para receber os eventos
    $eventos = [];

    //Percorrer a lista  de registros retornaado do banco de dados
    while($row_events = $result_events ->fetch(PDO::FETCH_ASSOC)){
        
    //extrair o array 
    extract ($row_events); 

    $eventos [] = [
    'id' => $id,
    'title' => $title,
    'color' => $color,
    'start' => $start,
    'end' => $end,
    ];

    }

    echo json_encode($eventos);
?>