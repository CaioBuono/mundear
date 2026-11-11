<?php

namespace Controllers;

use Exceptions\RegraNegocioException;
use Exceptions\DadosInvalidosException;
use Exceptions\OperacaoNaoPermitidaException;
use Exceptions\RegistroNaoEncontradoException;
use Models\Anotacao;
use Models\Destino;
use Models\GastoExtra;
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

    private const TIPO_GASTO_EXTRA = 'extra';

    private const COR_ICONE_TIPO = [
        ItemRoteiro::TIPO_TRANSPORTE => 'cor-transporte',
        ItemRoteiro::TIPO_HOSPEDAGEM => 'cor-hospedagem',
        ItemRoteiro::TIPO_GUIA       => 'cor-guia',
        ItemRoteiro::TIPO_PASSEIO    => 'cor-passeio',
        self::TIPO_GASTO_EXTRA       => 'cor-extra'
    ];

    private const COR_MARCADOR_TIPO = [
        ItemRoteiro::TIPO_TRANSPORTE => 'bg-navy-3',
        ItemRoteiro::TIPO_HOSPEDAGEM => 'bg-laranja',
        ItemRoteiro::TIPO_GUIA       => 'bg-ok',
        ItemRoteiro::TIPO_PASSEIO    => 'bg-[#7c4ddb]',
        self::TIPO_GASTO_EXTRA       => 'bg-[#d6336c]'
    ];

    private const FILTROS_STATUS = [
        'todos'       => ['rotulo' => 'Todos', 'status' => null],
        'planejamento' => ['rotulo' => 'Em planejamento', 'status' => Roteiro::STATUS_EM_PLANEJAMENTO],
        'prontos'     => ['rotulo' => 'Prontos para viajar', 'status' => Roteiro::STATUS_PRONTO_PARA_VIAJAR],
        'realizadas'  => ['rotulo' => 'Viagens realizadas', 'status' => Roteiro::STATUS_VIAGEM_REALIZADA]
    ];

    public static function getLista(): string
    {
        $obUsuario = self::exigirAutenticacao();
        $roteiros  = Roteiro::listarPorUsuario($obUsuario->getIdUsuario());
        $filtro    = self::parametroPermitido('status', self::FILTROS_STATUS, 'todos');
        $status    = self::FILTROS_STATUS[$filtro]['status'];
        $exibidos  = array_values(array_filter($roteiros, fn(Roteiro $obRoteiro) => $status === null or $obRoteiro->getStatus() === $status));

        $filtros = '';
        foreach(self::FILTROS_STATUS as $chave => $dados){
            $quantidade = count(array_filter($roteiros, fn(Roteiro $obRoteiro) => $dados['status'] === null or $obRoteiro->getStatus() === $dados['status']));
            $filtros   .= sprintf(
                '<a href="/roteiros%s" aria-pressed="%s" class="rounded-full border border-linha bg-white px-3.5 py-[7px] text-[13.5px] font-bold text-navy transition hover:border-navy aria-pressed:border-navy aria-pressed:bg-navy aria-pressed:text-white">%s · %d</a>',
                $chave === 'todos' ? '' : '?status=' . $chave,
                $chave === $filtro ? 'true' : 'false',
                self::e($dados['rotulo']),
                $quantidade
            );
        }

        $conteudo = match(true){
            empty($roteiros) => '<div class="cartao col-span-full px-10 py-10 text-center text-apagado">Você ainda não tem roteiros. Crie o primeiro com o botão "Adicionar novo roteiro".</div>',
            empty($exibidos) => '<div class="cartao col-span-full px-10 py-10 text-center text-apagado">Nenhum roteiro nesta situação.</div>',
            default          => self::getCardsRoteiros($exibidos)
        };

        $quantidadeTotal = count($roteiros);
        $cabecalho = sprintf(
            '<div class="border-b border-linha bg-white"><div class="container-app flex flex-wrap items-center justify-between gap-x-8 gap-y-5 pb-6 pt-[22px]"><div class="flex min-w-0 items-center gap-4"><span class="grid h-14 w-14 flex-none place-items-center rounded-[14px] bg-navy text-white shadow-[0_8px_18px_-10px_var(--color-navy)]">%s</span><div class="min-w-0"><h1 class="text-[28px] font-extrabold leading-tight tracking-[-.01em]">Meus roteiros</h1><p class="mt-0.5 text-[14.5px] text-apagado">%s · organize, edite e compartilhe seus planejamentos.</p></div></div><button type="button" class="btn-destaque btn-lg" data-abrir-janela="janela-novo-roteiro">%sAdicionar novo roteiro</button></div></div>',
            Icones::get('lista', 'h-7 w-7'),
            $quantidadeTotal . ($quantidadeTotal === 1 ? ' roteiro' : ' roteiros'),
            Icones::get('mais', 'h-[18px] w-[18px]')
        );

        return self::renderizarPagina('roteiro/lista.php', [
            'filtros'     => $filtros,
            'conteudo'    => $conteudo,
            'janelaNovo'  => self::getJanelaNovoRoteiro()
        ], 'roteiros', 'Meus roteiros', ['cabecalhoPagina' => $cabecalho]);
    }

    public static function getCardsRoteiros(array $roteiros): string
    {
        $variaveis = array_map(function(Roteiro $obRoteiro){
            $quantidade = count($obRoteiro->getItens()) + count($obRoteiro->getGastosExtras());

            return self::getResumoViagem($obRoteiro) + [
                'id'           => $obRoteiro->getIdRoteiro(),
                'titulo'       => self::e($obRoteiro->getTitulo()),
                'destinos'     => self::e(self::getDestinosComEstado($obRoteiro)),
                'itens'        => $quantidade . ($quantidade === 1 ? ' serviço' : ' serviços'),
                'total'        => self::moeda($obRoteiro->getValorTotalEstimado()),
                'status'       => self::getBadgeRoteiro($obRoteiro),
                'link'         => $obRoteiro->possuiLinkAtivo() ? '<span class="inline-flex items-center gap-1 text-xs font-bold text-ok">' . Icones::get('link', 'h-3.5 w-3.5') . 'Link ativo</span>' : ''
            ];
        }, $roteiros);

        return ViewRenderer::getLista($variaveis, self::$pathComponentesRoteiro, 'card.php');
    }

    private static function getBadgeRoteiro(Roteiro $obRoteiro): string
    {
        $cores = match($obRoteiro->getStatus()){
            Roteiro::STATUS_PRONTO_PARA_VIAJAR => 'bg-[#e6edfa] text-navy-3',
            Roteiro::STATUS_VIAGEM_REALIZADA   => 'bg-ok-suave text-ok',
            default                            => 'bg-laranja-suave text-laranja-escuro',
        };

        return '<span class="inline-flex flex-none items-center whitespace-nowrap rounded-full px-[9px] py-[3px] text-[11.5px] font-bold ' . $cores . '">' . self::e($obRoteiro->getStatus()) . '</span>';
    }

    public static function postNovo(): string
    {
        $obUsuario   = self::exigirAutenticacao();
        $vemDoInicio = ($_POST['origemFormulario'] ?? '') === 'inicio';
        $dados       = self::converterDestinoDigitado($_POST);

        $erros = Roteiro::validarDados($dados);
        if(!empty($erros) and $vemDoInicio){
            return UsuarioController::renderizarHome($_POST, $erros);
        }

        if(!empty($erros)){
            Session::setDadosFormulario('novoRoteiro', ['valores' => $_POST, 'erros' => $erros]);
            self::redirecionar(self::urlInterna($_POST['voltar'] ?? null, '/roteiros'));
        }

        $obRoteiro = Roteiro::criar($obUsuario->getIdUsuario(), $dados);

        Session::setFlash('sucesso', 'Roteiro "' . $obRoteiro->getTitulo() . '" criado! Comece escolhendo o transporte, ou pule esta etapa e complete depois.');
        self::redirecionar('/transporte?roteiro=' . $obRoteiro->getIdRoteiro());
    }

    public static function getJanelaNovoRoteiro(): string
    {
        $formulario = Session::getDadosFormulario('novoRoteiro');
        $valores    = $formulario['valores'] ?? [];
        $erros      = $formulario['erros'] ?? [];

        return ViewRenderer::getComponente([
            'voltar'           => self::e($_SERVER['REQUEST_URI'] ?? '/roteiros'),
            'abrirAoCarregar'  => empty($erros) ? '' : 'data-abrir-ao-carregar',
            'erros'            => empty($erros) ? '' : '<div class="erro-campos">' . Icones::get('alerta', 'h-[18px] w-[18px] flex-none') . '<span>' . self::e(implode(' ', $erros)) . '</span></div>',
            'valorTitulo'      => self::e($valores['titulo'] ?? ''),
            'valorOrigem'      => self::e($valores['origem'] ?? ''),
            'valorDestino'     => self::e($valores['destino'] ?? ''),
            'valorDataInicio'  => self::e($valores['dataInicio'] ?? ''),
            'valorDataFim'     => self::e($valores['dataFim'] ?? ''),
            'valorPessoas'     => self::e($valores['quantidadePessoas'] ?? '2'),
            'valorOrcamento'   => self::e($valores['orcamento'] ?? ''),
            'dataMinima'       => date('Y-m-d'),
            'datalistDestinos' => self::getDatalistDestinos(),
            'iconeFechar'      => Icones::get('fechar', 'h-5 w-5'),
            'iconeSeta'        => Icones::get('seta', 'h-[18px] w-[18px]')
        ], self::$pathComponentesRoteiro, 'janela-novo-roteiro.php');
    }

    private static function converterDestinoDigitado(array $dados): array
    {
        $obDestino         = Destino::buscarPorCidade(explode(' - ', (string) ($dados['destino'] ?? ''))[0]);
        $dados['destinos'] = $obDestino instanceof Destino ? [$obDestino->getIdDestino()] : [];

        return $dados;
    }

    private static function getDestinosComEstado(Roteiro $obRoteiro): string
    {
        return implode(', ', array_map(fn(Destino $obDestino) => $obDestino->getCidade() . ' - ' . $obDestino->getEstado(), $obRoteiro->getDestinos()));
    }

    public static function getResumoViagem(Roteiro $obRoteiro): array
    {
        $destinos = self::getDestinosComEstado($obRoteiro);
        $pessoas  = $obRoteiro->getQuantidadePessoas();
        $dias     = $obRoteiro->getQuantidadeDias();

        return [
            'trajeto'         => self::e($obRoteiro->getOrigem() !== '' ? $obRoteiro->getOrigem() . ' → ' . $destinos : $destinos),
            'periodo'         => Formatador::data($obRoteiro->getDataInicio()) . ' – ' . Formatador::data($obRoteiro->getDataFim()),
            'dias'            => $dias . ($dias > 1 ? ' dias' : ' dia'),
            'pessoas'         => $pessoas . ($pessoas === 1 ? ' pessoa' : ' pessoas'),
            'iconePin'        => Icones::get('pin', 'h-4 w-4'),
            'iconeCalendario' => Icones::get('calendario', 'h-4 w-4'),
            'iconePessoas'    => Icones::get('pessoas', 'h-4 w-4')
        ];
    }

    public static function getEditar(): string
    {
        $obUsuario  = self::exigirAutenticacao();
        $obRoteiro  = self::getRoteiroDoUsuario($obUsuario->getIdUsuario(), $_GET['id'] ?? 0);
        $id         = $obRoteiro->getIdRoteiro();
        $destinos   = self::getDestinosComEstado($obRoteiro);
        $pessoas    = $obRoteiro->getQuantidadePessoas();
        $quantidade = count($obRoteiro->getItens()) + count($obRoteiro->getGastosExtras());

        return self::renderizarPagina('roteiro/editar.php', self::getResumoViagem($obRoteiro) + [
            'id'                     => $id,
            'titulo'                 => self::e($obRoteiro->getTitulo()),
            'status'                 => self::getBadgeRoteiro($obRoteiro),
            'quantidadeItens'        => $quantidade . ($quantidade === 1 ? ' serviço no roteiro' : ' serviços no roteiro'),
            'urlTransporte'          => '/transporte?roteiro=' . $id,
            'urlHospedagem'          => '/hospedagem?roteiro=' . $id,
            'urlGuias'               => '/turismo?roteiro=' . $id . '&modo=guias',
            'urlPasseios'            => '/turismo?roteiro=' . $id . '&modo=passeios',
            'cronograma'             => self::getCronogramaHtml($obRoteiro, true),
            'valorTitulo'            => self::e($obRoteiro->getTitulo()),
            'valorOrigem'            => self::e($obRoteiro->getOrigem()),
            'valorDestino'           => self::e(explode(', ', $destinos)[0]),
            'datalistDestinos'       => self::getDatalistDestinos(),
            'valorDataInicio'        => $obRoteiro->getDataInicio(),
            'valorDataFim'           => $obRoteiro->getDataFim(),
            'valorPessoas'           => $pessoas,
            'valorOrcamento'         => $obRoteiro->getOrcamento() === null ? '' : number_format($obRoteiro->getOrcamento(), 2, '.', ''),
            'total'                  => self::moeda($obRoteiro->calcularValorTotal()),
            'porPessoa'              => self::moeda($obRoteiro->calcularValorPorPessoa()),
            'totaisPorTipo'          => self::getTotaisPorTipoHtml($obRoteiro),
            'orcamento'              => self::getOrcamentoHtml($obRoteiro),
            'painelCompartilhamento' => self::getPainelCompartilhamento($obRoteiro),
            'janelaNovo'             => self::getJanelaNovoRoteiro(),
            'iconeMais'              => Icones::get('mais', 'h-[18px] w-[18px]'),
            'janelaGasto'            => ViewRenderer::getComponente([
                'id'               => $id,
                'opcoesDias'       => self::getOpcoesDias($obRoteiro),
                'opcoesCategorias' => self::opcoesSelect(array_combine(GastoExtra::CATEGORIAS, GastoExtra::CATEGORIAS), 'Refeição'),
                'iconeFechar'      => Icones::get('fechar', 'h-5 w-5'),
                'iconeMais'        => Icones::get('mais', 'h-[18px] w-[18px]')
            ], self::$pathComponentesRoteiro, 'janela-gasto.php'),
            'iconeAviao'             => Icones::get('aviao', 'h-[22px] w-[22px]'),
            'iconeCama'              => Icones::get('cama', 'h-[22px] w-[22px]'),
            'iconeGuia'              => Icones::get('pessoas', 'h-[22px] w-[22px]'),
            'iconeIngresso'          => Icones::get('ingresso', 'h-[22px] w-[22px]'),
            'iconeCarteira'          => Icones::get('carteira', 'h-[22px] w-[22px]'),
            'iconeBaixo'             => Icones::get('baixo', 'h-5 w-5 text-apagado transition group-open:rotate-180')
        ], 'roteiros', $obRoteiro->getTitulo());
    }

    private static function getOpcoesDias(Roteiro $obRoteiro): string
    {
        $opcoes = [];
        foreach($obRoteiro->getDatasDaViagem() as $indice => $data){
            $opcoes[$data] = 'Dia ' . ($indice + 1) . ' · ' . Formatador::dataCurta($data);
        }

        return self::opcoesSelect($opcoes, $obRoteiro->getDataInicio());
    }

    public static function postEditar(): void
    {
        $obRoteiro = self::getRoteiroDoPost();

        try{
            $obRoteiro->atualizarDados(self::converterDestinoDigitado($_POST));
            $obRoteiro->salvar();
            Session::setFlash('sucesso', 'Dados da viagem atualizados.');
        }catch(RegraNegocioException $e){
            Session::setFlash('erro', $e->getMessage());
        }

        self::redirecionar('/roteiros/editar?id=' . $obRoteiro->getIdRoteiro());
    }

    public static function postAdicionarGasto(): void
    {
        $obRoteiro = self::getRoteiroDoPost();
        $descricao = trim((string) ($_POST['descricao'] ?? ''));

        try{
            $obRoteiro->adicionarGastoExtra(
                $descricao,
                (string) ($_POST['categoria'] ?? ''),
                (float) str_replace(',', '.', (string) ($_POST['valor'] ?? '0')),
                (string) ($_POST['data'] ?? '')
            );
            $obRoteiro->salvar();
            Session::setFlash('sucesso', sprintf('"%s" foi adicionado ao roteiro. Novo total estimado: %s.', $descricao, self::moeda($obRoteiro->getValorTotalEstimado())));
        }catch(RegraNegocioException $e){
            Session::setFlash('erro', $e->getMessage());
        }

        self::redirecionar('/roteiros/editar?id=' . $obRoteiro->getIdRoteiro());
    }

    public static function postRemoverGasto(): void
    {
        $obRoteiro = self::getRoteiroDoPost();

        if($obRoteiro->removerGastoExtra((int) ($_POST['idGasto'] ?? 0))){
            $obRoteiro->salvar();
            Session::setFlash('sucesso', 'Gasto removido. Novo total estimado: ' . self::moeda($obRoteiro->getValorTotalEstimado()) . '.');
        }else{
            Session::setFlash('erro', 'Este gasto não foi encontrado no roteiro.');
        }

        self::redirecionar('/roteiros/editar?id=' . $obRoteiro->getIdRoteiro());
    }

    public static function postAnotacao(): void
    {
        $obRoteiro    = self::getRoteiroDoPost();
        $data         = (string) ($_POST['data'] ?? '');
        $semRecarregar = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';

        try{
            $obRoteiro->adicionarAnotacao($data, (string) ($_POST['texto'] ?? ''));
            $obRoteiro->salvar();

            if($semRecarregar){
                http_response_code(204);
                exit();
            }

            Session::setFlash('sucesso', 'Anotação salva.');
        }catch(RegraNegocioException $e){
            if($semRecarregar){
                http_response_code(422);
                echo $e->getMessage();
                exit();
            }

            Session::setFlash('erro', $e->getMessage());
        }

        self::redirecionar('/roteiros/editar?id=' . $obRoteiro->getIdRoteiro() . '#dia-' . $data);
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
            Session::setFlash('sucesso', 'Roteiro concluído. Agora ele está pronto para viajar.');
        }catch(RegraNegocioException $e){
            Session::setFlash('erro', $e->getMessage());
            self::redirecionar('/roteiros/editar?id=' . $obRoteiro->getIdRoteiro());
        }

        self::redirecionar('/roteiros/editar?id=' . $obRoteiro->getIdRoteiro() . '&compartilhar=1');
    }

    public static function postRevogarLink(): void
    {
        $obRoteiro = self::getRoteiroDoPost();

        try{
            $obRoteiro->revogarCompartilhamento();
            $obRoteiro->salvar();
            Session::setFlash('sucesso', 'Link desativado. Quem tinha o link não acessa mais o roteiro.');
        }catch(RegraNegocioException $e){
            Session::setFlash('erro', $e->getMessage());
        }

        self::redirecionar('/roteiros/editar?id=' . $obRoteiro->getIdRoteiro());
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

            foreach($obRoteiro->getGastosExtras() as $obGasto){
                if($obGasto->getData() === $data){
                    $itensHtml .= self::getGastoHtml($obRoteiro, $obGasto, $editavel);
                }
            }

            foreach($hospedagens as $obHospedagem){
                if($data > $obHospedagem->getDataAgendada() and $data <= $obHospedagem->getDataFim()){
                    $texto = $data === $obHospedagem->getDataFim() ? 'Check-out: ' : 'Hospedado(a) em ';
                    $itensHtml .= '<p class="flex items-center gap-2 px-[18px] py-2.5 text-[13px] text-apagado">' . Icones::get('cama', 'h-4 w-4 text-laranja') . $texto . self::e($obHospedagem->getDescricao()) . '</p>';
                }
            }

            if($itensHtml === ''){
                $itensHtml = '<p class="px-[18px] py-3.5 text-[13.5px] text-apagado">Dia livre, nenhum serviço agendado.</p>';
            }

            $dias[] = [
                'data'          => $data,
                'numero'        => $indice + 1,
                'dataFormatada' => Formatador::data($data),
                'diaSemana'     => Formatador::diaSemana($data),
                'itens'         => $itensHtml,
                'anotacao'      => self::getAnotacaoHtml($obRoteiro, $data, $indice + 1, $editavel),
                'acoesDia'      => $editavel
                    ? sprintf(
                        '<div class="ml-auto flex flex-wrap items-center gap-x-3.5 gap-y-1"><a href="/turismo?roteiro=%d&data=%s" class="inline-flex items-center gap-1 text-[13.5px] font-bold text-laranja-escuro hover:underline">%sGuia ou passeio</a><button type="button" data-abrir-janela="janela-gasto" data-dia="%s" class="inline-flex cursor-pointer items-center gap-1 text-[13.5px] font-bold text-laranja-escuro hover:underline">%sOutro gasto</button></div>',
                        $obRoteiro->getIdRoteiro(),
                        $data,
                        Icones::get('mais', 'h-4 w-4'),
                        $data,
                        Icones::get('carteira', 'h-4 w-4')
                    )
                    : ''
            ];
        }

        return ViewRenderer::getLista($dias, self::$pathComponentesRoteiro, 'dia.php');
    }

    private static function getAnotacaoHtml(Roteiro $obRoteiro, string $data, int $numeroDia, bool $editavel): string
    {
        $texto = $obRoteiro->getAnotacaoDoDia($data)?->getTexto() ?? '';

        if($editavel){
            return ViewRenderer::getComponente([
                'idRoteiro' => $obRoteiro->getIdRoteiro(),
                'data'      => $data,
                'numeroDia' => $numeroDia,
                'texto'     => self::e($texto),
                'maximo'    => Anotacao::TAMANHO_MAXIMO_TEXTO
            ], self::$pathComponentesRoteiro, 'anotacao.php');
        }

        return $texto === '' ? '' : '<div class="px-[18px] pb-3.5 pt-1"><p class="rounded-r-md border-l-3 border-laranja bg-[#fbfcfe] px-3 py-2 text-[13.5px]">' . self::e($texto) . '</p></div>';
    }

    private static function getItemHtml(Roteiro $obRoteiro, ItemRoteiro $obItem, bool $editavel): string
    {
        $icone = match($obItem->getTipoServico()){
            ItemRoteiro::TIPO_TRANSPORTE => match(true){
                $obItem->getTransporte() instanceof VeiculoPessoal,
                str_starts_with($obItem->getDescricao(), 'Carona') => 'carro',
                str_starts_with($obItem->getDescricao(), 'Ônibus') => 'onibus',
                default                                            => 'aviao'
            },
            ItemRoteiro::TIPO_HOSPEDAGEM => 'cama',
            ItemRoteiro::TIPO_GUIA       => 'pessoas',
            default                      => 'ingresso'
        };

        $detalhes = $obItem->getDetalhes();
        if($obItem->getTipoServico() === ItemRoteiro::TIPO_HOSPEDAGEM){
            $noites   = Validador::diasEntre($obItem->getDataAgendada(), (string) $obItem->getDataFim());
            $detalhes = 'Check-in · ' . $noites . ($noites === 1 ? ' noite · ' : ' noites · ') . $detalhes;
        }

        $controleQuantidade = ($editavel and $obItem->isCobradoPorPessoa() and !$obItem->getTransporte() instanceof VeiculoPessoal)
            ? ViewRenderer::getComponente([
                'idRoteiro'     => $obRoteiro->getIdRoteiro(),
                'idItem'        => $obItem->getIdItem(),
                'quantidade'    => $obItem->getQuantidadePessoas(),
                'maximo'        => ItemRoteiro::QUANTIDADE_MAXIMA_PESSOAS,
                'valorUnitario' => number_format($obItem->getValorUnitario(), 2, '.', '')
            ], self::$pathComponentesRoteiro, 'controle-quantidade.php')
            : '';

        return ViewRenderer::getComponente([
            'ancora'             => 'item-' . $obItem->getIdItem(),
            'idSubtotal'         => $obItem->getIdItem(),
            'icone'              => Icones::get($icone, 'h-[19px] w-[19px]'),
            'corIcone'           => self::COR_ICONE_TIPO[$obItem->getTipoServico()],
            'descricao'          => self::e($obItem->getDescricao()),
            'detalhes'           => self::e($detalhes),
            'quantidade'         => self::e($obItem->getDescricaoQuantidade()),
            'subtotal'           => self::moeda($obItem->getSubtotal()),
            'controleQuantidade' => $controleQuantidade,
            'botaoRemover'       => $editavel ? self::getBotaoRemover($obRoteiro, '/roteiros/itens/remover', 'idItem', $obItem->getIdItem(), $obItem->getDescricao()) : ''
        ], self::$pathComponentesRoteiro, 'item.php');
    }

    private static function getGastoHtml(Roteiro $obRoteiro, GastoExtra $obGasto, bool $editavel): string
    {
        return ViewRenderer::getComponente([
            'ancora'             => 'gasto-' . $obGasto->getIdGasto(),
            'idSubtotal'         => 'gasto-' . $obGasto->getIdGasto(),
            'icone'              => Icones::get('carteira', 'h-[19px] w-[19px]'),
            'corIcone'           => self::COR_ICONE_TIPO[self::TIPO_GASTO_EXTRA],
            'descricao'          => self::e($obGasto->getDescricao()),
            'detalhes'           => 'Gasto por fora · ' . self::e($obGasto->getCategoria()),
            'quantidade'         => '',
            'subtotal'           => self::moeda($obGasto->getValor()),
            'controleQuantidade' => '',
            'botaoRemover'       => $editavel ? self::getBotaoRemover($obRoteiro, '/roteiros/gastos/remover', 'idGasto', $obGasto->getIdGasto(), $obGasto->getDescricao()) : ''
        ], self::$pathComponentesRoteiro, 'item.php');
    }

    private static function getBotaoRemover(Roteiro $obRoteiro, string $acao, string $nomeCampo, int $id, string $descricao): string
    {
        return ViewRenderer::getComponente([
            'acao'         => $acao,
            'idRoteiro'    => $obRoteiro->getIdRoteiro(),
            'nomeCampo'    => $nomeCampo,
            'id'           => $id,
            'descricao'    => self::e($descricao),
            'iconeLixeira' => Icones::get('lixeira', 'h-[18px] w-[18px]')
        ], self::$pathComponentesRoteiro, 'botao-remover.php');
    }

    public static function getTotaisPorTipoHtml(Roteiro $obRoteiro): string
    {
        $rotulos = ItemRoteiro::getRotulosTipo() + [self::TIPO_GASTO_EXTRA => 'Outros gastos'];
        $totais  = $obRoteiro->getTotaisPorTipo() + [
            self::TIPO_GASTO_EXTRA => array_sum(array_map(fn(GastoExtra $obGasto) => $obGasto->getValor(), $obRoteiro->getGastosExtras()))
        ];
        $html    = '';

        foreach($totais as $tipo => $valor){
            $html .= sprintf(
                '<div class="flex items-center gap-2.5 border-t border-linha py-[9px] text-sm"><span class="h-2.5 w-2.5 flex-none rounded-full %s"></span>%s<span class="ml-auto font-bold">%s</span></div>',
                self::COR_MARCADOR_TIPO[$tipo],
                self::e($rotulos[$tipo]),
                self::moeda($valor)
            );
        }

        return $html;
    }

    private static function getOrcamentoHtml(Roteiro $obRoteiro): string
    {
        $saldo = $obRoteiro->calcularSaldoOrcamento();

        if($saldo === null){
            return '<div class="border-t border-linha pt-3"><span class="dica">Sem orçamento definido. Informe quanto quer gastar em "Editar dados da viagem".</span></div>';
        }

        $orcamento  = $obRoteiro->getOrcamento();
        $percentual = $orcamento > 0 ? min(100, $obRoteiro->calcularValorTotal() / $orcamento * 100) : 100;
        $estourou   = $obRoteiro->isOrcamentoUltrapassado();

        return sprintf(
            '<div class="flex flex-col gap-1.5 border-t border-linha pt-3 text-sm"><div class="flex items-center justify-between gap-3"><span class="dica font-semibold">Quanto quer gastar</span><b>%s</b></div><div class="h-2 overflow-hidden rounded-full bg-suave"><i class="block h-full rounded-full %s" style="width:%s%%"></i></div><span class="text-[13px] font-bold %s">%s</span></div>',
            self::moeda($orcamento),
            $estourou ? 'bg-alerta' : 'bg-ok',
            number_format($percentual, 1, '.', ''),
            $estourou ? 'text-alerta' : 'text-ok',
            $estourou ? self::moeda(-$saldo) . ' acima do orçamento' : 'Ainda cabem ' . self::moeda($saldo) . ' no orçamento'
        );
    }

    private static function getPainelCompartilhamento(Roteiro $obRoteiro): string
    {
        $variaveis = ['id' => $obRoteiro->getIdRoteiro(), 'iconeLink' => Icones::get('link', 'h-[18px] w-[18px]')];

        if(empty($obRoteiro->getItens())){
            $acao = ViewRenderer::getComponente($variaveis, self::$pathComponentesRoteiro, 'painel-sem-itens.php');
        }elseif($obRoteiro->possuiLinkAtivo()){
            $obCompartilhamento = $obRoteiro->getCompartilhamento();
            $expiracao          = $obCompartilhamento->getDataExpiracao();
            $diasRestantes      = $expiracao ? max(0, Validador::diasEntre(date('Y-m-d'), substr($expiracao, 0, 10))) : null;

            $acao = '<button type="button" class="btn-destaque" data-abrir-janela="janela-link">' . $variaveis['iconeLink'] . 'Compartilhar link</button>'
                  . ViewRenderer::getComponente($variaveis + [
                      'urlCompleta'     => self::e(self::urlAbsoluta($obCompartilhamento->gerarLinkAcesso())),
                      'urlRelativa'     => self::e($obCompartilhamento->gerarLinkAcesso()),
                      'expiracao'       => $diasRestantes === null ? 'Sem data de expiração' : 'Expira em ' . $diasRestantes . ($diasRestantes === 1 ? ' dia' : ' dias'),
                      'abrirAoCarregar' => isset($_GET['compartilhar']) ? 'data-abrir-ao-carregar' : '',
                      'iconeFechar'     => Icones::get('fechar', 'h-5 w-5'),
                      'iconeOlho'       => Icones::get('olho', 'h-[18px] w-[18px]')
                  ], self::$pathComponentesRoteiro, 'painel-link.php');
        }else{
            $obCompartilhamento = $obRoteiro->getCompartilhamento();
            $aviso = $obCompartilhamento
                ? '<p class="rounded-lg bg-fundo px-3 py-2 text-[12.5px] text-apagado">O link anterior ' . ($obCompartilhamento->isExpirado() ? 'expirou' : 'foi desativado') . ' e não funciona mais. Gere um novo link se quiser compartilhar de novo.</p>'
                : '';

            $acao = $aviso . sprintf(
                '<form method="POST" action="/roteiros/finalizar" class="flex flex-col">{{csrf}}<input type="hidden" name="idRoteiro" value="%d"><button type="submit" class="btn-destaque">%s%s</button></form>',
                $obRoteiro->getIdRoteiro(),
                $variaveis['iconeLink'],
                $obRoteiro->isFinalizado() ? 'Gerar novo link' : 'Concluir e gerar link'
            );
        }

        return ViewRenderer::getComponente(['acao' => $acao], self::$pathComponentesRoteiro, 'painel-finalizar.php');
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
