<?php

namespace Services;

use BD\TabelaJson;
use Utils\Texto;

class ApiHospedagemSimulada
{
    private static function getTabela(): TabelaJson
    {
        return new TabelaJson('hospedagem', 'idHospedagem');
    }

    public static function buscarPorCidade(string $cidade): array
    {
        return self::getTabela()->listar(fn($hospedagem) => Texto::contem($hospedagem['cidade'], $cidade));
    }

    public static function buscarPorId(int $idHospedagem): ?array
    {
        return self::getTabela()->buscarPorId($idHospedagem);
    }
}
