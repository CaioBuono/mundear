<?php

namespace Exceptions;

abstract class RegraNegocioException extends \DomainException
{
    private string $regra;

    public function __construct(string $mensagem, string $regra = '')
    {
        parent::__construct($mensagem);
        $this->regra = $regra;
    }

    public function getRegra(): string
    {
        return $this->regra;
    }
}
