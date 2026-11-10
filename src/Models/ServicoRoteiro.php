<?php

namespace Models;

interface ServicoRoteiro
{
    public function getTipoServico(): string;

    public function getIdServico(): int|string;

    public function getNomeServico(): string;

    public function getDetalhesServico(): string;

    public function getValorUnitario(): float;
}
