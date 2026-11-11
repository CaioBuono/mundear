<?php

namespace Models;

use BD\TabelaJson;
use Utils\Formatador;
use Utils\Texto;
use Utils\Validador;

class GuiaTuristico implements ServicoRoteiro
{
    const UNIDADES_VALOR = ['meio período', 'dia', 'noite', 'hora'];

    private ?int $idGuia = null;

    private string $nome = '';

    private string $telefone = '';

    private string $especialidade = '';

    private string $cidadeAtuacao = '';

    private string $email = '';

    private string $idiomas = '';

    private string $regioes = '';

    private string $apresentacao = '';

    private float $valorDiaria = 0;

    private string $unidadeValor = 'dia';

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
    public function getEmail(): string { return $this->email; }
    public function getIdiomas(): string { return $this->idiomas; }
    public function getRegioes(): string { return $this->regioes; }
    public function getApresentacao(): string { return $this->apresentacao; }
    public function getUnidadeValor(): string { return $this->unidadeValor; }

    public function setAtivo(bool $ativo): void
    {
        $this->ativo = $ativo;
    }

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

    public function getTipoServico(): string { return ItemRoteiro::TIPO_GUIA; }
    public function getIdServico(): int|string { return (int) $this->idGuia; }
    public function getNomeServico(): string { return 'Guia ' . $this->nome; }
    public function getDetalhesServico(): string { return $this->especialidade . ' · ' . $this->cidadeAtuacao . ' · ' . Formatador::telefone($this->telefone); }
    public function getValorUnitario(): float { return $this->valorDiaria; }

    public static function validarDados(array $dados): array
    {
        $erros = [];

        if(strlen(trim($dados['nome'] ?? '')) < 3)           $erros[] = 'Informe o nome do guia (mínimo 3 caracteres).';
        if(!Validador::telefone($dados['telefone'] ?? ''))    $erros[] = 'Informe um telefone válido com DDD (10 ou 11 dígitos).';
        if(trim($dados['especialidade'] ?? '') === '')        $erros[] = 'Informe a especialidade do guia.';
        if(!Destino::buscarPorCidade($dados['cidadeAtuacao'] ?? '')) $erros[] = 'Selecione uma cidade de atuação válida.';
        if(!Validador::decimalPositivo(str_replace(',', '.', $dados['valorDiaria'] ?? ''))) $erros[] = 'Informe um valor de diária maior que zero.';

        $email = trim($dados['email'] ?? '');
        if($email !== '' and !Validador::email($email)) $erros[] = 'Informe um e-mail válido para o guia.';

        $diaFolga = $dados['diaFolga'] ?? '';
        if($diaFolga !== '' and !Validador::inteiroEntre($diaFolga, 0, 6)) $erros[] = 'Dia de folga inválido.';

        if(!in_array($dados['unidadeValor'] ?? '', self::UNIDADES_VALOR, true)) $erros[] = 'Escolha como o valor do guia é cobrado.';
        if(strlen(trim($dados['apresentacao'] ?? '')) > 160) $erros[] = 'A apresentação pode ter no máximo 160 caracteres.';

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
        $this->unidadeValor  = $dados['unidadeValor'];
        $this->diaFolga      = $diaFolga === '' ? null : (int) $diaFolga;
        $this->ativo         = !empty($dados['ativo']);
        $this->email         = strtolower(trim($dados['email'] ?? ''));
        $this->idiomas       = trim($dados['idiomas'] ?? '');
        $this->regioes       = trim($dados['regioes'] ?? '');
        $this->apresentacao  = trim($dados['apresentacao'] ?? '');
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

    public static function getPorId(int $idGuia): ?GuiaTuristico
    {
        $registro = self::getTabela()->buscarPorId($idGuia);
        return $registro ? self::criar($registro) : null;
    }

    public static function pesquisar(string $cidade = '', string $termo = '', ?bool $ativo = true): array
    {
        $registros = self::getTabela()->listar(function($guia) use ($cidade, $termo, $ativo){
            if($ativo !== null and (bool) $guia['ativo'] !== $ativo)              return false;
            if($cidade !== '' and !Texto::contem($guia['cidadeAtuacao'], $cidade)) return false;
            if($termo === '')                                                       return true;

            $camposPesquisados = [$guia['nome'], $guia['especialidade'], $guia['idiomas'] ?? '', $guia['regioes'] ?? ''];
            foreach($camposPesquisados as $campo){
                if(Texto::contem($campo, $termo)) return true;
            }

            return false;
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
        $obGuia->unidadeValor  = $registro['unidadeValor'] ?? 'dia';
        $obGuia->diaFolga      = isset($registro['diaFolga']) ? (int) $registro['diaFolga'] : null;
        $obGuia->ativo         = (bool) ($registro['ativo'] ?? true);
        $obGuia->dataCadastro  = $registro['dataCadastro'] ?? null;
        $obGuia->email         = $registro['email'] ?? '';
        $obGuia->idiomas       = $registro['idiomas'] ?? '';
        $obGuia->regioes       = $registro['regioes'] ?? '';
        $obGuia->apresentacao  = $registro['apresentacao'] ?? '';

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
            'unidadeValor'  => $this->unidadeValor,
            'diaFolga'      => $this->diaFolga,
            'ativo'         => $this->ativo,
            'dataCadastro'  => $this->dataCadastro,
            'email'         => $this->email,
            'idiomas'       => $this->idiomas,
            'regioes'       => $this->regioes,
            'apresentacao'  => $this->apresentacao
        ];
    }
}
