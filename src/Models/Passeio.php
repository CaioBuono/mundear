<?php

namespace Models;

use BD\TabelaJson;
use Utils\Formatador;

class Passeio implements ServicoRoteiro
{
    private int $idPasseio;

    private int $idDestino;

    private string $nomeAtracao;

    private string $localizacao;

    private int $duracaoMinutos;

    private float $precoIngresso;

    /** Dia da semana em que a atração fecha (0 = domingo ... 6 = sábado) ou null. */
    private ?int $diaFechado = null;

    private static function getTabela(): TabelaJson
    {
        return new TabelaJson('passeio', 'idPasseio');
    }

    public function getIdPasseio(): int { return $this->idPasseio; }
    public function getIdDestino(): int { return $this->idDestino; }
    public function getNomeAtracao(): string { return $this->nomeAtracao; }
    public function getLocalizacao(): string { return $this->localizacao; }
    public function getDuracaoMinutos(): int { return $this->duracaoMinutos; }
    public function getPrecoIngresso(): float { return $this->precoIngresso; }
    public function getDiaFechado(): ?int { return $this->diaFechado; }

    public function getDestino(): ?Destino
    {
        return Destino::getPorId($this->idDestino);
    }

    public function verificarDisponibilidade(string $data): bool
    {
        return $this->diaFechado === null || (int) date('w', strtotime($data)) !== $this->diaFechado;
    }

    public function exibirDetalhes(): string
    {
        return sprintf(
            '%s · %s · duração %s · %s',
            $this->nomeAtracao,
            $this->localizacao,
            Formatador::duracao($this->duracaoMinutos),
            $this->precoIngresso > 0 ? Formatador::moeda($this->precoIngresso) . ' por pessoa' : 'gratuito'
        );
    }

    public function getDescricaoFuncionamento(): string
    {
        return $this->diaFechado === null ? 'Aberto todos os dias' : 'Fechado: ' . Formatador::nomeDiaSemana($this->diaFechado);
    }

    // ServicoRoteiro
    public function getTipoServico(): string { return ItemRoteiro::TIPO_PASSEIO; }
    public function getIdServico(): int|string { return $this->idPasseio; }
    public function getNomeServico(): string { return $this->nomeAtracao; }
    public function getDetalhesServico(): string { return $this->localizacao . ' · ' . Formatador::duracao($this->duracaoMinutos); }
    public function getValorUnitario(): float { return $this->precoIngresso; }

    public static function getPorId(int $idPasseio): ?Passeio
    {
        $registro = self::getTabela()->buscarPorId($idPasseio);
        return $registro ? self::criar($registro) : null;
    }

    /**
     * @return Passeio[]
     */
    public static function listarPorDestino(int $idDestino): array
    {
        return array_map(
            fn($registro) => self::criar($registro),
            self::getTabela()->listar(fn($passeio) => (int) $passeio['idDestino'] === $idDestino)
        );
    }

    private static function criar(array $registro): Passeio
    {
        $obPasseio                 = new Passeio();
        $obPasseio->idPasseio      = (int) $registro['idPasseio'];
        $obPasseio->idDestino      = (int) $registro['idDestino'];
        $obPasseio->nomeAtracao    = $registro['nomeAtracao'];
        $obPasseio->localizacao    = $registro['localizacao'];
        $obPasseio->duracaoMinutos = (int) $registro['duracaoMinutos'];
        $obPasseio->precoIngresso  = (float) $registro['precoIngresso'];
        $obPasseio->diaFechado     = isset($registro['diaFechado']) ? (int) $registro['diaFechado'] : null;

        return $obPasseio;
    }
}
