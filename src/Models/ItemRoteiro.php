<?php

namespace Models;

use Exceptions\RegraNegocioException;
use Utils\Formatador;
use Utils\Validador;

/**
 * Ligação entre o Roteiro e um serviço (composição Roteiro ◆— ItemRoteiro).
 *
 * Cada item representa apenas UM tipo de serviço e guarda uma "fotografia"
 * dele (nome, detalhes e valor no momento da escolha). Assim o roteiro continua
 * correto mesmo que o serviço original mude de preço ou seja inativado.
 */
class ItemRoteiro
{
    const TIPO_TRANSPORTE = 'transporte';
    const TIPO_HOSPEDAGEM = 'hospedagem';
    const TIPO_GUIA       = 'guia';
    const TIPO_PASSEIO    = 'passeio';

    const QUANTIDADE_MAXIMA_PESSOAS = 20;

    private ?int $idItem = null;

    private string $tipoServico;

    private int|string $idServico;

    private string $descricao;

    private string $detalhes;

    private string $dataAgendada;

    /** Data de saída (check-out) — usada apenas por hospedagens. */
    private ?string $dataFim = null;

    /** Horário de partida — usado apenas por transportes. */
    private ?string $horario = null;

    private int $quantidadePessoas = 1;

    private int $quantidadeDiarias = 1;

    private float $valorUnitario = 0;

    private float $subtotal = 0;

    public static function criarDeServico(ServicoRoteiro $servico, string $dataAgendada, int $quantidadePessoas, ?string $dataFim = null, ?string $horario = null): ItemRoteiro
    {
        $obItem                    = new ItemRoteiro();
        $obItem->tipoServico       = $servico->getTipoServico();
        $obItem->idServico         = $servico->getIdServico();
        $obItem->descricao         = $servico->getNomeServico();
        $obItem->detalhes          = $servico->getDetalhesServico();
        $obItem->valorUnitario     = $servico->getValorUnitario();
        $obItem->dataAgendada      = $dataAgendada;
        $obItem->dataFim           = $dataFim;
        $obItem->horario           = $horario;
        $obItem->quantidadeDiarias = $dataFim ? max(1, Validador::diasEntre($dataAgendada, $dataFim)) : 1;
        $obItem->atualizarQuantidade($quantidadePessoas);

        return $obItem;
    }

    // ------------------------------------------------------------------
    // Métodos do diagrama de classes
    // ------------------------------------------------------------------

    /**
     * Transporte e passeio são cobrados por pessoa; hospedagem e guia, por diária.
     */
    public function calcularSubtotal(): float
    {
        $quantidade = $this->isCobradoPorPessoa() ? $this->quantidadePessoas : $this->quantidadeDiarias;
        $this->subtotal = round($this->valorUnitario * $quantidade, 2);

        return $this->subtotal;
    }

    public function atualizarQuantidade(int $quantidadePessoas): void
    {
        if($quantidadePessoas < 1 or $quantidadePessoas > self::QUANTIDADE_MAXIMA_PESSOAS){
            throw new RegraNegocioException('A quantidade de pessoas deve ficar entre 1 e ' . self::QUANTIDADE_MAXIMA_PESSOAS . '.');
        }

        $this->quantidadePessoas = $quantidadePessoas;
        $this->calcularSubtotal();
    }

    public function obterResumoItem(): string
    {
        return sprintf(
            '%s · %s · %s · %s',
            $this->getRotuloTipo(),
            $this->descricao,
            $this->getDescricaoQuantidade(),
            Formatador::moeda($this->subtotal)
        );
    }

    // ------------------------------------------------------------------
    // Apoio
    // ------------------------------------------------------------------

    public function isCobradoPorPessoa(): bool
    {
        return in_array($this->tipoServico, [self::TIPO_TRANSPORTE, self::TIPO_PASSEIO], true);
    }

    public function getRotuloTipo(): string
    {
        return self::getRotulosTipo()[$this->tipoServico] ?? 'Serviço';
    }

    public static function getRotulosTipo(): array
    {
        return [
            self::TIPO_TRANSPORTE => 'Transporte',
            self::TIPO_HOSPEDAGEM => 'Hospedagem',
            self::TIPO_GUIA       => 'Guia turístico',
            self::TIPO_PASSEIO    => 'Passeio'
        ];
    }

    public function getDescricaoQuantidade(): string
    {
        $valor   = Formatador::moeda($this->valorUnitario);
        $pessoas = $this->quantidadePessoas . ($this->quantidadePessoas > 1 ? ' pessoas' : ' pessoa');
        $diarias = $this->quantidadeDiarias . ($this->quantidadeDiarias > 1 ? ' diárias' : ' diária');

        return match($this->tipoServico){
            self::TIPO_TRANSPORTE => $pessoas . ' × ' . $valor,
            self::TIPO_PASSEIO    => $this->valorUnitario > 0 ? $pessoas . ' × ' . $valor : $pessoas . ' · entrada gratuita',
            self::TIPO_HOSPEDAGEM => $diarias . ' × ' . $valor . ' · ' . $pessoas,
            default               => $diarias . ' × ' . $valor . ' · grupo de ' . $pessoas,
        };
    }

    public function getIdItem(): ?int { return $this->idItem; }
    public function setIdItem(int $idItem): void { $this->idItem = $idItem; }
    public function getTipoServico(): string { return $this->tipoServico; }
    public function getIdServico(): int|string { return $this->idServico; }
    public function getDescricao(): string { return $this->descricao; }
    public function getDetalhes(): string { return $this->detalhes; }
    public function getDataAgendada(): string { return $this->dataAgendada; }
    public function getDataFim(): ?string { return $this->dataFim; }
    public function getHorario(): ?string { return $this->horario; }
    public function getQuantidadePessoas(): int { return $this->quantidadePessoas; }
    public function getQuantidadeDiarias(): int { return $this->quantidadeDiarias; }
    public function getValorUnitario(): float { return $this->valorUnitario; }
    public function getSubtotal(): float { return $this->subtotal; }

    public static function fromArray(array $dados): ItemRoteiro
    {
        $obItem                    = new ItemRoteiro();
        $obItem->idItem            = (int) $dados['idItem'];
        $obItem->tipoServico       = $dados['tipoServico'];
        $obItem->idServico         = $dados['idServico'];
        $obItem->descricao         = $dados['descricao'];
        $obItem->detalhes          = $dados['detalhes'] ?? '';
        $obItem->dataAgendada      = $dados['dataAgendada'];
        $obItem->dataFim           = $dados['dataFim'] ?? null;
        $obItem->horario           = $dados['horario'] ?? null;
        $obItem->quantidadePessoas = (int) $dados['quantidadePessoas'];
        $obItem->quantidadeDiarias = (int) ($dados['quantidadeDiarias'] ?? 1);
        $obItem->valorUnitario     = (float) $dados['valorUnitario'];
        $obItem->calcularSubtotal();

        return $obItem;
    }

    public function toArray(): array
    {
        return [
            'idItem'            => $this->idItem,
            'tipoServico'       => $this->tipoServico,
            'idServico'         => $this->idServico,
            'descricao'         => $this->descricao,
            'detalhes'          => $this->detalhes,
            'dataAgendada'      => $this->dataAgendada,
            'dataFim'           => $this->dataFim,
            'horario'           => $this->horario,
            'quantidadePessoas' => $this->quantidadePessoas,
            'quantidadeDiarias' => $this->quantidadeDiarias,
            'valorUnitario'     => $this->valorUnitario,
            'subtotal'          => $this->subtotal
        ];
    }
}
