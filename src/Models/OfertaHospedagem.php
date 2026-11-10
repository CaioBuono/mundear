<?php

namespace Models;

use Utils\Formatador;

class OfertaHospedagem
{
    private int $idOferta;

    private string $nomeSite;

    private float $precoDiaria;

    private string $linkReserva;

    private string $dataConsulta;

    public function __construct(int $idOferta, string $nomeSite, float $precoDiaria, string $linkReserva, string $dataConsulta)
    {
        $this->idOferta     = $idOferta;
        $this->nomeSite     = $nomeSite;
        $this->precoDiaria  = $precoDiaria;
        $this->linkReserva  = $linkReserva;
        $this->dataConsulta = $dataConsulta;
    }

    public function getIdOferta(): int { return $this->idOferta; }
    public function getNomeSite(): string { return $this->nomeSite; }
    public function getPrecoDiaria(): float { return $this->precoDiaria; }
    public function getLinkReserva(): string { return $this->linkReserva; }
    public function getDataConsulta(): string { return $this->dataConsulta; }

    public function exibirOferta(): string
    {
        return sprintf('%s · %s por diária · %s', $this->nomeSite, Formatador::moeda($this->precoDiaria), $this->linkReserva);
    }

    public static function fromArray(array $registro): OfertaHospedagem
    {
        return new OfertaHospedagem(
            (int) $registro['idOferta'],
            $registro['nomeSite'],
            (float) $registro['precoDiaria'],
            $registro['linkReserva'] ?? '',
            $registro['dataConsulta'] ?? date('Y-m-d H:i:s')
        );
    }
}
