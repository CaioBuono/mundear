<?php

namespace Controllers;

use Exceptions\RegraNegocioException;
use Exceptions\DadosInvalidosException;
use Exceptions\OperacaoNaoPermitidaException;
use Exceptions\RegistroNaoEncontradoException;
use Models\Destino;
use Models\GuiaTuristico;
use Models\Hospedagem;
use Models\ItemRoteiro;
use Models\Passeio;
use Models\Roteiro;
use Models\Transporte;
use Models\VeiculoPessoal;
use Services\ApiHospedagemSimulada;
use Session\Session;
use Utils\Formatador;
use Utils\Validador;
use Views\Icones;
use Views\ViewRenderer;

class RoteiroController extends Controller
{
    private static $pathComponentesRoteiro = __DIR__ . '/../Views/roteiro/components/';

    private const ESTILO_TIPO = [
        ItemRoteiro::TIPO_TRANSPORTE => ['bg-blue-50 text-blue-900', 'text-blue-800'],
        ItemRoteiro::TIPO_HOSPEDAGEM => ['bg-orange-50 text-orange-600', 'text-orange-600'],
        ItemRoteiro::TIPO_GUIA       => ['bg-emerald-50 text-emerald-700', 'text-emerald-700'],
        ItemRoteiro::TIPO_PASSEIO    => ['bg-violet-50 text-violet-700', 'text-violet-700'],
    ];

    private const COR_MARCADOR_TIPO = [
        ItemRoteiro::TIPO_TRANSPORTE => 'bg-blue-800',
        ItemRoteiro::TIPO_HOSPEDAGEM => 'bg-orange-500',
        ItemRoteiro::TIPO_GUIA       => 'bg-emerald-600',
        ItemRoteiro::TIPO_PASSEIO    => 'bg-violet-600',
    ];

    private const VALIDADES_LINK =['7' => '7 dias', '30' => '30 dias', '90' => '90 dias', '0' => 'Sem expiração'];

    public static function getLista(): string
    {
        $obUsuario = self::exigirAutenticacao();
        $roteiros  = Roteiro::listarPorUsuario($obUsuario->getIdUsuario());

        $conteudo = empty($roteiros)
            ? self::getEstadoVazio('Nenhum roteiro por aqui ainda', 'Crie seu primeiro roteiro informando o nome da viagem, as datas e o destino.', '<a href="/roteiros/novo" class="btn-destaque mt-5">Criar roteiro</a>')
            : '<div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">' . self::getCardsRoteiros($roteiros) . '</div>';

        return self::renderizarPagina('roteiro/lista.php', [
            'conteudo' => $conteudo,
            'resumo'   => count($roteiros) === 1 ? '1 roteiro' : count($roteiros) . ' roteiros'
        ], 'roteiros', 'Meus roteiros');
    }

    public static function getCardsRoteiros(array $roteiros): string
    {
        $variaveis = array_map(fn(Roteiro $obRoteiro) => [
            'id'              => $obRoteiro->getIdRoteiro(),
            'titulo'          => self::e($obRoteiro->getTitulo()),
            'destinos'        => self::e($obRoteiro->getNomesDestinos()),
            'periodo'         => Formatador::data($obRoteiro->getDataInicio()) . ' – ' . Formatador::data($obRoteiro->getDataFim()),
            'dias'            => $obRoteiro->getQuantidadeDias() . ($obRoteiro->getQuantidadeDias() > 1 ? ' dias' : ' dia'),
            'itens'           => count($obRoteiro->getItens()) . (count($obRoteiro->getItens()) === 1 ? ' serviço' : ' serviços'),
            'total'           => self::moeda($obRoteiro->getValorTotalEstimado()),
            'status'          => self::getBadgeRoteiro($obRoteiro),
            'link'            => $obRoteiro->possuiLinkAtivo() ? '<p class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-emerald-700">' . Icones::get('link', 'h-3.5 w-3.5') . 'Link ativo</p>' : '',
            'iconePin'        => Icones::get('pin', 'h-4 w-4 shrink-0 text-slate-400'),
            'iconeCalendario' => Icones::get('calendario', 'h-4 w-4 shrink-0 text-slate-400')
        ], $roteiros);

        return ViewRenderer::getLista($variaveis, self::$pathComponentesRoteiro, 'card.php');
    }

    private static function getBadgeRoteiro(Roteiro $obRoteiro): string
    {
        $cores = match($obRoteiro->getStatus()){
            Roteiro::STATUS_PRONTO_PARA_VIAJAR => 'bg-[#e6edfa] text-navy-3',
            Roteiro::STATUS_VIAGEM_REALIZADA   => 'bg-ok-suave text-ok',
            default                            => 'bg-laranja-suave text-laranja-escuro',
        };

        return '<span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-xs font-bold ' . $cores . '">' . self::e($obRoteiro->getStatus()) . '</span>';
    }

    public static function getNovo(): string
    {
        self::exigirAutenticacao();

        return self::renderizarFormularioNovo([], []);
    }

    public static function postNovo(): string
    {
        $obUsuario    = self::exigirAutenticacao();
        $vemDoInicio  = ($_POST['origemFormulario'] ?? '') === 'inicio';
        $dados        = $_POST;

        if($vemDoInicio){
            $obDestino         = Destino::buscarPorCidade(explode(' - ', (string) ($dados['destino'] ?? ''))[0]);
            $dados['destinos'] = $obDestino instanceof Destino ? [$obDestino->getIdDestino()] : [];
        }

        $erros = Roteiro::validarDados($dados);
        if(!empty($erros)){
            return $vemDoInicio
                ? UsuarioController::renderizarHome($_POST, $erros)
                : self::renderizarFormularioNovo($_POST, $erros);
        }

        $obRoteiro = Roteiro::criar($obUsuario->getIdUsuario(), $dados);

        Session::setFlash('sucesso', 'Roteiro "' . $obRoteiro->getTitulo() . '" criado! Comece escolhendo o transporte, ou pule esta etapa e complete depois.');
        self::redirecionar('/transporte?roteiro=' . $obRoteiro->getIdRoteiro());
    }

    private static function renderizarFormularioNovo(array $valores, array $erros): string
    {
        return self::renderizarPagina('roteiro/novo.php', [
            'feedback'        => empty($erros) ? '' : self::getAlertaLista('erro', $erros),
            'valorTitulo'     => self::e($valores['titulo'] ?? ''),
            'valorDataInicio' => self::e($valores['dataInicio'] ?? ''),
            'valorDataFim'    => self::e($valores['dataFim'] ?? ''),
            'dataMinima'      => date('Y-m-d'),
            'opcoesDestinos'  => self::getCheckboxesDestinos((array) ($valores['destinos'] ?? []))
        ], 'roteiros', 'Novo roteiro');
    }

    private static function getCheckboxesDestinos(array $idsSelecionados): string
    {
        $idsSelecionados = array_map('intval', $idsSelecionados);

        $variaveis = array_map(fn(Destino $obDestino) => [
            'id'      => $obDestino->getIdDestino(),
            'cidade'  => self::e($obDestino->getCidade()),
            'estado'  => self::e($obDestino->getEstado() . ' · ' . $obDestino->getPais()),
            'marcado' => in_array($obDestino->getIdDestino(), $idsSelecionados, true) ? 'checked' : ''
        ], Destino::listar());

        return ViewRenderer::getLista($variaveis, self::$pathComponentesRoteiro, 'checkbox-destino.php');
    }

    public static function getEditar(): string
    {
        $obUsuario = self::exigirAutenticacao();
        $obRoteiro = self::getRoteiroDoUsuario($obUsuario->getIdUsuario(), $_GET['id'] ?? 0);
        $id        = $obRoteiro->getIdRoteiro();

        return self::renderizarPagina('roteiro/editar.php', [
            'id'                     => $id,
            'titulo'                 => self::e($obRoteiro->getTitulo()),
            'status'                 => self::getBadgeRoteiro($obRoteiro),
            'destinos'               => self::e($obRoteiro->getNomesDestinos()),
            'periodo'                => Formatador::data($obRoteiro->getDataInicio()) . ' a ' . Formatador::data($obRoteiro->getDataFim()),
            'dias'                   => $obRoteiro->getQuantidadeDias() . ($obRoteiro->getQuantidadeDias() > 1 ? ' dias' : ' dia'),
            'quantidadeItens'        => count($obRoteiro->getItens()) . (count($obRoteiro->getItens()) === 1 ? ' serviço' : ' serviços'),
            'total'                  => self::moeda($obRoteiro->getValorTotalEstimado()),
            'totaisPorTipo'          => self::getTotaisPorTipoHtml($obRoteiro),
            'cronograma'             => self::getCronogramaHtml($obRoteiro, true),
            'painelCompartilhamento' => self::getPainelCompartilhamento($obRoteiro),
            'valorTitulo'            => self::e($obRoteiro->getTitulo()),
            'valorDataInicio'        => $obRoteiro->getDataInicio(),
            'valorDataFim'           => $obRoteiro->getDataFim(),
            'opcoesDestinos'         => self::getCheckboxesDestinos($obRoteiro->getIdDestinos()),
            'iconePin'               => Icones::get('pin', 'h-4 w-4 text-slate-400'),
            'iconeCalendario'        => Icones::get('calendario', 'h-4 w-4 text-slate-400'),
            'iconeAviao'             => Icones::get('aviao', 'h-6 w-6'),
            'iconeCama'              => Icones::get('cama', 'h-6 w-6'),
            'iconeGuia'              => Icones::get('guia', 'h-6 w-6'),
            'iconeIngresso'          => Icones::get('ingresso', 'h-6 w-6')
        ], 'roteiros', $obRoteiro->getTitulo());
    }

    public static function postEditar(): void
    {
        $obRoteiro = self::getRoteiroDoPost();

        try{
            $obRoteiro->atualizarDados($_POST);
            $obRoteiro->salvar();
            Session::setFlash('sucesso', 'Dados da viagem atualizados.');
        }catch(RegraNegocioException $e){
            Session::setFlash('erro', $e->getMessage());
        }

        self::redirecionar('/roteiros/editar?id=' . $obRoteiro->getIdRoteiro());
    }

    public static function postExcluir(): void
    {
        $obRoteiro = self::getRoteiroDoPost();
        $obRoteiro->excluir();

        Session::setFlash('sucesso', 'O roteiro "' . $obRoteiro->getTitulo() . '" foi excluído.');
        self::redirecionar('/roteiros');
    }

    public static function postFinalizar(): void
    {
        $obRoteiro = self::getRoteiroDoPost();

        try{
            $obRoteiro->concluir();
            $obRoteiro->salvar();
            Session::setFlash('sucesso', 'Roteiro finalizado! O link somente leitura já pode ser compartilhado.');
        }catch(RegraNegocioException $e){
            Session::setFlash('erro', $e->getMessage());
        }

        self::redirecionar('/roteiros/editar?id=' . $obRoteiro->getIdRoteiro() . '#compartilhar');
    }

    public static function postRevogarLink(): void
    {
        $obRoteiro = self::getRoteiroDoPost();

        try{
            $obRoteiro->revogarCompartilhamento();
            $obRoteiro->salvar();
            Session::setFlash('sucesso', 'Link revogado. Quem tiver o endereço antigo não conseguirá mais ver o roteiro.');
        }catch(RegraNegocioException $e){
            Session::setFlash('erro', $e->getMessage());
        }

        self::redirecionar('/roteiros/editar?id=' . $obRoteiro->getIdRoteiro() . '#compartilhar');
    }

    public static function postAdicionarItem(): void
    {
        $obUsuario = self::exigirAutenticacao();
        $voltar    = self::urlInterna($_POST['voltar'] ?? null, '/roteiros');
        $obRoteiro = Roteiro::getDoUsuario((int) ($_POST['idRoteiro'] ?? 0), $obUsuario->getIdUsuario());

        if(!$obRoteiro instanceof Roteiro){
            Session::setFlash('erro', 'Selecione um dos seus roteiros para adicionar o serviço.');
            self::redirecionar($voltar);
        }

        try{
            $obItem = self::criarItem($_POST);

            if(in_array($obItem->getTipoServico(), [ItemRoteiro::TIPO_TRANSPORTE, ItemRoteiro::TIPO_HOSPEDAGEM], true)){
                self::removerItensDoTipo($obRoteiro, $obItem->getTipoServico());
            }

            $obRoteiro->adicionarServico($obItem);
            $obRoteiro->salvar();

            Session::setFlash(
                'sucesso',
                sprintf('"%s" foi adicionado ao roteiro "%s". Novo total estimado: %s.', $obItem->getDescricao(), $obRoteiro->getTitulo(), self::moeda($obRoteiro->getValorTotalEstimado())),
                '/roteiros/editar?id=' . $obRoteiro->getIdRoteiro(),
                'Ver roteiro'
            );
        }catch(RegraNegocioException $e){
            Session::setFlash('erro', $e->getMessage());
        }

        self::redirecionar($voltar);
    }

    private static function removerItensDoTipo(Roteiro $obRoteiro, string $tipoServico): void
    {
        foreach($obRoteiro->getItens() as $obItem){
            if($obItem->getTipoServico() === $tipoServico){
                $obRoteiro->removerServico($obItem->getIdItem());
            }
        }
    }

    public static function postAdicionarVeiculo(): void
    {
        $obRoteiro = self::getRoteiroDoPost();
        $voltar    = self::urlInterna($_POST['voltar'] ?? null, BuscaController::getUrlEtapa($obRoteiro, 'Transporte'));
        $trechos   = empty($_POST['idaVolta']) ? 1 : 2;
        $destinos  = $obRoteiro->getDestinos();

        try{
            $obVeiculo = new VeiculoPessoal(
                (string) ($_POST['tipoVeiculo'] ?? ''),
                (string) ($_POST['combustivel'] ?? ''),
                (float) ($_POST['distancia'] ?? 0) * $trechos,
                (float) ($_POST['consumo'] ?? 0),
                (float) ($_POST['precoCombustivel'] ?? 0),
                (float) ($_POST['pedagios'] ?? 0) * $trechos,
                $obRoteiro->getOrigem() ?: 'Origem',
                isset($destinos[0]) ? $destinos[0]->getCidade() . ' - ' . $destinos[0]->getEstado() : 'Destino',
                $obRoteiro->getDataInicio() . ' 06:00:00'
            );

            $obItem = ItemRoteiro::criarDeServico($obVeiculo, $obRoteiro->getDataInicio(), $obRoteiro->getQuantidadePessoas(), null, '06:00');

            self::removerItensDoTipo($obRoteiro, ItemRoteiro::TIPO_TRANSPORTE);
            $obRoteiro->adicionarServico($obItem);
            $obRoteiro->salvar();

            Session::setFlash('sucesso', sprintf('Estimativa de %s adicionada ao roteiro: %s.', $obVeiculo->getTipoVeiculo(), self::moeda($obItem->getSubtotal())));
        }catch(RegraNegocioException $e){
            Session::setFlash('erro', $e->getMessage());
        }

        self::redirecionar($voltar);
    }

    public static function postAvancarEtapa(): void
    {
        $obRoteiro    = self::getRoteiroDoPost();
        $etapaDaTela  = (string) ($_POST['etapa'] ?? '');
        $posicao      = array_search($etapaDaTela, Roteiro::ETAPAS, true);

        if($posicao === false or $posicao === count(Roteiro::ETAPAS) - 1){
            self::redirecionar('/roteiros/editar?id=' . $obRoteiro->getIdRoteiro());
        }

        if($obRoteiro->getEtapaAtual() === $etapaDaTela){
            $obRoteiro->avancarEtapa();
            $obRoteiro->salvar();
        }

        self::redirecionar(BuscaController::getUrlEtapa($obRoteiro, Roteiro::ETAPAS[$posicao + 1]));
    }

    public static function postRemoverItem(): void
    {
        $obRoteiro  = self::getRoteiroDoPost();
        $finalizado = $obRoteiro->isFinalizado();

        if($obRoteiro->removerServico((int) ($_POST['idItem'] ?? 0))){
            $obRoteiro->salvar();

            $mensagem = 'Item removido. Novo total estimado: ' . self::moeda($obRoteiro->getValorTotalEstimado()) . '.';
            if($finalizado and !$obRoteiro->isFinalizado()){
                $mensagem .= ' Como o roteiro ficou sem serviços, ele voltou para rascunho e o link de compartilhamento foi desativado.';
            }

            Session::setFlash('sucesso', $mensagem);
        }else{
            Session::setFlash('erro', 'Este item não foi encontrado no roteiro.');
        }

        self::redirecionar('/roteiros/editar?id=' . $obRoteiro->getIdRoteiro());
    }

    public static function postQuantidadeItem(): void
    {
        $obRoteiro = self::getRoteiroDoPost();
        $idItem    = (int) ($_POST['idItem'] ?? 0);

        try{
            if(!Validador::inteiroEntre($_POST['quantidade'] ?? '', 1, ItemRoteiro::QUANTIDADE_MAXIMA_PESSOAS)){
                throw new DadosInvalidosException('Informe uma quantidade de pessoas entre 1 e ' . ItemRoteiro::QUANTIDADE_MAXIMA_PESSOAS . '.');
            }

            $obRoteiro->atualizarQuantidadeItem($idItem, (int) $_POST['quantidade']);
            $obRoteiro->salvar();
            Session::setFlash('sucesso', 'Quantidade atualizada. Novo total estimado: ' . self::moeda($obRoteiro->getValorTotalEstimado()) . '.');
        }catch(RegraNegocioException $e){
            Session::setFlash('erro', $e->getMessage());
        }

        self::redirecionar('/roteiros/editar?id=' . $obRoteiro->getIdRoteiro() . '#item-' . $idItem);
    }

    private static function criarItem(array $dados): ItemRoteiro
    {
        $pessoas = $dados['pessoas'] ?? 1;
        if(!Validador::inteiroEntre($pessoas, 1, ItemRoteiro::QUANTIDADE_MAXIMA_PESSOAS)){
            throw new DadosInvalidosException('Informe uma quantidade de pessoas entre 1 e ' . ItemRoteiro::QUANTIDADE_MAXIMA_PESSOAS . '.');
        }

        $pessoas = (int) $pessoas;

        return match($dados['tipoServico'] ?? ''){
            ItemRoteiro::TIPO_TRANSPORTE => self::criarItemTransporte($dados, $pessoas),
            ItemRoteiro::TIPO_HOSPEDAGEM => self::criarItemHospedagem($dados, $pessoas),
            ItemRoteiro::TIPO_GUIA       => self::criarItemGuia($dados, $pessoas),
            ItemRoteiro::TIPO_PASSEIO    => self::criarItemPasseio($dados, $pessoas),
            default                      => throw new DadosInvalidosException('Tipo de serviço inválido.')
        };
    }

    private static function criarItemTransporte(array $dados, int $pessoas): ItemRoteiro
    {
        $data = $dados['data'] ?? '';
        if(!Validador::data($data)){
            throw new DadosInvalidosException('A data da viagem é inválida. Refaça a busca.');
        }

        $obTransporte = Transporte::buscarPorCodigo($dados['origem'] ?? '', $dados['destino'] ?? '', $data, (string) ($dados['idServico'] ?? ''));
        if(!$obTransporte instanceof Transporte){
            throw new RegistroNaoEncontradoException('Essa opção de transporte não está mais disponível. Refaça a busca.');
        }

        if(!$obTransporte->verificarDisponibilidade($obTransporte->getDataHoraPartida(), $pessoas)){
            throw new OperacaoNaoPermitidaException(sprintf('Não há assentos suficientes para %d pessoa(s) nessa opção, ou a partida já aconteceu.', $pessoas));
        }

        return ItemRoteiro::criarDeServico(
            $obTransporte,
            substr($obTransporte->getDataHoraPartida(), 0, 10),
            $pessoas,
            null,
            Formatador::hora($obTransporte->getDataHoraPartida())
        );
    }

    private static function criarItemHospedagem(array $dados, int $pessoas): ItemRoteiro
    {
        $entrada = $dados['dataInicio'] ?? '';
        $saida   = $dados['dataFim'] ?? '';

        if(!Validador::data($entrada) or !Validador::data($saida) or $saida <= $entrada){
            throw new DadosInvalidosException('Informe datas de entrada e saída válidas (a saída deve ser depois da entrada).');
        }

        $obHospedagem = Hospedagem::getPorId((int) ($dados['idServico'] ?? 0));
        if(!$obHospedagem instanceof Hospedagem){
            throw new RegistroNaoEncontradoException('Hospedagem não encontrada. Refaça a busca.');
        }

        $obOferta = $obHospedagem->obterMenorPreco();
        foreach($obHospedagem->compararPrecos() as $obOfertaSite){
            if($obOfertaSite->getNomeSite() === ($dados['site'] ?? '')){
                $obOferta = $obOfertaSite;
            }
        }

        $quarto = ApiHospedagemSimulada::buscarPorId($obHospedagem->getIdHospedagem())['quarto'] ?? $obHospedagem->getTipo();

        return ItemRoteiro::criarDeServico($obHospedagem, $entrada, $pessoas, $saida, null, $obOferta->getPrecoDiaria(), $quarto . ' · via ' . $obOferta->getNomeSite());
    }

    private static function criarItemGuia(array $dados, int $pessoas): ItemRoteiro
    {
        $data = $dados['data'] ?? '';
        if(!Validador::data($data)){
            throw new DadosInvalidosException('Escolha a data em que deseja contratar o guia.');
        }

        $obGuia = GuiaTuristico::getPorId((int) ($dados['idServico'] ?? 0));
        if(!$obGuia instanceof GuiaTuristico or !$obGuia->isAtivo()){
            throw new OperacaoNaoPermitidaException('Este guia não está disponível para novas reservas.');
        }

        if(!$obGuia->consultarDisponibilidade($data)){
            throw new OperacaoNaoPermitidaException(sprintf('%s não atende em %s (%s). Escolha outro dia.', $obGuia->getNome(), Formatador::data($data), strtolower(Formatador::diaSemana($data))));
        }

        return ItemRoteiro::criarDeServico($obGuia, $data, $pessoas);
    }

    private static function criarItemPasseio(array $dados, int $pessoas): ItemRoteiro
    {
        $data = $dados['data'] ?? '';
        if(!Validador::data($data)){
            throw new DadosInvalidosException('Escolha a data do passeio.');
        }

        $obPasseio = Passeio::getPorId((int) ($dados['idServico'] ?? 0));
        if(!$obPasseio instanceof Passeio){
            throw new RegistroNaoEncontradoException('Passeio não encontrado. Refaça a busca.');
        }

        if(!$obPasseio->verificarDisponibilidade($data)){
            throw new OperacaoNaoPermitidaException(sprintf('%s fica fechado(a) em %s (%s). Escolha outro dia.', $obPasseio->getNomeAtracao(), Formatador::data($data), strtolower(Formatador::diaSemana($data))));
        }

        return ItemRoteiro::criarDeServico($obPasseio, $data, $pessoas);
    }

    public static function getCronogramaHtml(Roteiro $obRoteiro, bool $editavel): string
    {
        $itensPorData = $obRoteiro->getItensPorData();
        $hospedagens  = array_filter($obRoteiro->getItens(), fn(ItemRoteiro $obItem) => $obItem->getTipoServico() === ItemRoteiro::TIPO_HOSPEDAGEM);
        $dias         = [];

        foreach($obRoteiro->getDatasDaViagem() as $indice => $data){
            $itensHtml = '';
            foreach($itensPorData[$data] ?? [] as $obItem){
                $itensHtml .= self::getItemHtml($obRoteiro, $obItem, $editavel);
            }

            foreach($hospedagens as $obHospedagem){
                if($data > $obHospedagem->getDataAgendada() and $data < $obHospedagem->getDataFim()){
                    $itensHtml .= '<p class="flex items-center gap-2 px-5 py-3 text-sm text-slate-500">' . Icones::get('cama', 'h-4 w-4 text-orange-500') . 'Hospedado(a) em ' . self::e($obHospedagem->getDescricao()) . '</p>';
                }
                if($data === $obHospedagem->getDataFim()){
                    $itensHtml .= '<p class="flex items-center gap-2 px-5 py-3 text-sm text-slate-500">' . Icones::get('cama', 'h-4 w-4 text-orange-500') . 'Check-out: ' . self::e($obHospedagem->getDescricao()) . '</p>';
                }
            }

            if($itensHtml === ''){
                $itensHtml = '<p class="px-5 py-4 text-sm text-slate-400">Dia livre — nenhum serviço agendado.</p>';
            }

            $dias[] = [
                'data'          => $data,
                'numero'        => $indice + 1,
                'dataFormatada' => Formatador::data($data),
                'diaSemana'     => Formatador::diaSemana($data),
                'itens'         => $itensHtml,
                'acaoDia'       => $editavel
                    ? '<a href="/turismo?roteiro=' . $obRoteiro->getIdRoteiro() . '&data=' . $data . '" class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-50">' . Icones::get('mais', 'h-3.5 w-3.5') . '<span class="hidden sm:inline">Guia ou passeio</span></a>'
                    : ''
            ];
        }

        return ViewRenderer::getLista($dias, self::$pathComponentesRoteiro, 'dia.php');
    }

    private static function getItemHtml(Roteiro $obRoteiro, ItemRoteiro $obItem, bool $editavel): string
    {
        [$corIcone, $corTipo] = self::ESTILO_TIPO[$obItem->getTipoServico()];

        $icone = match($obItem->getTipoServico()){
            ItemRoteiro::TIPO_TRANSPORTE => str_starts_with($obItem->getDescricao(), 'Ônibus') ? 'onibus' : 'aviao',
            ItemRoteiro::TIPO_HOSPEDAGEM => 'cama',
            ItemRoteiro::TIPO_GUIA       => 'guia',
            default                      => 'ingresso'
        };

        $complemento = match($obItem->getTipoServico()){
            ItemRoteiro::TIPO_TRANSPORTE => ' · ' . $obItem->getHorario(),
            ItemRoteiro::TIPO_HOSPEDAGEM => ' · check-in ' . Formatador::dataCurta($obItem->getDataAgendada()) . ' → check-out ' . Formatador::dataCurta($obItem->getDataFim()),
            default                      => ''
        };

        $variaveisComuns = ['idRoteiro' => $obRoteiro->getIdRoteiro(), 'idItem' => $obItem->getIdItem()];

        $controleQuantidade = ($editavel and $obItem->isCobradoPorPessoa())
            ? ViewRenderer::getComponente($variaveisComuns + [
                'quantidade'    => $obItem->getQuantidadePessoas(),
                'maximo'        => ItemRoteiro::QUANTIDADE_MAXIMA_PESSOAS,
                'valorUnitario' => number_format($obItem->getValorUnitario(), 2, '.', '')
            ], self::$pathComponentesRoteiro, 'controle-quantidade.php')
            : '';

        $botaoRemover = $editavel
            ? ViewRenderer::getComponente($variaveisComuns + ['iconeLixeira' => Icones::get('lixeira')], self::$pathComponentesRoteiro, 'botao-remover.php')
            : '';

        return ViewRenderer::getComponente([
            'idItem'             => $obItem->getIdItem(),
            'icone'              => Icones::get($icone),
            'corIcone'           => $corIcone,
            'corTipo'            => $corTipo,
            'tipo'               => self::e($obItem->getRotuloTipo() . $complemento),
            'descricao'          => self::e($obItem->getDescricao()),
            'detalhes'           => self::e($obItem->getDetalhes()),
            'quantidade'         => self::e($obItem->getDescricaoQuantidade()),
            'subtotal'           => self::moeda($obItem->getSubtotal()),
            'controleQuantidade' => $controleQuantidade,
            'botaoRemover'       => $botaoRemover
        ], self::$pathComponentesRoteiro, 'item.php');
    }

    public static function getTotaisPorTipoHtml(Roteiro $obRoteiro): string
    {
        $rotulos = ItemRoteiro::getRotulosTipo();
        $html    = '';

        foreach($obRoteiro->getTotaisPorTipo() as $tipo => $valor){
            $classeValor = $valor > 0 ? 'font-semibold text-blue-950' : 'text-slate-400';
            $html .= sprintf(
                '<div class="flex items-center justify-between py-2"><dt class="flex items-center gap-2 text-slate-600"><span class="h-2.5 w-2.5 rounded-full %s"></span>%s</dt><dd class="%s">%s</dd></div>',
                self::COR_MARCADOR_TIPO[$tipo],
                self::e($rotulos[$tipo]),
                $classeValor,
                self::moeda($valor)
            );
        }

        return $html;
    }

    private static function getPainelCompartilhamento(Roteiro $obRoteiro): string
    {
        $variaveis = ['id' => $obRoteiro->getIdRoteiro()];

        if(empty($obRoteiro->getItens())){
            return ViewRenderer::getComponente($variaveis, self::$pathComponentesRoteiro, 'painel-sem-itens.php');
        }

        if($obRoteiro->possuiLinkAtivo()){
            $obCompartilhamento = $obRoteiro->getCompartilhamento();

            return ViewRenderer::getComponente($variaveis + [
                'urlCompleta'  => self::e(self::urlAbsoluta($obCompartilhamento->gerarLinkAcesso())),
                'urlRelativa'  => self::e($obCompartilhamento->gerarLinkAcesso()),
                'expiracao'    => $obCompartilhamento->getDataExpiracao()
                    ? 'Válido até ' . Formatador::data($obCompartilhamento->getDataExpiracao())
                    : 'Sem data de expiração',
                'iconeCopiar'  => Icones::get('copiar', 'h-4 w-4'),
                'iconeOlho'    => Icones::get('olho', 'h-4 w-4')
            ], self::$pathComponentesRoteiro, 'painel-link.php');
        }

        $obCompartilhamento = $obRoteiro->getCompartilhamento();
        $aviso = '';
        if($obCompartilhamento){
            $aviso = '<p class="mt-3 rounded-xl bg-slate-100 px-3 py-2 text-xs text-slate-600">O link anterior ' . ($obCompartilhamento->isExpirado() ? 'expirou' : 'foi revogado') . ' e não funciona mais. Gere um novo link se quiser compartilhar de novo.</p>';
        }

        return ViewRenderer::getComponente($variaveis + [
            'textoBotao'     => $obRoteiro->isFinalizado() ? 'Gerar novo link' : 'Finalizar e gerar link',
            'opcoesValidade' => self::opcoesSelect(self::VALIDADES_LINK, '30'),
            'aviso'          => $aviso
        ], self::$pathComponentesRoteiro, 'painel-finalizar.php');
    }

    private static function urlAbsoluta(string $caminho): string
    {
        $protocolo = (!empty($_SERVER['HTTPS']) and $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host      = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';

        return $protocolo . '://' . $host . $caminho;
    }

    private static function getRoteiroDoUsuario(int $idUsuario, mixed $idRoteiro): Roteiro
    {
        $obRoteiro = Roteiro::getDoUsuario((int) $idRoteiro, $idUsuario);

        if(!$obRoteiro instanceof Roteiro){
            echo ErroController::exibir(404, 'Roteiro não encontrado', 'Este roteiro não existe ou pertence a outra pessoa.');
            exit();
        }

        return $obRoteiro;
    }

    private static function getRoteiroDoPost(): Roteiro
    {
        $obUsuario = self::exigirAutenticacao();

        return self::getRoteiroDoUsuario($obUsuario->getIdUsuario(), $_POST['idRoteiro'] ?? 0);
    }
}
