<?php

namespace Models;

use Services\ApiTransporteSimulada;
use Utils\Formatador;

/**
 * Opção de deslocamento (voo ou ônibus) retornada pela API simulada.
 * O id é um código alfanumérico gerado pela "API" (ex.: V3fa91c0d2).
 */
class Transporte implements ServicoRoteiro
{
    private string $idTransporte;

    private string $tipoTransporte;

    private string $empresa;

    private string $localOrigem;

    private string $localDestino;

    private string $dataHoraPartida;

    private string $dataHoraChegada;

    private int $duracaoMinutos;

    private float $preco;

    private int $assentosDisponiveis;

    public function getIdTransporte(): string { return $this->idTransporte; }
    public function getTipoTransporte(): string { return $this->tipoTransporte; }
    public function getEmpresa(): string { return $this->empresa; }
    public function getLocalOrigem(): string { return $this->localOrigem; }
    public function getLocalDestino(): string { return $this->localDestino; }
    public function getDataHoraPartida(): string { return $this->dataHoraPartida; }
    public function getDataHoraChegada(): string { return $this->dataHoraChegada; }
    public function getDuracaoMinutos(): int { return $this->duracaoMinutos; }
    public function getPreco(): float { return $this->preco; }
    public function getAssentosDisponiveis(): int { return $this->assentosDisponiveis; }

    public function isVoo(): bool
    {
        return $this->tipoTransporte === ApiTransporteSimulada::TIPO_VOO;
    }

    public function getRotuloTipo(): string
    {
        return $this->isVoo() ? 'Voo' : 'Ônibus';
    }

    /**
     * Disponível se a partida ainda não ocorreu e houver assentos suficientes.
     */
    public function verificarDisponibilidade(string $dataHora, int $quantidadePessoas): bool
    {
        return strtotime($dataHora) > time() && $quantidadePessoas <= $this->assentosDisponiveis;
    }

    public function exibirDetalhesTransporte(): string
    {
        return sprintf(
            '%s %s · %s → %s · %s (%s) · %s por pessoa',
            $this->getRotuloTipo(),
            $this->empresa,
            $this->localOrigem,
            $this->localDestino,
            Formatador::dataHora($this->dataHoraPartida),
            Formatador::duracao($this->duracaoMinutos),
            Formatador::moeda($this->preco)
        );
    }

    // ServicoRoteiro
    public function getTipoServico(): string { return ItemRoteiro::TIPO_TRANSPORTE; }
    public function getIdServico(): int|string { return $this->idTransporte; }
    public function getNomeServico(): string { return $this->getRotuloTipo() . ' ' . $this->empresa; }
    public function getValorUnitario(): float { return $this->preco; }

    public function getDetalhesServico(): string
    {
        return sprintf(
            '%s → %s · saída %s, chegada %s',
            $this->localOrigem,
            $this->localDestino,
            Formatador::hora($this->dataHoraPartida),
            Formatador::dataCurta($this->dataHoraChegada) . ' ' . Formatador::hora($this->dataHoraChegada)
        );
    }

    /**
     * RF02: busca opções com filtros de data, origem/destino e tipo.
     *
     * @return Transporte[]
     */
    public static function pesquisar(string $origem, string $destino, string $data, string $tipo = 'todos'): array
    {
        return array_map(
            fn($registro) => self::criar($registro),
            ApiTransporteSimulada::buscar($origem, $destino, $data, $tipo)
        );
    }

    /**
     * Reencontra uma opção pelo código refazendo a mesma busca na API simulada.
     */
    public static function buscarPorCodigo(string $origem, string $destino, string $data, string $codigo): ?Transporte
    {
        foreach(self::pesquisar($origem, $destino, $data) as $obTransporte){
            if(hash_equals($obTransporte->idTransporte, $codigo)){
                return $obTransporte;
            }
        }

        return null;
    }

    private static function criar(array $registro): Transporte
    {
        $obTransporte                      = new Transporte();
        $obTransporte->idTransporte        = $registro['idTransporte'];
        $obTransporte->tipoTransporte      = $registro['tipoTransporte'];
        $obTransporte->empresa             = $registro['empresa'];
        $obTransporte->localOrigem         = $registro['localOrigem'];
        $obTransporte->localDestino        = $registro['localDestino'];
        $obTransporte->dataHoraPartida     = $registro['dataHoraPartida'];
        $obTransporte->dataHoraChegada     = $registro['dataHoraChegada'];
        $obTransporte->duracaoMinutos      = (int) $registro['duracaoMinutos'];
        $obTransporte->preco               = (float) $registro['preco'];
        $obTransporte->assentosDisponiveis = (int) $registro['assentosDisponiveis'];

        return $obTransporte;
    }
}
