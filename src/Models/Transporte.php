<?php

namespace Models;

use Services\ApiTransporteSimulada;
use Utils\Formatador;

class Transporte implements ServicoRoteiro
{
    const TIPO_VEICULO_PESSOAL = 'veiculo';

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

    public function __construct(
        string $idTransporte,
        string $tipoTransporte,
        string $empresa,
        string $localOrigem,
        string $localDestino,
        string $dataHoraPartida,
        string $dataHoraChegada,
        int $duracaoMinutos,
        float $preco,
        int $assentosDisponiveis
    ){
        $this->idTransporte        = $idTransporte;
        $this->tipoTransporte      = $tipoTransporte;
        $this->empresa             = $empresa;
        $this->localOrigem         = $localOrigem;
        $this->localDestino        = $localDestino;
        $this->dataHoraPartida     = $dataHoraPartida;
        $this->dataHoraChegada     = $dataHoraChegada;
        $this->duracaoMinutos      = $duracaoMinutos;
        $this->preco               = $preco;
        $this->assentosDisponiveis = $assentosDisponiveis;
    }

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
        return match($this->tipoTransporte){
            ApiTransporteSimulada::TIPO_VOO    => 'Avião',
            ApiTransporteSimulada::TIPO_CARONA => 'Carona',
            self::TIPO_VEICULO_PESSOAL         => 'Veículo pessoal',
            default                         => 'Ônibus',
        };
    }

    public function calcularCusto(int $quantidadePessoas): float
    {
        return round($this->preco * $quantidadePessoas, 2);
    }

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

    public static function pesquisar(string $origem, string $destino, string $data, ?float $precoMaximo = null, string $tipo = 'todos'): array
    {
        $opcoes = array_map(
            fn($registro) => self::fromArray($registro),
            ApiTransporteSimulada::buscar($origem, $destino, $data, $tipo)
        );

        if($precoMaximo !== null){
            $opcoes = array_values(array_filter($opcoes, fn(Transporte $obTransporte) => $obTransporte->preco <= $precoMaximo));
        }

        usort($opcoes, fn(Transporte $a, Transporte $b) => $a->preco <=> $b->preco);

        return $opcoes;
    }

    public static function buscarPorCodigo(string $origem, string $destino, string $data, string $codigo): ?Transporte
    {
        foreach(self::pesquisar($origem, $destino, $data) as $obTransporte){
            if(hash_equals($obTransporte->idTransporte, $codigo)){
                return $obTransporte;
            }
        }

        return null;
    }

    public function toArray(): array
    {
        return [
            'idTransporte'        => $this->idTransporte,
            'tipoTransporte'      => $this->tipoTransporte,
            'empresa'             => $this->empresa,
            'localOrigem'         => $this->localOrigem,
            'localDestino'        => $this->localDestino,
            'dataHoraPartida'     => $this->dataHoraPartida,
            'dataHoraChegada'     => $this->dataHoraChegada,
            'duracaoMinutos'      => $this->duracaoMinutos,
            'preco'               => $this->preco,
            'assentosDisponiveis' => $this->assentosDisponiveis
        ];
    }

    public static function fromArray(array $registro): Transporte
    {
        if(($registro['tipoTransporte'] ?? '') === self::TIPO_VEICULO_PESSOAL){
            return VeiculoPessoal::fromArray($registro);
        }

        return new Transporte(
            (string) $registro['idTransporte'],
            $registro['tipoTransporte'],
            $registro['empresa'],
            $registro['localOrigem'],
            $registro['localDestino'],
            $registro['dataHoraPartida'],
            $registro['dataHoraChegada'],
            (int) $registro['duracaoMinutos'],
            (float) $registro['preco'],
            (int) $registro['assentosDisponiveis']
        );
    }
}
