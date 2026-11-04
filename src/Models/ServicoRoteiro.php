<?php

namespace Models;

/**
 * Contrato comum aos serviços que podem virar um ItemRoteiro
 * (Transporte, Hospedagem, GuiaTuristico e Passeio).
 *
 * Graças a ele, ItemRoteiro::criarDeServico() trata todos os serviços de
 * forma polimórfica, sem precisar saber qual é a classe concreta.
 */
interface ServicoRoteiro
{
    /** transporte | hospedagem | guia | passeio */
    public function getTipoServico(): string;

    public function getIdServico(): int|string;

    public function getNomeServico(): string;

    public function getDetalhesServico(): string;

    public function getValorUnitario(): float;
}
