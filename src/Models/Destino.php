<?php

namespace Models;

use BD\TabelaJson;
use Utils\Texto;

class Destino
{
    private int $idDestino;

    private string $cidade;

    private string $estado;

    private string $pais;

    private static function getTabela(): TabelaJson
    {
        return new TabelaJson('destino', 'idDestino');
    }

    public function getIdDestino(): int { return $this->idDestino; }
    public function getCidade(): string { return $this->cidade; }
    public function getEstado(): string { return $this->estado; }
    public function getPais(): string { return $this->pais; }

    public function exibirInformacoes(): string
    {
        return $this->cidade . ' - ' . $this->estado . ', ' . $this->pais;
    }

    public function listarAtracoes(): array
    {
        return Passeio::listarPorDestino($this->idDestino);
    }

    public static function listar(): array
    {
        $destinos = array_map(fn($registro) => self::criar($registro), self::getTabela()->listar());
        usort($destinos, fn($a, $b) => strcmp(Texto::normalizar($a->cidade), Texto::normalizar($b->cidade)));

        return $destinos;
    }

    public static function getPorId(int $idDestino): ?Destino
    {
        $registro = self::getTabela()->buscarPorId($idDestino);
        return $registro ? self::criar($registro) : null;
    }

    public static function getPorIds(array $idsDestino): array
    {
        return array_values(array_filter(array_map(fn($id) => self::getPorId((int) $id), $idsDestino)));
    }

    public static function buscarPorCidade(string $cidade): ?Destino
    {
        if(trim($cidade) === '') return null;

        $registro = self::getTabela()->buscarPrimeiro(fn($destino) => Texto::iguais($destino['cidade'], $cidade));
        return $registro ? self::criar($registro) : null;
    }

    private static function criar(array $registro): Destino
    {
        $obDestino            = new Destino();
        $obDestino->idDestino = (int) $registro['idDestino'];
        $obDestino->cidade    = $registro['cidade'];
        $obDestino->estado    = $registro['estado'];
        $obDestino->pais      = $registro['pais'];

        return $obDestino;
    }
}
