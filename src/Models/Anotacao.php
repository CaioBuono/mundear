<?php

namespace Models;

use Exceptions\DadosInvalidosException;
use Utils\Validador;

class Anotacao
{
    const TAMANHO_MAXIMO_TEXTO = 1000;

    private int $idAnotacao;

    private string $data;

    private string $texto;

    public function __construct(string $data, string $texto, int $idAnotacao = 0)
    {
        if(!Validador::data($data)){
            throw new DadosInvalidosException('Informe uma data válida para a anotação.', 'RN06');
        }

        $this->idAnotacao = $idAnotacao;
        $this->data       = $data;
        $this->editarTexto($texto);
    }

    public function getIdAnotacao(): int { return $this->idAnotacao; }
    public function setIdAnotacao(int $idAnotacao): void { $this->idAnotacao = $idAnotacao; }
    public function getData(): string { return $this->data; }
    public function getTexto(): string { return $this->texto; }

    public function editarTexto(string $texto): void
    {
        $texto = trim($texto);

        if(strlen($texto) > self::TAMANHO_MAXIMO_TEXTO){
            throw new DadosInvalidosException('A anotação pode ter no máximo ' . self::TAMANHO_MAXIMO_TEXTO . ' caracteres.', 'RN06');
        }

        $this->texto = $texto;
    }

    public function toArray(): array
    {
        return [
            'idAnotacao' => $this->idAnotacao,
            'data'       => $this->data,
            'texto'      => $this->texto
        ];
    }

    public static function fromArray(array $registro): Anotacao
    {
        return new Anotacao($registro['data'], $registro['texto'], (int) $registro['idAnotacao']);
    }
}
