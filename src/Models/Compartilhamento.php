<?php

namespace Models;

class Compartilhamento
{
    private int $idCompartilhamento;

    private string $token;

    private string $urlLink;

    private ?string $dataExpiracao = null;

    private bool $statusAtivo = true;

    private string $dataCriacao;

    public static function criar(int $idCompartilhamento, ?string $dataExpiracao): Compartilhamento
    {
        $obCompartilhamento                     = new Compartilhamento();
        $obCompartilhamento->idCompartilhamento = $idCompartilhamento;
        $obCompartilhamento->dataExpiracao      = $dataExpiracao;
        $obCompartilhamento->dataCriacao        = date('Y-m-d H:i:s');
        $obCompartilhamento->gerarLinkAcesso();

        return $obCompartilhamento;
    }

    public function gerarLinkAcesso(): string
    {
        if(empty($this->token)){
            $this->token   = bin2hex(random_bytes(16));
            $this->urlLink = '/compartilhado?token=' . $this->token;
        }

        return $this->urlLink;
    }

    public function revogarAcesso(): void
    {
        $this->statusAtivo = false;
    }

    public function isExpirado(): bool
    {
        return $this->dataExpiracao !== null && strtotime($this->dataExpiracao) < time();
    }

    public function isValido(): bool
    {
        return $this->statusAtivo && !$this->isExpirado();
    }

    public function confereToken(string $token): bool
    {
        return hash_equals($this->token, $token);
    }

    public function getIdCompartilhamento(): int { return $this->idCompartilhamento; }
    public function getUrlLink(): string { return $this->urlLink; }
    public function getDataExpiracao(): ?string { return $this->dataExpiracao; }
    public function isStatusAtivo(): bool { return $this->statusAtivo; }
    public function getDataCriacao(): string { return $this->dataCriacao; }

    public static function fromArray(array $dados): Compartilhamento
    {
        $obCompartilhamento                     = new Compartilhamento();
        $obCompartilhamento->idCompartilhamento = (int) $dados['idCompartilhamento'];
        $obCompartilhamento->token              = $dados['token'];
        $obCompartilhamento->urlLink            = $dados['urlLink'];
        $obCompartilhamento->dataExpiracao      = $dados['dataExpiracao'] ?? null;
        $obCompartilhamento->statusAtivo        = (bool) $dados['statusAtivo'];
        $obCompartilhamento->dataCriacao        = $dados['dataCriacao'];

        return $obCompartilhamento;
    }

    public function toArray(): array
    {
        return [
            'idCompartilhamento' => $this->idCompartilhamento,
            'token'              => $this->token,
            'urlLink'            => $this->urlLink,
            'dataExpiracao'      => $this->dataExpiracao,
            'statusAtivo'        => $this->statusAtivo,
            'dataCriacao'        => $this->dataCriacao
        ];
    }
}
