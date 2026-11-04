<?php

namespace Models;

use Services\ApiHospedagemSimulada;
use Utils\Formatador;

class Hospedagem implements ServicoRoteiro
{
    private int $idHospedagem;

    private string $nomeLocal;

    private string $tipo;

    private string $cidade;

    private string $endereco;

    private float $precoDiaria;

    private float $avaliacao;

    public function getIdHospedagem(): int { return $this->idHospedagem; }
    public function getNomeLocal(): string { return $this->nomeLocal; }
    public function getTipo(): string { return $this->tipo; }
    public function getCidade(): string { return $this->cidade; }
    public function getEndereco(): string { return $this->endereco; }
    public function getPrecoDiaria(): float { return $this->precoDiaria; }
    public function getAvaliacao(): float { return $this->avaliacao; }

    /**
     * RF03: pesquisa hospedagens por cidade e período de estadia.
     *
     * @return Hospedagem[]
     */
    public static function pesquisarPorCidade(string $cidade, string $dataInicio, string $dataFim): array
    {
        // O período é validado pelo controller; a API simulada não tem lotação.
        return array_map(fn($registro) => self::criar($registro), ApiHospedagemSimulada::buscarPorCidade($cidade));
    }

    public function calcularSubtotalEstadia(int $quantidadeDiarias): float
    {
        return round($this->precoDiaria * max(1, $quantidadeDiarias), 2);
    }

    public static function getPorId(int $idHospedagem): ?Hospedagem
    {
        $registro = ApiHospedagemSimulada::buscarPorId($idHospedagem);
        return $registro ? self::criar($registro) : null;
    }

    // ServicoRoteiro
    public function getTipoServico(): string { return ItemRoteiro::TIPO_HOSPEDAGEM; }
    public function getIdServico(): int|string { return $this->idHospedagem; }
    public function getNomeServico(): string { return $this->nomeLocal; }
    public function getDetalhesServico(): string { return $this->tipo . ' · ' . $this->endereco . ', ' . $this->cidade; }
    public function getValorUnitario(): float { return $this->precoDiaria; }

    public function getDescricaoAvaliacao(): string
    {
        return Formatador::avaliacao($this->avaliacao);
    }

    private static function criar(array $registro): Hospedagem
    {
        $obHospedagem               = new Hospedagem();
        $obHospedagem->idHospedagem = (int) $registro['idHospedagem'];
        $obHospedagem->nomeLocal    = $registro['nomeLocal'];
        $obHospedagem->tipo         = $registro['tipo'] ?? 'Hotel';
        $obHospedagem->cidade       = $registro['cidade'];
        $obHospedagem->endereco     = $registro['endereco'];
        $obHospedagem->precoDiaria  = (float) $registro['precoDiaria'];
        $obHospedagem->avaliacao    = (float) $registro['avaliacao'];

        return $obHospedagem;
    }
}
