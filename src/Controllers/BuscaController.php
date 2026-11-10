<?php

namespace Controllers;

use Models\ItemRoteiro;
use Models\Roteiro;
use Models\Usuario;
use Session\Session;
use Utils\Formatador;
use Views\Icones;
use Views\ViewRenderer;

abstract class BuscaController extends Controller
{
    protected static array $roteirosUsuario = [];

    protected static ?Roteiro $roteiroContexto = null;

    protected static function carregarRoteiros(Usuario $obUsuario): void
    {
        self::$roteirosUsuario = Roteiro::listarPorUsuario($obUsuario->getIdUsuario());
        self::$roteiroContexto = null;

        $idContexto = (int) ($_GET['roteiro'] ?? 0);
        foreach(self::$roteirosUsuario as $obRoteiro){
            if($obRoteiro->getIdRoteiro() === $idContexto){
                self::$roteiroContexto = $obRoteiro;
            }
        }
    }

    protected static function getPrimeiraCidadeContexto(): string
    {
        $destinos = self::$roteiroContexto?->getDestinos() ?? [];

        return isset($destinos[0]) ? $destinos[0]->getCidade() : '';
    }

    protected static function getBannerContexto(): string
    {
        $obRoteiro = self::$roteiroContexto;
        if(!$obRoteiro){
            return '';
        }

        return sprintf(
            '<div class="mb-6 flex flex-col gap-3 rounded-2xl border border-orange-200 bg-orange-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-orange-700">Montando o roteiro</p>
                    <p class="font-semibold text-blue-950">%s</p>
                    <p class="text-sm text-slate-600">%s · %s a %s · total atual %s</p>
                </div>
                <a href="/roteiros/editar?id=%d" class="btn-secundario h-10 shrink-0 text-sm">Voltar ao roteiro</a>
            </div>',
            self::e($obRoteiro->getTitulo()),
            self::e($obRoteiro->getNomesDestinos()),
            Formatador::data($obRoteiro->getDataInicio()),
            Formatador::data($obRoteiro->getDataFim()),
            self::moeda($obRoteiro->getValorTotalEstimado()),
            $obRoteiro->getIdRoteiro()
        );
    }

    protected static function getCampoRoteiroContexto(): string
    {
        return self::$roteiroContexto ? '<input type="hidden" name="roteiro" value="' . self::$roteiroContexto->getIdRoteiro() . '">' : '';
    }

    protected static function getFormAdicionar(array $camposOcultos, ?string $motivoIndisponivel = null): string
    {
        if($motivoIndisponivel !== null){
            return '<p class="w-full rounded-xl bg-slate-100 px-3 py-2 text-center text-sm font-medium text-slate-500">' . self::e($motivoIndisponivel) . '</p>';
        }

        if(empty(self::$roteirosUsuario)){
            return '<a href="/roteiros/novo" class="w-full rounded-xl border border-dashed border-blue-300 px-3 py-2 text-center text-sm font-semibold text-blue-700 hover:bg-blue-50">Crie um roteiro para adicionar este serviço</a>';
        }

        $campos = '';
        foreach($camposOcultos as $nome => $valor){
            $campos .= '<input type="hidden" name="' . self::e($nome) . '" value="' . self::e($valor) . '">';
        }

        $opcoes = [];
        foreach(self::$roteirosUsuario as $obRoteiro){
            $opcoes[$obRoteiro->getIdRoteiro()] = sprintf(
                '%s (%s a %s)',
                $obRoteiro->getTitulo(),
                Formatador::dataCurta($obRoteiro->getDataInicio()),
                Formatador::dataCurta($obRoteiro->getDataFim())
            );
        }

        return ViewRenderer::getComponente([
            'camposOcultos'  => $campos,
            'opcoesRoteiros' => self::opcoesSelect($opcoes, self::$roteiroContexto?->getIdRoteiro()),
            'voltar'         => self::e($_SERVER['REQUEST_URI'] ?? '/')
        ], self::$pathComponentes, 'form-adicionar.php');
    }

    protected static function getEstadoInicialBusca(string $titulo, string $mensagem): string
    {
        return self::getEstadoVazio($titulo, $mensagem);
    }
    protected const PAGINAS_ETAPAS = [
        'Transporte' => ['rotulo' => 'Transporte', 'tipos' => [ItemRoteiro::TIPO_TRANSPORTE]],
        'Hospedagem' => ['rotulo' => 'Hospedagem', 'tipos' => [ItemRoteiro::TIPO_HOSPEDAGEM]],
        'Turismo'    => ['rotulo' => 'Turismo', 'tipos' => [ItemRoteiro::TIPO_GUIA, ItemRoteiro::TIPO_PASSEIO]],
        'Revisão'    => ['rotulo' => 'Revisar roteiro', 'tipos' => []]
    ];

    public static function getUrlEtapa(Roteiro $obRoteiro, string $etapa): string
    {
        $id = $obRoteiro->getIdRoteiro();

        return match($etapa){
            'Transporte' => '/transporte?roteiro=' . $id,
            'Hospedagem' => '/hospedagem?roteiro=' . $id,
            'Turismo'    => '/turismo?roteiro=' . $id,
            default      => '/roteiros/editar?id=' . $id,
        };
    }

    protected static function exigirRoteiroContexto(Usuario $obUsuario): Roteiro
    {
        self::carregarRoteiros($obUsuario);

        if(self::$roteiroContexto instanceof Roteiro){
            return self::$roteiroContexto;
        }

        $emPlanejamento = array_filter(self::$roteirosUsuario, fn(Roteiro $obRoteiro) => $obRoteiro->getStatus() === Roteiro::STATUS_EM_PLANEJAMENTO);
        $obRoteiro      = reset($emPlanejamento) ?: reset(self::$roteirosUsuario);

        if(!$obRoteiro instanceof Roteiro){
            Session::setFlash('info', 'Crie um roteiro primeiro: preencha o quadro "Planeje sua próxima viagem".');
            self::redirecionar('/home');
        }

        self::$roteiroContexto = $obRoteiro;
        return $obRoteiro;
    }

    protected static function getNomeDestino(Roteiro $obRoteiro): string
    {
        $destinos = $obRoteiro->getDestinos();

        return isset($destinos[0]) ? $destinos[0]->getCidade() . ' - ' . $destinos[0]->getEstado() : '';
    }

    protected static function getCabecalhoPagina(Roteiro $obRoteiro, string $titulo, string $descricao, string $icone, string $cores): string
    {
        $pessoas = $obRoteiro->getQuantidadePessoas();

        return sprintf(
            '<div class="border-b border-linha bg-white" style="%s">
                <div class="container-app flex flex-wrap items-center justify-between gap-x-8 gap-y-5 pb-6 pt-[22px]">
                    <div class="flex min-w-0 items-center gap-4">
                        <span class="grid h-14 w-14 flex-none place-items-center rounded-[14px] bg-(--mc) text-white shadow-[0_8px_18px_-10px_var(--mc)]">%s</span>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-1.5 text-[12.5px] font-semibold text-apagado"><a href="/roteiros" class="font-bold hover:text-navy hover:underline">Meus roteiros</a>/<a href="/roteiros/editar?id=%d" class="font-bold hover:text-navy hover:underline">%s</a></div>
                            <h1 class="mt-0.5 text-[28px] font-extrabold leading-tight tracking-[-.01em]">%s</h1>
                            <p class="mt-0.5 text-[14.5px] text-apagado">%s</p>
                        </div>
                    </div>
                    <div class="flex max-w-full items-center gap-[18px] rounded-xl border border-navy bg-navy py-3 pl-[18px] pr-3 shadow-[0_10px_24px_-14px_rgba(19,40,77,.7)]">
                        <div class="flex min-w-0 flex-col gap-0.5 text-[13.5px]">
                            <b class="flex items-center gap-1.5 font-display text-[14.5px] text-white"><span class="text-laranja-claro">%s</span>%s → %s</b>
                            <span class="flex flex-wrap items-center gap-1.5 text-menu-claro">%s%s – %s · %s%d %s</span>
                        </div>
                        <a href="/roteiros/editar?id=%d" class="btn-vidro !h-9 !px-3 !text-[13px]">Ver roteiro</a>
                    </div>
                </div>
            </div>',
            $cores,
            Icones::get($icone, 'h-7 w-7'),
            $obRoteiro->getIdRoteiro(),
            self::e($obRoteiro->getTitulo()),
            self::e($titulo),
            self::e($descricao),
            Icones::get('pin', 'h-4 w-4'),
            self::e($obRoteiro->getOrigem() ?: 'Origem não informada'),
            self::e(self::getNomeDestino($obRoteiro)),
            Icones::get('calendario', 'h-3.5 w-3.5'),
            Formatador::data($obRoteiro->getDataInicio()),
            Formatador::data($obRoteiro->getDataFim()),
            Icones::get('pessoas', 'h-3.5 w-3.5'),
            $pessoas,
            $pessoas === 1 ? 'pessoa' : 'pessoas',
            $obRoteiro->getIdRoteiro()
        );
    }

    protected static function roteiroTemItemDoTipo(Roteiro $obRoteiro, array $tipos): bool
    {
        foreach($obRoteiro->getItens() as $obItem){
            if(in_array($obItem->getTipoServico(), $tipos, true)){
                return true;
            }
        }

        return false;
    }

    protected static function getBarraEtapas(Roteiro $obRoteiro, string $etapaDaTela): string
    {
        if($obRoteiro->getStatus() !== Roteiro::STATUS_EM_PLANEJAMENTO){
            return '';
        }

        $etapas  = array_keys(self::PAGINAS_ETAPAS);
        $posicao = array_search($etapaDaTela, $etapas, true);
        $passos  = '';

        foreach($etapas as $indice => $etapa){
            $temItens = self::roteiroTemItemDoTipo($obRoteiro, self::PAGINAS_ETAPAS[$etapa]['tipos']);
            $atual    = $indice === $posicao;
            $feita    = !$atual && ($indice < $posicao || $temItens);

            $classeNumero = match(true){
                $atual  => 'border-navy bg-navy text-white shadow-[0_0_0_4px_var(--color-suave)]',
                $feita  => 'border-ok bg-ok text-white',
                default => 'border-linha bg-white',
            };

            $passos .= sprintf(
                '<li class="flex items-center gap-2 text-[13.5px] font-bold %s %s"><span class="grid h-7 w-7 flex-none place-items-center rounded-full border-2 text-[12.5px] font-extrabold %s">%s</span><span class="hidden sm:inline">%s</span></li>',
                $indice > 0 ? 'before:h-0.5 before:w-[22px] before:rounded before:bg-linha' : '',
                $atual ? 'text-tinta' : ($feita ? 'text-ok' : 'text-apagado'),
                $classeNumero,
                $feita ? '✓' : $indice + 1,
                self::e(self::PAGINAS_ETAPAS[$etapa]['rotulo'])
            );
        }

        $rotuloEtapa = strtolower(self::PAGINAS_ETAPAS[$etapaDaTela]['rotulo']);
        $dica        = self::roteiroTemItemDoTipo($obRoteiro, self::PAGINAS_ETAPAS[$etapaDaTela]['tipos'])
            ? self::PAGINAS_ETAPAS[$etapaDaTela]['rotulo'] . ' adicionado ao roteiro.'
            : 'Nenhum item de ' . $rotuloEtapa . ' ainda. Você pode pular e adicionar depois.';

        $proxima      = $etapas[$posicao + 1];
        $botaoVoltar  = $posicao > 0 ? '<a href="' . self::getUrlEtapa($obRoteiro, $etapas[$posicao - 1]) . '" class="btn-secundario">Voltar</a>' : '';
        $rotuloBotao  = $proxima === 'Revisão' ? 'Revisar roteiro' : 'Continuar para ' . self::PAGINAS_ETAPAS[$proxima]['rotulo'];

        return sprintf(
            '<div class="h-28 md:h-20"></div>
            <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-linha bg-white pb-[env(safe-area-inset-bottom)] shadow-[0_-10px_28px_rgba(19,40,77,.1)]" aria-label="Etapas do roteiro">
                <div class="container-app flex flex-wrap items-center gap-x-5 gap-y-3 py-3">
                    <ol class="flex flex-wrap items-center gap-2">%s</ol>
                    <div class="ml-auto flex flex-wrap items-center justify-end gap-3">
                        <span class="dica hidden xl:inline">%s</span>
                        %s
                        <form method="POST" action="/roteiros/avancar-etapa">
                            <input type="hidden" name="_csrf" value="%s">
                            <input type="hidden" name="idRoteiro" value="%d">
                            <input type="hidden" name="etapa" value="%s">
                            <button type="submit" class="btn-destaque">%s %s</button>
                        </form>
                    </div>
                </div>
            </nav>',
            $passos,
            self::e($dica),
            $botaoVoltar,
            Session::getTokenCsrf(),
            $obRoteiro->getIdRoteiro(),
            self::e($etapaDaTela),
            self::e($rotuloBotao),
            Icones::get('seta', 'h-[18px] w-[18px]')
        );
    }

    protected static function getBotaoAdicionar(Roteiro $obRoteiro, array $camposOcultos, bool $jaNoRoteiro, string $rotulo = 'Adicionar ao roteiro'): string
    {
        if($jaNoRoteiro){
            return '<button class="btn-secundario !cursor-default !border-transparent !bg-ok-suave !text-ok" disabled>No roteiro ✓</button>';
        }

        return '<form method="POST" action="/roteiros/itens/adicionar">' . self::getCamposAdicionar($obRoteiro, $camposOcultos) . '<button type="submit" class="btn-destaque">' . Icones::get('mais', 'h-4 w-4') . self::e($rotulo) . '</button></form>';
    }

    protected static function getCamposAdicionar(Roteiro $obRoteiro, array $camposOcultos): string
    {
        $campos = '<input type="hidden" name="_csrf" value="' . Session::getTokenCsrf() . '">'
                . '<input type="hidden" name="idRoteiro" value="' . $obRoteiro->getIdRoteiro() . '">'
                . '<input type="hidden" name="voltar" value="' . self::e($_SERVER['REQUEST_URI'] ?? '/') . '">';

        foreach($camposOcultos as $nome => $valor){
            $campos .= '<input type="hidden" name="' . self::e($nome) . '" value="' . self::e($valor) . '">';
        }

        return $campos;
    }

    protected static function getAbasModo(string $url, array $modos, string $modoAtual): string
    {
        $abas = '';
        foreach($modos as $modo => $dados){
            $ativa = $modo === $modoAtual;
            $abas .= sprintf(
                '<a href="%s&modo=%s" style="%s" aria-pressed="%s" class="flex items-center gap-3.5 rounded-xl border px-4 py-3.5 font-bold transition %s">
                    <span class="grid h-12 w-12 flex-none place-items-center rounded-xl %s">%s</span>
                    <span class="flex min-w-0 flex-col text-[15px] %s">%s<small class="text-[11.5px] font-semibold text-apagado">%s</small></span>
                </a>',
                $url,
                $modo,
                $dados['cores'],
                $ativa ? 'true' : 'false',
                $ativa ? 'border-(--mc) bg-(--ms) shadow-[inset_0_0_0_1px_var(--mc),0_6px_18px_-8px_var(--mc)]' : 'border-linha bg-white text-navy hover:border-(--mc)',
                $ativa ? 'bg-(--mc) text-white' : 'bg-(--ms) text-(--mc)',
                Icones::get($dados['icone'], 'h-[26px] w-[26px]'),
                $ativa ? 'text-(--mc)' : '',
                self::e($dados['rotulo']),
                self::e($dados['legenda'])
            );
        }

        return $abas;
    }

    protected static function getGaleria(array $fotos, string $nome): string
    {
        $caminhos = array_map(fn($foto) => '/public/img/' . $foto, $fotos);
        $pontos   = '';

        foreach(array_keys($caminhos) as $indice){
            $pontos .= '<i class="h-1.5 rounded-full bg-white ' . ($indice === 0 ? 'w-4' : 'w-1.5 opacity-55') . '"></i>';
        }

        $classeBotao = 'absolute top-1/2 grid h-[30px] w-[30px] -translate-y-1/2 place-items-center rounded-full bg-white/90 text-tinta shadow-[0_2px_6px_rgba(0,0,0,.25)] hover:bg-white';

        return sprintf(
            '<div class="relative h-[150px] overflow-hidden rounded-[10px] bg-(--mc)" data-galeria="%s">
                <img src="%s" alt="Foto de %s. Clique para ampliar" class="block h-full w-full cursor-zoom-in object-cover" data-ampliar>
                <button type="button" data-galeria-passo="-1" aria-label="Foto anterior" class="%s left-2 rotate-180">%s</button>
                <button type="button" data-galeria-passo="1" aria-label="Próxima foto" class="%s right-2">%s</button>
                <span data-galeria-contagem class="absolute right-2 top-2 rounded-full bg-[rgba(15,29,54,.7)] px-[7px] py-0.5 text-[11px] font-bold text-white">1/%d</span>
                <span data-galeria-pontos class="absolute inset-x-0 bottom-2 flex justify-center gap-[5px]">%s</span>
            </div>',
            self::e(json_encode($caminhos, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)),
            self::e($caminhos[0] ?? ''),
            self::e($nome),
            $classeBotao,
            Icones::get('seta', 'h-4 w-4'),
            $classeBotao,
            Icones::get('seta', 'h-4 w-4'),
            count($caminhos),
            $pontos
        );
    }

    protected static function getEtiquetas(array $etiquetas, array $destacadas = []): string
    {
        $html = '';
        foreach($etiquetas as $etiqueta){
            $cores = in_array($etiqueta, $destacadas, true) ? 'bg-laranja-suave text-laranja-escuro' : 'bg-(--ms) text-(--mc)';
            $html .= '<span class="rounded-full px-[9px] py-[3px] text-xs font-semibold ' . $cores . '">' . self::e($etiqueta) . '</span>';
        }

        return $html;
    }
}
