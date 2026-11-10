<?php

if(PHP_SAPI !== 'cli'){
    http_response_code(404);
    exit();
}

$pastaSeed  = __DIR__ . '/src/BD/seed/';
$pastaDados = __DIR__ . '/src/BD/';

foreach(glob($pastaSeed . 'table_*.json') as $arquivoSeed){
    $dados = json_decode(file_get_contents($arquivoSeed), true);

    if(basename($arquivoSeed) === 'table_roteiro.json'){
        foreach($dados as $indice => $roteiro){
            if(!empty($roteiro['compartilhamento'])){
                $dados[$indice]['compartilhamento']['dataCriacao']   = date('Y-m-d H:i:s');
                $dados[$indice]['compartilhamento']['dataExpiracao'] = date('Y-m-d H:i:s', strtotime('+30 days'));
            }
        }
    }

    $json = json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    file_put_contents($pastaDados . basename($arquivoSeed), $json . PHP_EOL, LOCK_EX);

    echo 'Restaurado: ' . basename($arquivoSeed) . ' (' . count($dados) . ' registros)' . PHP_EOL;
}
