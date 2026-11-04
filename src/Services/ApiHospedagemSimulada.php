<?php

namespace Services;

use BD\TabelaJson;
use Utils\Texto;

/**
 * Simula o consumo de uma API externa de hospedagens — RF03.
 * O "catálogo do parceiro" fica em src/BD/table_hospedagem.json.
 */
class ApiHospedagemSimulada
{
    private static function getTabela(): TabelaJson
    {
        return new TabelaJson('hospedagem', 'idHospedagem');
    }

    /**
     * @return array[] hospedagens da cidade informada (busca parcial, sem acentos)
     */
    public static function buscarPorCidade(string $cidade): array
    {
        return self::getTabela()->listar(fn($hospedagem) => Texto::contem($hospedagem['cidade'], $cidade));
    }

    public static function buscarPorId(int $idHospedagem): ?array
    {
        return self::getTabela()->buscarPorId($idHospedagem);
    }
}
