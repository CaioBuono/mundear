<?php

namespace Models;

use Exceptions\DadosInvalidosException;
use Utils\Formatador;

class VeiculoPessoal extends Transporte
{
    const TIPOS_VEICULO = ['Carro', 'Moto', 'SUV ou picape', 'Van'];

    const COMBUSTIVEIS = ['Gasolina', 'Etanol', 'Diesel'];

    private string $tipoVeiculo;

    private string $combustivel;

    private float $distanciaKm;

    private float $consumoKmPorLitro;

    private float $precoCombustivel;

    private float $valorPedagios;

    public function __construct(
        string $tipoVeiculo,
        string $combustivel,
        float $distanciaKm,
        float $consumoKmPorLitro,
        float $precoCombustivel,
        float $valorPedagios,
        string $localOrigem,
        string $localDestino,
        string $dataHoraPartida
    ){
        if(!in_array($tipoVeiculo, self::TIPOS_VEICULO, true) or !in_array($combustivel, self::COMBUSTIVEIS, true)){
            throw new DadosInvalidosException('Escolha um tipo de veículo e um combustível válidos.', 'RN06');
        }

        if($distanciaKm <= 0 or $consumoKmPorLitro <= 0 or $precoCombustivel <= 0 or $valorPedagios < 0){
            throw new DadosInvalidosException('Informe distância, consumo e preço do combustível maiores que zero.', 'RN06');
        }

        $this->tipoVeiculo       = $tipoVeiculo;
        $this->combustivel       = $combustivel;
        $this->distanciaKm       = $distanciaKm;
        $this->consumoKmPorLitro = $consumoKmPorLitro;
        $this->precoCombustivel  = $precoCombustivel;
        $this->valorPedagios     = $valorPedagios;

        parent::__construct(
            'VP' . substr(md5($tipoVeiculo . $combustivel . $distanciaKm . $localOrigem . $localDestino), 0, 8),
            self::TIPO_VEICULO_PESSOAL,
            $tipoVeiculo,
            $localOrigem,
            $localDestino,
            $dataHoraPartida,
            $dataHoraPartida,
            0,
            $this->calcularCusto(1),
            PHP_INT_MAX
        );
    }

    public function getTipoVeiculo(): string { return $this->tipoVeiculo; }
    public function getCombustivel(): string { return $this->combustivel; }
    public function getDistanciaKm(): float { return $this->distanciaKm; }
    public function getConsumoKmPorLitro(): float { return $this->consumoKmPorLitro; }
    public function getPrecoCombustivel(): float { return $this->precoCombustivel; }
    public function getValorPedagios(): float { return $this->valorPedagios; }

    public function calcularCusto(int $quantidadePessoas): float
    {
        $litros = $this->distanciaKm / $this->consumoKmPorLitro;

        return round($litros * $this->precoCombustivel + $this->valorPedagios, 2);
    }

    public function getNomeServico(): string
    {
        return 'Veículo pessoal · ' . $this->tipoVeiculo . ' (estimativa)';
    }

    public function getDetalhesServico(): string
    {
        return sprintf(
            '%s → %s · %s km · %s · pedágios %s',
            $this->getLocalOrigem(),
            $this->getLocalDestino(),
            number_format($this->distanciaKm, 0, ',', '.'),
            $this->combustivel,
            Formatador::moeda($this->valorPedagios)
        );
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'tipoVeiculo'       => $this->tipoVeiculo,
            'combustivel'       => $this->combustivel,
            'distanciaKm'       => $this->distanciaKm,
            'consumoKmPorLitro' => $this->consumoKmPorLitro,
            'precoCombustivel'  => $this->precoCombustivel,
            'valorPedagios'     => $this->valorPedagios
        ];
    }

    public static function fromArray(array $registro): VeiculoPessoal
    {
        return new VeiculoPessoal(
            $registro['tipoVeiculo'],
            $registro['combustivel'],
            (float) $registro['distanciaKm'],
            (float) $registro['consumoKmPorLitro'],
            (float) $registro['precoCombustivel'],
            (float) $registro['valorPedagios'],
            $registro['localOrigem'],
            $registro['localDestino'],
            $registro['dataHoraPartida']
        );
    }
}
