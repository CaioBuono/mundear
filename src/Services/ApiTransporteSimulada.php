<?php

namespace Services;

use BD\TabelaJson;
use Utils\Texto;

class ApiTransporteSimulada
{
    public const TIPO_VOO    = 'voo';
    public const TIPO_ONIBUS = 'onibus';
    public const TIPO_CARONA = 'carona';

    private static function getTabela(): TabelaJson
    {
        return new TabelaJson('transporte', 'idTransporte');
    }

    public static function buscar(string $origem, string $destino, string $data, string $tipo = 'todos'): array
    {
        $cidadeOrigem  = explode(' - ', $origem)[0];
        $cidadeDestino = explode(' - ', $destino)[0];

        $ofertas = self::getTabela()->listar(function($oferta) use ($cidadeOrigem, $cidadeDestino, $tipo){
            if($tipo !== 'todos' and $oferta['tipoTransporte'] !== $tipo) return false;
            if(!Texto::iguais($oferta['cidadeDestino'], $cidadeDestino))   return false;

            foreach($oferta['cidadesOrigem'] as $cidadeAtendida){
                if(Texto::iguais($cidadeAtendida, $cidadeOrigem)) return true;
            }

            return false;
        });

        return array_map(fn($oferta) => self::montarViagem($oferta, $data), $ofertas);
    }

    public static function getApresentacao(string $idTransporte): array
    {
        foreach(self::getTabela()->listar() as $oferta){
            if($oferta['idTransporte'] === $idTransporte){
                return $oferta;
            }
        }

        return [];
    }

    private static function montarViagem(array $oferta, string $data): array
    {
        $partida = new \DateTime($data . ' ' . $oferta['horaPartida']);
        $chegada = (clone $partida)->modify('+' . (int) $oferta['duracaoMinutos'] . ' minutes');

        return $oferta + [
            'dataHoraPartida' => $partida->format('Y-m-d H:i:s'),
            'dataHoraChegada' => $chegada->format('Y-m-d H:i:s')
        ];
    }
}
