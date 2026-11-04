<?php

namespace Controllers;

use Models\Roteiro;
use Models\Usuario;
use Utils\Formatador;
use Views\ViewRenderer;

/**
 * Comportamentos comuns às páginas de pesquisa (transporte, hospedagem e turismo):
 * roteiro de contexto (?roteiro=ID) e o formulário "Adicionar ao roteiro".
 */
abstract class BuscaController extends Controller
{
    /** @var Roteiro[] */
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

    /**
     * Faixa destacando o roteiro que está sendo montado.
     */
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

    /**
     * Campo oculto com o roteiro de contexto, para mantê-lo ao refazer a busca.
     */
    protected static function getCampoRoteiroContexto(): string
    {
        return self::$roteiroContexto ? '<input type="hidden" name="roteiro" value="' . self::$roteiroContexto->getIdRoteiro() . '">' : '';
    }

    /**
     * Formulário "Adicionar ao roteiro" exibido em cada resultado da busca.
     */
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
}
