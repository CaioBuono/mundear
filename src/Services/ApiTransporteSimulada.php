<?php

namespace Services;

use Random\Engine\Mt19937;
use Random\Randomizer;
use Utils\Texto;

/**
 * Simula o consumo de uma API externa de passagens (voos e ônibus) — RF02.
 *
 * As opções são geradas de forma determinística a partir de (tipo, origem,
 * destino, data): a mesma busca sempre devolve os mesmos resultados, com os
 * mesmos códigos. Assim é possível reencontrar a opção escolhida no momento de
 * adicioná-la ao roteiro sem confiar em preços enviados pelo navegador.
 */
class ApiTransporteSimulada
{
    public const TIPO_VOO    = 'voo';
    public const TIPO_ONIBUS = 'onibus';

    private const EMPRESAS = [
        self::TIPO_VOO    => ['Aerovia Linhas Aéreas', 'Céu Azul Linhas Aéreas', 'VoeBrasil'],
        self::TIPO_ONIBUS => ['Viação Estrela', 'Expresso Litoral', 'Rodoviária Horizonte'],
    ];

    /** Rotas rodoviárias acima desta distância (km) não têm ônibus. */
    private const DISTANCIA_MAXIMA_ONIBUS = 1800;

    /** Coordenadas aproximadas (lat, lon) para estimar distâncias. */
    private const COORDENADAS = [
        'rio de janeiro'     => [-22.91, -43.17],
        'sao paulo'          => [-23.55, -46.63],
        'salvador'           => [-12.97, -38.50],
        'florianopolis'      => [-27.59, -48.55],
        'foz do iguacu'      => [-25.55, -54.59],
        'gramado'            => [-29.38, -50.88],
        'recife'             => [-8.05, -34.90],
        'belo horizonte'     => [-19.92, -43.94],
        'fortaleza'          => [-3.73, -38.52],
        'manaus'             => [-3.12, -60.02],
        'brasilia'           => [-15.79, -47.88],
        'curitiba'           => [-25.43, -49.27],
        'porto alegre'       => [-30.03, -51.23],
        'campinas'           => [-22.91, -47.06],
        'santos'             => [-23.96, -46.33],
        'niteroi'            => [-22.88, -43.10],
        'goiania'            => [-16.68, -49.25],
        'vitoria'            => [-20.32, -40.34],
        'natal'              => [-5.79, -35.21],
        'joao pessoa'        => [-7.12, -34.86],
        'maceio'             => [-9.67, -35.74],
        'aracaju'            => [-10.91, -37.07],
        'belem'              => [-1.46, -48.49],
        'sao luis'           => [-2.53, -44.30],
        'teresina'           => [-5.09, -42.80],
        'cuiaba'             => [-15.60, -56.10],
        'campo grande'       => [-20.47, -54.62],
        'porto seguro'       => [-16.45, -39.06],
        'balneario camboriu' => [-26.99, -48.63],
    ];

    /**
     * @return array[] registros brutos no formato da classe Transporte
     */
    public static function buscar(string $origem, string $destino, string $data, string $tipo = 'todos'): array
    {
        $tipos      = in_array($tipo, [self::TIPO_VOO, self::TIPO_ONIBUS], true) ? [$tipo] : [self::TIPO_VOO, self::TIPO_ONIBUS];
        $distancia  = self::distanciaEstimada($origem, $destino);
        $resultados = [];

        foreach($tipos as $tipoTransporte){
            if($tipoTransporte === self::TIPO_ONIBUS and $distancia * 1.25 > self::DISTANCIA_MAXIMA_ONIBUS){
                continue;
            }

            $resultados = array_merge($resultados, self::gerarOpcoes($tipoTransporte, $origem, $destino, $data, $distancia));
        }

        usort($resultados, fn($a, $b) => strcmp($a['dataHoraPartida'], $b['dataHoraPartida']));

        return $resultados;
    }

    private static function gerarOpcoes(string $tipo, string $origem, string $destino, string $data, float $distancia): array
    {
        $chave      = implode('|', [$tipo, Texto::normalizar($origem), Texto::normalizar($destino), $data]);
        $sorteio    = new Randomizer(new Mt19937(crc32($chave)));
        $empresas   = self::EMPRESAS[$tipo];
        $quantidade = $sorteio->getInt(3, 5);
        $opcoes     = [];

        for($i = 0; $i < $quantidade; $i++){
            // Partidas entre 05:00 e 23:00, em intervalos de 5 minutos
            $minutosPartida = $sorteio->getInt(60, 276) * 5;

            if($tipo === self::TIPO_VOO){
                $duracao = (int) (40 + $distancia / 12) + $sorteio->getInt(0, 6) * 10;
                $preco   = (150 + $distancia * 0.30) * $sorteio->getInt(80, 140) / 100;
            }else{
                $distanciaRodoviaria = $distancia * 1.25;
                $duracao = (int) ($distanciaRodoviaria / 70 * 60) + $sorteio->getInt(0, 6) * 10;
                $preco   = (30 + $distanciaRodoviaria * 0.16) * $sorteio->getInt(85, 130) / 100;
            }

            $partida = (new \DateTime($data))->setTime(0, 0)->modify("+{$minutosPartida} minutes");
            $chegada = (clone $partida)->modify("+{$duracao} minutes");

            $opcoes[] = [
                'idTransporte'        => strtoupper($tipo[0]) . substr(md5($chave . '|' . $i), 0, 9),
                'tipoTransporte'      => $tipo,
                'empresa'             => $empresas[$sorteio->getInt(0, count($empresas) - 1)],
                'localOrigem'         => $origem,
                'localDestino'        => $destino,
                'dataHoraPartida'     => $partida->format('Y-m-d H:i:s'),
                'dataHoraChegada'     => $chegada->format('Y-m-d H:i:s'),
                'duracaoMinutos'      => $duracao,
                'preco'               => floor($preco) + 0.90,
                'assentosDisponiveis' => $sorteio->getInt(0, 9) === 0 ? 0 : $sorteio->getInt(2, 48)
            ];
        }

        return $opcoes;
    }

    /**
     * Distância em linha reta (km). Para cidades fora da lista usa um valor
     * pseudoaleatório estável, para que a simulação funcione com qualquer cidade.
     */
    private static function distanciaEstimada(string $origem, string $destino): float
    {
        $a = self::COORDENADAS[Texto::normalizar($origem)] ?? null;
        $b = self::COORDENADAS[Texto::normalizar($destino)] ?? null;

        if($a and $b){
            return self::haversine($a, $b);
        }

        $par = [Texto::normalizar($origem), Texto::normalizar($destino)];
        sort($par);

        return 300 + crc32(implode('|', $par)) % 2200;
    }

    private static function haversine(array $a, array $b): float
    {
        [$lat1, $lon1] = array_map('deg2rad', $a);
        [$lat2, $lon2] = array_map('deg2rad', $b);

        $h = sin(($lat2 - $lat1) / 2) ** 2 + cos($lat1) * cos($lat2) * sin(($lon2 - $lon1) / 2) ** 2;

        return 6371 * 2 * asin(sqrt($h));
    }
}
