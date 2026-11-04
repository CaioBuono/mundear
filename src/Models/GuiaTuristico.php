<?php

namespace Models;

use BD\TabelaJson;
use Utils\Formatador;
use Utils\Texto;
use Utils\Validador;

class GuiaTuristico implements ServicoRoteiro
{
    private ?int $idGuia = null;

    private string $nome = '';

    private string $telefone = '';

    private string $especialidade = '';

    private string $cidadeAtuacao = '';

    private float $valorDiaria = 0;

    /** Dia da semana sem atendimento (0 = domingo ... 6 = sábado) ou null. */
    private ?int $diaFolga = null;

    private bool $ativo = true;

    private ?string $dataCadastro = null;

    private static function getTabela(): TabelaJson
    {
        return new TabelaJson('guia', 'idGuia');
    }

    public function getIdGuia(): ?int { return $this->idGuia; }
    public function getNome(): string { return $this->nome; }
    public function getTelefone(): string { return $this->telefone; }
    public function getEspecialidade(): string { return $this->especialidade; }
    public function getCidadeAtuacao(): string { return $this->cidadeAtuacao; }
    public function getValorDiaria(): float { return $this->valorDiaria; }
    public function getDiaFolga(): ?int { return $this->diaFolga; }
    public function isAtivo(): bool { return $this->ativo; }

    public function setAtivo(bool $ativo): void
    {
        $this->ativo = $ativo;
    }

    // ------------------------------------------------------------------
    // Métodos do diagrama de classes
    // ------------------------------------------------------------------

    /**
     * O guia está disponível se estiver ativo e a data não cair no seu dia de folga.
     */
    public function consultarDisponibilidade(string $data): bool
    {
        if(!$this->ativo) return false;

        return $this->diaFolga === null || (int) date('w', strtotime($data)) !== $this->diaFolga;
    }

    public function exibirPerfil(): string
    {
        return sprintf(
            '%s — %s em %s · %s · %s/dia',
            $this->nome,
            $this->especialidade,
            $this->cidadeAtuacao,
            Formatador::telefone($this->telefone),
            Formatador::moeda($this->valorDiaria)
        );
    }

    public function getDescricaoFolga(): string
    {
        return $this->diaFolga === null ? 'Atende todos os dias' : 'Folga: ' . Formatador::nomeDiaSemana($this->diaFolga);
    }

    // ------------------------------------------------------------------
    // ServicoRoteiro
    // ------------------------------------------------------------------

    public function getTipoServico(): string { return ItemRoteiro::TIPO_GUIA; }
    public function getIdServico(): int|string { return (int) $this->idGuia; }
    public function getNomeServico(): string { return 'Guia ' . $this->nome; }
    public function getDetalhesServico(): string { return $this->especialidade . ' · ' . $this->cidadeAtuacao . ' · ' . Formatador::telefone($this->telefone); }
    public function getValorUnitario(): float { return $this->valorDiaria; }

    // ------------------------------------------------------------------
    // Persistência (RF10 — CRUD feito pelo Administrador)
    // ------------------------------------------------------------------

    /**
     * RN05: lista de mensagens de erro para os dados informados (vazia = válido).
     */
    public static function validarDados(array $dados): array
    {
        $erros = [];

        if(strlen(trim($dados['nome'] ?? '')) < 3)           $erros[] = 'Informe o nome do guia (mínimo 3 caracteres).';
        if(!Validador::telefone($dados['telefone'] ?? ''))    $erros[] = 'Informe um telefone válido com DDD (10 ou 11 dígitos).';
        if(trim($dados['especialidade'] ?? '') === '')        $erros[] = 'Informe a especialidade do guia.';
        if(!Destino::buscarPorCidade($dados['cidadeAtuacao'] ?? '')) $erros[] = 'Selecione uma cidade de atuação válida.';
        if(!Validador::decimalPositivo(str_replace(',', '.', $dados['valorDiaria'] ?? ''))) $erros[] = 'Informe um valor de diária maior que zero.';

        $diaFolga = $dados['diaFolga'] ?? '';
        if($diaFolga !== '' and !Validador::inteiroEntre($diaFolga, 0, 6)) $erros[] = 'Dia de folga inválido.';

        return $erros;
    }

    public function preencherDados(array $dados): void
    {
        $diaFolga = $dados['diaFolga'] ?? '';

        $this->nome          = trim($dados['nome']);
        $this->telefone      = preg_replace('/\D/', '', $dados['telefone']);
        $this->especialidade = trim($dados['especialidade']);
        $this->cidadeAtuacao = Destino::buscarPorCidade($dados['cidadeAtuacao'])->getCidade();
        $this->valorDiaria   = round((float) str_replace(',', '.', $dados['valorDiaria']), 2);
        $this->diaFolga      = $diaFolga === '' ? null : (int) $diaFolga;
        $this->ativo         = !empty($dados['ativo']);
    }

    public function salvar(): bool
    {
        if($this->idGuia === null){
            $this->dataCadastro = date('Y-m-d H:i:s');
            $this->idGuia       = self::getTabela()->inserir($this->toArray());
            return $this->idGuia > 0;
        }

        return self::getTabela()->atualizar($this->idGuia, $this->toArray());
    }

    public function excluir(): bool
    {
        return $this->idGuia !== null && self::getTabela()->excluir($this->idGuia);
    }

    // ------------------------------------------------------------------
    // Consultas (RF04)
    // ------------------------------------------------------------------

    public static function getPorId(int $idGuia): ?GuiaTuristico
    {
        $registro = self::getTabela()->buscarPorId($idGuia);
        return $registro ? self::criar($registro) : null;
    }

    /**
     * Pesquisa por localidade (e, opcionalmente, especialidade).
     *
     * @return GuiaTuristico[]
     */
    public static function pesquisar(string $cidade = '', string $especialidade = '', ?bool $ativo = true): array
    {
        $registros = self::getTabela()->listar(function($guia) use ($cidade, $especialidade, $ativo){
            if($ativo !== null and (bool) $guia['ativo'] !== $ativo)                   return false;
            if($cidade !== '' and !Texto::contem($guia['cidadeAtuacao'], $cidade))      return false;
            if($especialidade !== '' and !Texto::contem($guia['especialidade'], $especialidade)) return false;
            return true;
        });

        usort($registros, fn($a, $b) => [$a['cidadeAtuacao'], $a['nome']] <=> [$b['cidadeAtuacao'], $b['nome']]);

        return array_map(fn($registro) => self::criar($registro), $registros);
    }

    private static function criar(array $registro): GuiaTuristico
    {
        $obGuia                = new GuiaTuristico();
        $obGuia->idGuia        = (int) $registro['idGuia'];
        $obGuia->nome          = $registro['nome'];
        $obGuia->telefone      = $registro['telefone'];
        $obGuia->especialidade = $registro['especialidade'];
        $obGuia->cidadeAtuacao = $registro['cidadeAtuacao'];
        $obGuia->valorDiaria   = (float) $registro['valorDiaria'];
        $obGuia->diaFolga      = isset($registro['diaFolga']) ? (int) $registro['diaFolga'] : null;
        $obGuia->ativo         = (bool) ($registro['ativo'] ?? true);
        $obGuia->dataCadastro  = $registro['dataCadastro'] ?? null;

        return $obGuia;
    }

    private function toArray(): array
    {
        return [
            'idGuia'        => $this->idGuia,
            'nome'          => $this->nome,
            'telefone'      => $this->telefone,
            'especialidade' => $this->especialidade,
            'cidadeAtuacao' => $this->cidadeAtuacao,
            'valorDiaria'   => $this->valorDiaria,
            'diaFolga'      => $this->diaFolga,
            'ativo'         => $this->ativo,
            'dataCadastro'  => $this->dataCadastro
        ];
    }
}
