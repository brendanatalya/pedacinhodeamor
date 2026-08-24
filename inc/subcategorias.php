<?php
/**
 * inc/subcategorias.php
 *
 * Função única de detecção de subcategoria (doces + salgados), usada por
 * doces.php, salgados.php e cardapio.php. Antes essa mesma lógica estava
 * copiada em 3 lugares diferentes — se um dia precisar adicionar uma
 * palavra nova (ex: "casadinho"), só precisa mexer aqui.
 *
 * Quando o banco tiver uma coluna própria de subcategoria, essa função
 * pode ser removida e substituída pelo valor vindo do banco direto.
 */

function extrair_subcategoria(string $nome): string {
    $nome = mb_strtolower($nome, 'UTF-8');

    $mapa = [
        // doces
        'cone'          => ['cone'],
        'trufa'         => ['trufa'],
        'brigadeiro'    => ['brigadeiro'],
        'bolo'          => ['bolo'],
        'docinho'       => ['docinho', 'camafeu', 'beijinho', 'olho de sogra', 'cajuzinho',
                             'quindim', 'bicho de pé', 'bixo de pé'],
        // salgados
        'croissant'     => ['croissant'],
        'assado'        => ['assado', 'enroladinho', 'esfiha', 'esfirra'],
        'pao de queijo' => ['pão de queijo', 'pao de queijo'],
        'coxinha'       => ['coxinha'],
        'empada'        => ['empada'],
    ];

    foreach ($mapa as $sub => $palavras) {
        foreach ($palavras as $palavra) {
            if (str_contains($nome, $palavra)) return $sub;
        }
    }

    return 'outro';
}