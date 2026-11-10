<?php

namespace Models;

use Exceptions\DadosInvalidosException;
use Utils\Formatador;
use Utils\Validador;

class GastoExtra
{
    const CATEGORIAS = ['Refeição', 'Compras', 'Ingresso', 'Outro'];

    private int $idGasto;

    private string $descricao;

    private string $categoria;

    private string $data;

    private float $valor;

    public function __construct(string $descricao, string $categoria, float $valor, string $data, int $idGasto = 0)
    {
        $descricao = trim($descricao);

        if($descricao === ''){
            throw new DadosInvalidosException('Informe a descrição do gasto.', 'RN06');
        }

        if(!in_array($categoria, self::CATEGORIAS, true)){
            throw new DadosInvalidosException('Escolha uma categoria válida para o gasto.', 'RN06');
        }

        if(!Validador::data($data)){
            throw new DadosInvalidosException('Informe uma data válida para o gasto.', 'RN06');
        }

        $this->idGasto   = $idGasto;
        $this->descricao = $descricao;
        $this->categoria = $categoria;
        $this->data      = $data;
        $this->atualizarValor($valor);
    }

    public function getIdGasto(): int { return $this->idGasto; }
    public function setIdGasto(int $idGasto): void { $this->idGasto = $idGasto; }
    public function getDescricao(): string { return $this->descricao; }
    public function getCategoria(): string { return $this->categoria; }
    public function getData(): string { return $this->data; }
    public function getValor(): float { return $this->valor; }

    public function atualizarValor(float $valor): void
    {
        if($valor <= 0){
            throw new DadosInvalidosException('O valor do gasto deve ser maior que zero.', 'RN06');
        }

        $this->valor = round($valor, 2);
    }

    public function exibirResumo(): string
    {
        return sprintf('%s · %s · %s', $this->descricao, Formatador::data($this->data), Formatador::moeda($this->valor));
    }

    public function toArray(): array
    {
        return [
            'idGasto'   => $this->idGasto,
            'descricao' => $this->descricao,
            'categoria' => $this->categoria,
            'data'      => $this->data,
            'valor'     => $this->valor
        ];
    }

    public static function fromArray(array $registro): GastoExtra
    {
        return new GastoExtra($registro['descricao'], $registro['categoria'], (float) $registro['valor'], $registro['data'], (int) $registro['idGasto']);
    }
}
