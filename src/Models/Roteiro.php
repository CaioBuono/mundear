<?php

namespace Models;

use BD\TabelaJson;
use Exceptions\DadosInvalidosException;
use Exceptions\EstadoInvalidoException;
use Exceptions\OperacaoNaoPermitidaException;
use Exceptions\RegistroNaoEncontradoException;
use Utils\Formatador;
use Utils\Texto;
use Utils\Validador;

class Roteiro
{
    const STATUS_EM_PLANEJAMENTO  = 'Em planejamento';
    const STATUS_PRONTO_PARA_VIAJAR = 'Pronto para viajar';
    const STATUS_VIAGEM_REALIZADA = 'Viagem realizada';

    const ETAPAS = ['Transporte', 'Hospedagem', 'Turismo', 'Revisão'];

    const DIAS_VALIDADE_LINK = 30;

    const DURACAO_MAXIMA_DIAS = 60;

    private ?int $idRoteiro = null;

    private int $idUsuario;

    private string $titulo = '';

    private string $dataInicio = '';

    private string $dataFim = '';

    private string $origem = '';

    private int $quantidadePessoas = 1;

    private ?float $orcamento = null;

    private string $etapaAtual = 'Transporte';

    private float $valorTotalEstimado = 0;

    private string $status = self::STATUS_EM_PLANEJAMENTO;

    private array $idDestinos = [];

    private array $itens = [];

    private array $gastosExtras = [];

    private array $anotacoes = [];

    private ?Compartilhamento $compartilhamento = null;

    private ?string $dataCriacao = null;

    private ?string $dataAtualizacao = null;

    private static function getTabela(): TabelaJson
    {
        return new TabelaJson('roteiro', 'idRoteiro');
    }

    public function getIdRoteiro(): ?int { return $this->idRoteiro; }
    public function getIdUsuario(): int { return $this->idUsuario; }
    public function getTitulo(): string { return $this->titulo; }
    public function getDataInicio(): string { return $this->dataInicio; }
    public function getDataFim(): string { return $this->dataFim; }
    public function getValorTotalEstimado(): float { return $this->valorTotalEstimado; }
    public function getOrigem(): string { return $this->origem; }
    public function getQuantidadePessoas(): int { return $this->quantidadePessoas; }
    public function getOrcamento(): ?float { return $this->orcamento; }
    public function getEtapaAtual(): string { return $this->etapaAtual; }
    public function getGastosExtras(): array { return $this->gastosExtras; }
    public function getAnotacoes(): array { return $this->anotacoes; }

    public function getStatus(): string
    {
        if($this->status === self::STATUS_PRONTO_PARA_VIAJAR and $this->dataFim < date('Y-m-d')){
            return self::STATUS_VIAGEM_REALIZADA;
        }

        return $this->status;
    }
    public function getIdDestinos(): array { return $this->idDestinos; }
    public function getCompartilhamento(): ?Compartilhamento { return $this->compartilhamento; }
    public function getDataAtualizacao(): ?string { return $this->dataAtualizacao; }

    public function getItens(): array { return $this->itens; }

    public function isFinalizado(): bool
    {
        return $this->status !== self::STATUS_EM_PLANEJAMENTO;
    }

    public function possuiLinkAtivo(): bool
    {
        return $this->compartilhamento !== null && $this->compartilhamento->isValido();
    }

    public function getAnotacaoDoDia(string $data): ?Anotacao
    {
        foreach($this->anotacoes as $obAnotacao){
            if($obAnotacao->getData() === $data){
                return $obAnotacao;
            }
        }

        return null;
    }

    public static function validarDados(array $dados, ?Roteiro $roteiroAtual = null): array
    {
        $erros  = [];
        $titulo = trim($dados['titulo'] ?? '');
        $inicio = $dados['dataInicio'] ?? '';
        $fim    = $dados['dataFim'] ?? '';

        if(strlen($titulo) < 3)  $erros[] = 'Informe o nome da viagem (mínimo 3 caracteres).';
        if(strlen($titulo) > 80) $erros[] = 'O nome da viagem pode ter no máximo 80 caracteres.';

        if(!Validador::data($inicio)) $erros[] = 'Informe uma data de início válida.';
        if(!Validador::data($fim))    $erros[] = 'Informe uma data de término válida.';

        if(Validador::data($inicio) and Validador::data($fim)){
            $dias = Validador::diasEntre($inicio, $fim);

            if($dias < 0){
                $erros[] = 'A data de término deve ser igual ou posterior à data de início.';
            }elseif($dias + 1 > self::DURACAO_MAXIMA_DIAS){
                $erros[] = 'A viagem pode ter no máximo ' . self::DURACAO_MAXIMA_DIAS . ' dias.';
            }

            $inicioAlterado = !$roteiroAtual || $roteiroAtual->dataInicio !== $inicio;
            if($inicioAlterado and $inicio < date('Y-m-d')){
                $erros[] = 'A data de início não pode estar no passado.';
            }
        }

        if(array_key_exists('quantidadePessoas', $dados) and !Validador::inteiroEntre($dados['quantidadePessoas'], 1, ItemRoteiro::QUANTIDADE_MAXIMA_PESSOAS)){
            $erros[] = 'O número de pessoas deve ficar entre 1 e ' . ItemRoteiro::QUANTIDADE_MAXIMA_PESSOAS . '.';
        }

        $orcamento = str_replace(',', '.', (string) ($dados['orcamento'] ?? ''));
        if($orcamento !== '' and !Validador::decimalPositivo($orcamento)){
            $erros[] = 'O orçamento, quando informado, deve ser maior que zero.';
        }

        if(strlen(trim($dados['origem'] ?? '')) > 80) $erros[] = 'A origem pode ter no máximo 80 caracteres.';

        $idDestinos = array_unique(array_map('intval', (array) ($dados['destinos'] ?? [])));
        if(empty($idDestinos)){
            $erros[] = 'Informe o destino da viagem.';
        }elseif(count(Destino::getPorIds($idDestinos)) !== count($idDestinos)){
            $erros[] = 'Um dos destinos selecionados é inválido.';
        }

        return $erros;
    }

    public static function criar(int $idUsuario, array $dados): Roteiro
    {
        $erros = self::validarDados($dados);
        if(!empty($erros)){
            throw new DadosInvalidosException(implode(' ', $erros), 'RN02');
        }

        $obRoteiro              = new Roteiro();
        $obRoteiro->idUsuario   = $idUsuario;
        $obRoteiro->dataCriacao = date('Y-m-d H:i:s');
        $obRoteiro->preencherDados($dados);
        $obRoteiro->salvar();

        return $obRoteiro;
    }

    public function atualizarDados(array $dados): void
    {
        $erros = self::validarDados($dados, $this);
        if(!empty($erros)){
            throw new DadosInvalidosException(implode(' ', $erros), 'RN02');
        }

        $obSimulacao = clone $this;
        $obSimulacao->preencherDados($dados);

        foreach($obSimulacao->gastosExtras as $obGasto){
            $obSimulacao->validarDataNoPeriodo($obGasto->getData(), $obGasto->getDescricao());
        }

        foreach($obSimulacao->anotacoes as $obAnotacao){
            $obSimulacao->validarDataNoPeriodo($obAnotacao->getData(), 'A anotação');
        }

        foreach($this->itens as $obItem){
            $obSimulacao->validarPeriodoItem($obItem);

            if($obItem->getTipoServico() === ItemRoteiro::TIPO_GUIA){
                $obGuia = GuiaTuristico::getPorId((int) $obItem->getIdServico());
                if($obGuia and !$obSimulacao->atendeCidade($obGuia->getCidadeAtuacao())){
                    throw new OperacaoNaoPermitidaException(sprintf(
                        'O guia %s atua em %s. Remova-o do roteiro antes de retirar esse destino.',
                        $obGuia->getNome(),
                        $obGuia->getCidadeAtuacao()
                    ), 'RN05');
                }
            }
        }

        $this->preencherDados($dados);
    }

    private function preencherDados(array $dados): void
    {
        $this->titulo     = trim($dados['titulo']);
        $this->dataInicio = $dados['dataInicio'];
        $this->dataFim    = $dados['dataFim'];
        $this->idDestinos = array_values(array_unique(array_map('intval', (array) $dados['destinos'])));
        $this->origem     = trim($dados['origem'] ?? $this->origem);

        if(array_key_exists('quantidadePessoas', $dados)){
            $this->quantidadePessoas = (int) $dados['quantidadePessoas'];
        }

        if(array_key_exists('orcamento', $dados)){
            $orcamento       = str_replace(',', '.', (string) $dados['orcamento']);
            $this->orcamento = $orcamento === '' ? null : round((float) $orcamento, 2);
        }
    }

    public function avancarEtapa(): void
    {
        $posicao = array_search($this->etapaAtual, self::ETAPAS, true);

        if($posicao === false or $posicao === count(self::ETAPAS) - 1){
            throw new EstadoInvalidoException('O roteiro já está na etapa de revisão.', 'RN11');
        }

        $this->etapaAtual = self::ETAPAS[$posicao + 1];
    }

    public function adicionarServico(ItemRoteiro $item): void
    {
        $this->validarPeriodoItem($item);

        if($item->getTipoServico() === ItemRoteiro::TIPO_GUIA){
            $this->validarGuia(GuiaTuristico::getPorId((int) $item->getIdServico()), $item->getDataAgendada());
        }

        $item->setIdItem($this->gerarIdItem());
        $this->itens[] = $item;

        $this->calcularValorTotal();
    }

    public function removerServico(int $idItem): bool
    {
        $quantidadeAntes = count($this->itens);
        $this->itens     = array_values(array_filter($this->itens, fn($obItem) => $obItem->getIdItem() !== $idItem));

        if(count($this->itens) === $quantidadeAntes){
            return false;
        }

        $this->calcularValorTotal();

        if(empty($this->itens) and $this->isFinalizado()){
            $this->status = self::STATUS_EM_PLANEJAMENTO;
            $this->compartilhamento?->revogarAcesso();
        }

        return true;
    }

    public function atualizarQuantidadeItem(int $idItem, int $quantidadePessoas): void
    {
        $obItem = $this->getItem($idItem);
        if(!$obItem instanceof ItemRoteiro){
            throw new RegistroNaoEncontradoException('O item informado não pertence a este roteiro.');
        }

        $obItem->atualizarQuantidade($quantidadePessoas);
        $this->calcularValorTotal();
    }

    public function adicionarGastoExtra(GastoExtra|string $gastoOuDescricao, string $categoria = '', float $valor = 0, string $data = ''): void
    {
        $obGasto = $gastoOuDescricao instanceof GastoExtra
            ? $gastoOuDescricao
            : new GastoExtra($gastoOuDescricao, $categoria, $valor, $data);

        $this->validarDataNoPeriodo($obGasto->getData(), $obGasto->getDescricao());

        if($obGasto->getIdGasto() === 0){
            $ids = array_map(fn(GastoExtra $obExistente) => $obExistente->getIdGasto(), $this->gastosExtras);
            $obGasto->setIdGasto(empty($ids) ? 1 : max($ids) + 1);
        }

        $this->gastosExtras[] = $obGasto;
        $this->calcularValorTotal();
    }

    public function removerGastoExtra(int $idGasto): bool
    {
        $quantidadeAntes    = count($this->gastosExtras);
        $this->gastosExtras = array_values(array_filter($this->gastosExtras, fn(GastoExtra $obGasto) => $obGasto->getIdGasto() !== $idGasto));

        if(count($this->gastosExtras) === $quantidadeAntes){
            return false;
        }

        $this->calcularValorTotal();
        return true;
    }

    public function adicionarAnotacao(string $data, string $texto): void
    {
        $this->validarDataNoPeriodo($data, 'A anotação');

        $obAnotacao = $this->getAnotacaoDoDia($data);

        if(trim($texto) === ''){
            $this->anotacoes = array_values(array_filter($this->anotacoes, fn(Anotacao $obExistente) => $obExistente !== $obAnotacao));
            return;
        }

        if($obAnotacao instanceof Anotacao){
            $obAnotacao->editarTexto($texto);
            return;
        }

        $ids        = array_map(fn(Anotacao $obExistente) => $obExistente->getIdAnotacao(), $this->anotacoes);
        $obAnotacao = new Anotacao($data, $texto, empty($ids) ? 1 : max($ids) + 1);

        $this->anotacoes[] = $obAnotacao;
    }

    public function calcularValorTotal(): float
    {
        $totalItens  = array_sum(array_map(fn($obItem) => $obItem->calcularSubtotal(), $this->itens));
        $totalGastos = array_sum(array_map(fn(GastoExtra $obGasto) => $obGasto->getValor(), $this->gastosExtras));

        $this->valorTotalEstimado = round($totalItens + $totalGastos, 2);

        return $this->valorTotalEstimado;
    }

    public function calcularValorPorPessoa(): float
    {
        return round($this->calcularValorTotal() / max(1, $this->quantidadePessoas), 2);
    }

    public function calcularSaldoOrcamento(): ?float
    {
        if($this->orcamento === null){
            return null;
        }

        return round($this->orcamento - $this->calcularValorTotal(), 2);
    }

    public function isOrcamentoUltrapassado(): bool
    {
        $saldo = $this->calcularSaldoOrcamento();

        return $saldo !== null && $saldo < 0;
    }

    private function validarDataNoPeriodo(string $data, string $descricao): void
    {
        if($data < $this->dataInicio or $data > $this->dataFim){
            throw new DadosInvalidosException(sprintf(
                '"%s" (%s) está fora do período da viagem, de %s a %s.',
                $descricao,
                Formatador::data($data),
                Formatador::data($this->dataInicio),
                Formatador::data($this->dataFim)
            ), 'RN04');
        }
    }

    private function validarPeriodoItem(ItemRoteiro $item): void
    {
        $inicio = $item->getDataAgendada();
        $fim    = $item->getDataFim() ?? $inicio;

        if($inicio < $this->dataInicio or $fim > $this->dataFim){
            $periodoItem = $fim !== $inicio ? Formatador::data($inicio) . ' a ' . Formatador::data($fim) : Formatador::data($inicio);

            throw new DadosInvalidosException(sprintf(
                '"%s" (%s) está fora do período da viagem, de %s a %s.',
                $item->getDescricao(),
                $periodoItem,
                Formatador::data($this->dataInicio),
                Formatador::data($this->dataFim)
            ), 'RN04');
        }
    }

    private function validarGuia(?GuiaTuristico $obGuia, string $data): void
    {
        if(!$obGuia instanceof GuiaTuristico or !$obGuia->isAtivo()){
            throw new OperacaoNaoPermitidaException('Este guia não está disponível para novas reservas.', 'RN14');
        }

        if(!$this->atendeCidade($obGuia->getCidadeAtuacao())){
            throw new OperacaoNaoPermitidaException(sprintf(
                '%s atua em %s, que não é destino deste roteiro (%s).',
                $obGuia->getNome(),
                $obGuia->getCidadeAtuacao(),
                $this->getNomesDestinos()
            ), 'RN05');
        }

        foreach($this->itens as $obItem){
            if(
                $obItem->getTipoServico() === ItemRoteiro::TIPO_GUIA and
                (int) $obItem->getIdServico() === $obGuia->getIdGuia() and
                $obItem->getDataAgendada() === $data
            ){
                throw new OperacaoNaoPermitidaException($obGuia->getNome() . ' já está reservado(a) para ' . Formatador::data($data) . ' neste roteiro.');
            }
        }
    }

    public function atendeCidade(string $cidade): bool
    {
        foreach($this->getDestinos() as $obDestino){
            if(Texto::iguais($obDestino->getCidade(), $cidade)){
                return true;
            }
        }

        return false;
    }

    private function gerarIdItem(): int
    {
        $ids = array_map(fn($obItem) => (int) $obItem->getIdItem(), $this->itens);

        return empty($ids) ? 1 : max($ids) + 1;
    }

    public function concluir(): Compartilhamento
    {
        if(empty($this->itens)){
            throw new EstadoInvalidoException('Adicione pelo menos um serviço (transporte, hospedagem, guia ou passeio) antes de concluir o roteiro e gerar o link.', 'RN03');
        }

        if($this->getStatus() === self::STATUS_VIAGEM_REALIZADA){
            throw new EstadoInvalidoException('Esta viagem já foi realizada e não pode ser concluída novamente.', 'RN11');
        }

        if(!$this->possuiLinkAtivo()){
            $dataExpiracao          = (new \DateTime('+' . self::DIAS_VALIDADE_LINK . ' days'))->format('Y-m-d H:i:s');
            $this->compartilhamento = Compartilhamento::criar(self::gerarIdCompartilhamento(), $dataExpiracao);
        }

        $this->status = self::STATUS_PRONTO_PARA_VIAJAR;

        return $this->compartilhamento;
    }

    public function revogarCompartilhamento(): void
    {
        if(!$this->compartilhamento or !$this->compartilhamento->isStatusAtivo()){
            throw new EstadoInvalidoException('Este roteiro não possui um link ativo.', 'RN08');
        }

        $this->compartilhamento->revogarAcesso();
    }

    private static function gerarIdCompartilhamento(): int
    {
        $ids = array_map(
            fn($roteiro) => (int) ($roteiro['compartilhamento']['idCompartilhamento'] ?? 0),
            self::getTabela()->listar()
        );

        return empty($ids) ? 1 : max($ids) + 1;
    }

    public function salvar(): void
    {
        $this->calcularValorTotal();
        $this->dataAtualizacao = date('Y-m-d H:i:s');

        if($this->idRoteiro === null){
            $this->idRoteiro = self::getTabela()->inserir($this->toArray());
            return;
        }

        self::getTabela()->atualizar($this->idRoteiro, $this->toArray());
    }

    public function excluir(): bool
    {
        return $this->idRoteiro !== null && self::getTabela()->excluir($this->idRoteiro);
    }

    public static function getPorId(int $idRoteiro): ?Roteiro
    {
        $registro = self::getTabela()->buscarPorId($idRoteiro);
        return $registro ? self::fromArray($registro) : null;
    }

    public static function getDoUsuario(int $idRoteiro, int $idUsuario): ?Roteiro
    {
        $obRoteiro = self::getPorId($idRoteiro);
        return ($obRoteiro and $obRoteiro->idUsuario === $idUsuario) ? $obRoteiro : null;
    }

    public static function listarPorUsuario(int $idUsuario): array
    {
        $registros = self::getTabela()->listar(fn($roteiro) => (int) $roteiro['idUsuario'] === $idUsuario);
        usort($registros, fn($a, $b) => strcmp($a['dataInicio'], $b['dataInicio']));

        return array_map(fn($registro) => self::fromArray($registro), $registros);
    }

    public static function listarTodos(): array
    {
        return array_map(fn($registro) => self::fromArray($registro), self::getTabela()->listar());
    }

    public static function getPorTokenCompartilhamento(string $token): ?Roteiro
    {
        if(!preg_match('/^[a-f0-9]{32}$/', $token)) return null;

        foreach(self::listarTodos() as $obRoteiro){
            if($obRoteiro->compartilhamento and $obRoteiro->compartilhamento->confereToken($token)){
                return $obRoteiro;
            }
        }

        return null;
    }

    public static function contarRoteirosComGuia(int $idGuia): int
    {
        $total = 0;
        foreach(self::listarTodos() as $obRoteiro){
            foreach($obRoteiro->itens as $obItem){
                if($obItem->getTipoServico() === ItemRoteiro::TIPO_GUIA and (int) $obItem->getIdServico() === $idGuia){
                    $total++;
                    break;
                }
            }
        }

        return $total;
    }

    public function getDestinos(): array
    {
        return Destino::getPorIds($this->idDestinos);
    }

    public function getNomesDestinos(): string
    {
        return implode(', ', array_map(fn($obDestino) => $obDestino->getCidade(), $this->getDestinos()));
    }

    public function getQuantidadeDias(): int
    {
        return Validador::diasEntre($this->dataInicio, $this->dataFim) + 1;
    }

    public function getDatasDaViagem(): array
    {
        $datas = [];
        $data  = new \DateTime($this->dataInicio);

        for($i = 0; $i < $this->getQuantidadeDias(); $i++){
            $datas[] = $data->format('Y-m-d');
            $data->modify('+1 day');
        }

        return $datas;
    }

    public function getItensPorData(): array
    {
        $ordemTipo = [ItemRoteiro::TIPO_TRANSPORTE => 0, ItemRoteiro::TIPO_GUIA => 1, ItemRoteiro::TIPO_PASSEIO => 2, ItemRoteiro::TIPO_HOSPEDAGEM => 3];
        $itens     = $this->itens;

        usort($itens, fn($a, $b) => [$a->getDataAgendada(), $ordemTipo[$a->getTipoServico()], (string) $a->getHorario()]
                                <=> [$b->getDataAgendada(), $ordemTipo[$b->getTipoServico()], (string) $b->getHorario()]);

        $agrupados = [];
        foreach($itens as $obItem){
            $agrupados[$obItem->getDataAgendada()][] = $obItem;
        }

        return $agrupados;
    }

    public function getTotaisPorTipo(): array
    {
        $totais = array_fill_keys(array_keys(ItemRoteiro::getRotulosTipo()), 0.0);
        foreach($this->itens as $obItem){
            $totais[$obItem->getTipoServico()] += $obItem->getSubtotal();
        }

        return $totais;
    }

    public function getItem(int $idItem): ?ItemRoteiro
    {
        foreach($this->itens as $obItem){
            if($obItem->getIdItem() === $idItem){
                return $obItem;
            }
        }

        return null;
    }

    private static function fromArray(array $dados): Roteiro
    {
        $obRoteiro                     = new Roteiro();
        $obRoteiro->idRoteiro          = (int) $dados['idRoteiro'];
        $obRoteiro->idUsuario          = (int) $dados['idUsuario'];
        $obRoteiro->titulo             = $dados['titulo'];
        $obRoteiro->dataInicio         = $dados['dataInicio'];
        $obRoteiro->dataFim            = $dados['dataFim'];
        $obRoteiro->status             = match($dados['status'] ?? ''){
            self::STATUS_PRONTO_PARA_VIAJAR, self::STATUS_VIAGEM_REALIZADA, 'finalizado' => self::STATUS_PRONTO_PARA_VIAJAR,
            default                                                                     => self::STATUS_EM_PLANEJAMENTO,
        };
        $obRoteiro->origem             = $dados['origem'] ?? '';
        $obRoteiro->quantidadePessoas  = (int) ($dados['quantidadePessoas'] ?? 1);
        $obRoteiro->orcamento          = isset($dados['orcamento']) ? (float) $dados['orcamento'] : null;
        $obRoteiro->etapaAtual         = in_array($dados['etapaAtual'] ?? '', self::ETAPAS, true) ? $dados['etapaAtual'] : self::ETAPAS[0];
        $obRoteiro->gastosExtras       = array_map(fn($gasto) => GastoExtra::fromArray($gasto), $dados['gastosExtras'] ?? []);
        $obRoteiro->anotacoes          = array_map(fn($anotacao) => Anotacao::fromArray($anotacao), $dados['anotacoes'] ?? []);
        $obRoteiro->idDestinos         = array_map('intval', $dados['idDestinos'] ?? []);
        $obRoteiro->itens              = array_map(fn($item) => ItemRoteiro::fromArray($item), $dados['itens'] ?? []);
        $obRoteiro->compartilhamento   = !empty($dados['compartilhamento']) ? Compartilhamento::fromArray($dados['compartilhamento']) : null;
        $obRoteiro->dataCriacao        = $dados['dataCriacao'] ?? null;
        $obRoteiro->dataAtualizacao    = $dados['dataAtualizacao'] ?? null;
        $obRoteiro->calcularValorTotal();

        return $obRoteiro;
    }

    private function toArray(): array
    {
        return [
            'idRoteiro'          => $this->idRoteiro,
            'idUsuario'          => $this->idUsuario,
            'titulo'             => $this->titulo,
            'dataInicio'         => $this->dataInicio,
            'dataFim'            => $this->dataFim,
            'origem'             => $this->origem,
            'quantidadePessoas'  => $this->quantidadePessoas,
            'orcamento'          => $this->orcamento,
            'etapaAtual'         => $this->etapaAtual,
            'idDestinos'         => $this->idDestinos,
            'status'             => $this->status,
            'valorTotalEstimado' => $this->valorTotalEstimado,
            'dataCriacao'        => $this->dataCriacao,
            'dataAtualizacao'    => $this->dataAtualizacao,
            'itens'              => array_map(fn($obItem) => $obItem->toArray(), $this->itens),
            'gastosExtras'       => array_map(fn(GastoExtra $obGasto) => $obGasto->toArray(), $this->gastosExtras),
            'anotacoes'          => array_map(fn(Anotacao $obAnotacao) => $obAnotacao->toArray(), $this->anotacoes),
            'compartilhamento'   => $this->compartilhamento?->toArray()
        ];
    }
}
