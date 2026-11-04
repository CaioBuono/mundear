<?php

namespace Models;

use BD\TabelaJson;
use Exceptions\RegraNegocioException;
use Utils\Formatador;
use Utils\Texto;
use Utils\Validador;

/**
 * O "carrinho" da viagem.
 *
 * Modelagem NoSQL (documento em src/BD/table_roteiro.json):
 *  - composição  (ItemRoteiro, Compartilhamento) => documentos embutidos no roteiro;
 *  - agregação   (Destino)                       => apenas os ids (idDestinos);
 *  - associação  (Usuario)                       => idUsuario.
 */
class Roteiro
{
    const STATUS_RASCUNHO   = 'rascunho';
    const STATUS_FINALIZADO = 'finalizado';

    const DURACAO_MAXIMA_DIAS = 60;

    private ?int $idRoteiro = null;

    private int $idUsuario;

    private string $titulo = '';

    private string $dataInicio = '';

    private string $dataFim = '';

    private float $valorTotalEstimado = 0;

    private string $status = self::STATUS_RASCUNHO;

    /** @var int[] */
    private array $idDestinos = [];

    /** @var ItemRoteiro[] */
    private array $itens = [];

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
    public function getStatus(): string { return $this->status; }
    public function getIdDestinos(): array { return $this->idDestinos; }
    public function getCompartilhamento(): ?Compartilhamento { return $this->compartilhamento; }
    public function getDataAtualizacao(): ?string { return $this->dataAtualizacao; }

    /** @return ItemRoteiro[] */
    public function getItens(): array { return $this->itens; }

    public function isFinalizado(): bool
    {
        return $this->status === self::STATUS_FINALIZADO;
    }

    public function possuiLinkAtivo(): bool
    {
        return $this->compartilhamento !== null && $this->compartilhamento->isValido();
    }

    // ------------------------------------------------------------------
    // Criação e edição dos dados iniciais (RF05 / RF06)
    // ------------------------------------------------------------------

    /**
     * RN05: valida nome, datas e destinos. Retorna as mensagens de erro (vazio = válido).
     */
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

        $idDestinos = array_unique(array_map('intval', (array) ($dados['destinos'] ?? [])));
        if(empty($idDestinos)){
            $erros[] = 'Selecione pelo menos um destino.';
        }elseif(count(Destino::getPorIds($idDestinos)) !== count($idDestinos)){
            $erros[] = 'Um dos destinos selecionados é inválido.';
        }

        return $erros;
    }

    public static function criar(int $idUsuario, array $dados): Roteiro
    {
        $erros = self::validarDados($dados);
        if(!empty($erros)){
            throw new RegraNegocioException(implode(' ', $erros));
        }

        $obRoteiro              = new Roteiro();
        $obRoteiro->idUsuario   = $idUsuario;
        $obRoteiro->dataCriacao = date('Y-m-d H:i:s');
        $obRoteiro->preencherDados($dados);
        $obRoteiro->salvar();

        return $obRoteiro;
    }

    /**
     * Altera nome, período e destinos garantindo que os itens já adicionados
     * continuem válidos (RN03 e RN04).
     */
    public function atualizarDados(array $dados): void
    {
        $erros = self::validarDados($dados, $this);
        if(!empty($erros)){
            throw new RegraNegocioException(implode(' ', $erros));
        }

        $obSimulacao = clone $this;
        $obSimulacao->preencherDados($dados);

        foreach($this->itens as $obItem){
            $obSimulacao->validarPeriodoItem($obItem);

            if($obItem->getTipoServico() === ItemRoteiro::TIPO_GUIA){
                $obGuia = GuiaTuristico::getPorId((int) $obItem->getIdServico());
                if($obGuia and !$obSimulacao->atendeCidade($obGuia->getCidadeAtuacao())){
                    throw new RegraNegocioException(sprintf(
                        'O guia %s atua em %s. Remova-o do roteiro antes de retirar esse destino.',
                        $obGuia->getNome(),
                        $obGuia->getCidadeAtuacao()
                    ));
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
    }

    // ------------------------------------------------------------------
    // Carrinho de serviços (RF06, RF07, RN03, RN04, RN07)
    // ------------------------------------------------------------------

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

        // RN02: sem serviços o roteiro volta a ser rascunho e o link deixa de valer
        if(empty($this->itens) and $this->isFinalizado()){
            $this->status = self::STATUS_RASCUNHO;
            $this->compartilhamento?->revogarAcesso();
        }

        return true;
    }

    public function atualizarQuantidadeItem(int $idItem, int $quantidadePessoas): void
    {
        $obItem = $this->getItem($idItem);
        if(!$obItem instanceof ItemRoteiro){
            throw new RegraNegocioException('O item informado não pertence a este roteiro.');
        }

        $obItem->atualizarQuantidade($quantidadePessoas);
        $this->calcularValorTotal();
    }

    /**
     * RF07 / RN07: soma dos subtotais, recalculada a cada inclusão/remoção.
     */
    public function calcularValorTotal(): float
    {
        $this->valorTotalEstimado = round(array_sum(array_map(fn($obItem) => $obItem->calcularSubtotal(), $this->itens)), 2);

        return $this->valorTotalEstimado;
    }

    /**
     * RN03: o serviço precisa estar dentro do período da viagem.
     */
    private function validarPeriodoItem(ItemRoteiro $item): void
    {
        $inicio = $item->getDataAgendada();
        $fim    = $item->getDataFim() ?? $inicio;

        if($inicio < $this->dataInicio or $fim > $this->dataFim){
            $periodoItem = $fim !== $inicio ? Formatador::data($inicio) . ' a ' . Formatador::data($fim) : Formatador::data($inicio);

            throw new RegraNegocioException(sprintf(
                '"%s" (%s) está fora do período da viagem, de %s a %s.',
                $item->getDescricao(),
                $periodoItem,
                Formatador::data($this->dataInicio),
                Formatador::data($this->dataFim)
            ));
        }
    }

    /**
     * RN04: o guia precisa atuar em um dos destinos e não pode ser reservado
     * duas vezes no mesmo dia do roteiro.
     */
    private function validarGuia(?GuiaTuristico $obGuia, string $data): void
    {
        if(!$obGuia instanceof GuiaTuristico or !$obGuia->isAtivo()){
            throw new RegraNegocioException('Este guia não está disponível para novas reservas.');
        }

        if(!$this->atendeCidade($obGuia->getCidadeAtuacao())){
            throw new RegraNegocioException(sprintf(
                '%s atua em %s, que não é destino deste roteiro (%s).',
                $obGuia->getNome(),
                $obGuia->getCidadeAtuacao(),
                $this->getNomesDestinos()
            ));
        }

        foreach($this->itens as $obItem){
            if(
                $obItem->getTipoServico() === ItemRoteiro::TIPO_GUIA and
                (int) $obItem->getIdServico() === $obGuia->getIdGuia() and
                $obItem->getDataAgendada() === $data
            ){
                throw new RegraNegocioException($obGuia->getNome() . ' já está reservado(a) para ' . Formatador::data($data) . ' neste roteiro.');
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

    // ------------------------------------------------------------------
    // Finalização e compartilhamento (RF09, RN02, RN06)
    // ------------------------------------------------------------------

    public function finalizar(?string $dataExpiracao): Compartilhamento
    {
        $obCompartilhamento = $this->gerarLinkCompartilhamento($dataExpiracao);
        $this->status       = self::STATUS_FINALIZADO;

        return $obCompartilhamento;
    }

    /**
     * Gera o link somente leitura. Se já existir um link válido, ele é mantido
     * para não quebrar endereços que já foram enviados a outras pessoas.
     */
    public function gerarLinkCompartilhamento(?string $dataExpiracao): Compartilhamento
    {
        if(empty($this->itens)){
            throw new RegraNegocioException('Adicione pelo menos um serviço (transporte, hospedagem, guia ou passeio) antes de finalizar o roteiro e gerar o link.');
        }

        if($this->possuiLinkAtivo()){
            return $this->compartilhamento;
        }

        $this->compartilhamento = Compartilhamento::criar(self::gerarIdCompartilhamento(), $dataExpiracao);

        return $this->compartilhamento;
    }

    public function revogarCompartilhamento(): void
    {
        if(!$this->compartilhamento or !$this->compartilhamento->isStatusAtivo()){
            throw new RegraNegocioException('Este roteiro não possui um link ativo.');
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

    // ------------------------------------------------------------------
    // Persistência
    // ------------------------------------------------------------------

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

    /**
     * RF08: por composição, itens e compartilhamento são apagados junto.
     */
    public function excluir(): bool
    {
        return $this->idRoteiro !== null && self::getTabela()->excluir($this->idRoteiro);
    }

    // ------------------------------------------------------------------
    // Consultas
    // ------------------------------------------------------------------

    public static function getPorId(int $idRoteiro): ?Roteiro
    {
        $registro = self::getTabela()->buscarPorId($idRoteiro);
        return $registro ? self::fromArray($registro) : null;
    }

    /**
     * Garante que o roteiro pertence ao usuário (o usuário só altera os próprios roteiros).
     */
    public static function getDoUsuario(int $idRoteiro, int $idUsuario): ?Roteiro
    {
        $obRoteiro = self::getPorId($idRoteiro);
        return ($obRoteiro and $obRoteiro->idUsuario === $idUsuario) ? $obRoteiro : null;
    }

    /**
     * @return Roteiro[] ordenados pela data de início
     */
    public static function listarPorUsuario(int $idUsuario): array
    {
        $registros = self::getTabela()->listar(fn($roteiro) => (int) $roteiro['idUsuario'] === $idUsuario);
        usort($registros, fn($a, $b) => strcmp($a['dataInicio'], $b['dataInicio']));

        return array_map(fn($registro) => self::fromArray($registro), $registros);
    }

    /**
     * @return Roteiro[]
     */
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

    /** @return Destino[] */
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

    /** @return string[] datas Y-m-d de todos os dias da viagem */
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

    /**
     * @return array<string, ItemRoteiro[]> itens agrupados pela data agendada
     */
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

    /**
     * @return array<string, float> subtotal por tipo de serviço
     */
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
        $obRoteiro->status             = $dados['status'] ?? self::STATUS_RASCUNHO;
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
            'idDestinos'         => $this->idDestinos,
            'status'             => $this->status,
            'valorTotalEstimado' => $this->valorTotalEstimado,
            'dataCriacao'        => $this->dataCriacao,
            'dataAtualizacao'    => $this->dataAtualizacao,
            'itens'              => array_map(fn($obItem) => $obItem->toArray(), $this->itens),
            'compartilhamento'   => $this->compartilhamento?->toArray()
        ];
    }
}
