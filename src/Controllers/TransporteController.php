<?php

namespace Controllers;

use Models\ItemRoteiro;
use Models\Roteiro;
use Models\Transporte;
use Models\VeiculoPessoal;
use Services\ApiTransporteSimulada;
use Utils\Formatador;
use Views\Icones;
use Views\ViewRenderer;

class TransporteController extends BuscaController
{
    private static $pathComponentesTransporte = __DIR__ . '/../Views/transporte/components/';

    private const PRECO_MAXIMO_FILTRO = 1000;

    private const MODOS = [
        'aviao'   => ['rotulo' => 'Avião', 'legenda' => 'passagens aéreas', 'tipo' => ApiTransporteSimulada::TIPO_VOO, 'icone' => 'aviao', 'cores' => '--mc:#2563c9;--ms:#e8f0fd;--mb:#bcd2f6'],
        'onibus'  => ['rotulo' => 'Ônibus', 'legenda' => 'passagens rodoviárias', 'tipo' => ApiTransporteSimulada::TIPO_ONIBUS, 'icone' => 'onibus', 'cores' => '--mc:#d9580f;--ms:#fdeee4;--mb:#f6caa9'],
        'carona'  => ['rotulo' => 'Carona', 'legenda' => 'via BlaBlaCar', 'tipo' => ApiTransporteSimulada::TIPO_CARONA, 'icone' => 'pessoas', 'cores' => '--mc:#1f8a5b;--ms:#e3f4ec;--mb:#b4e0c9'],
        'veiculo' => ['rotulo' => 'Veículo pessoal', 'legenda' => 'estimativa de custo', 'tipo' => Transporte::TIPO_VEICULO_PESSOAL, 'icone' => 'carro', 'cores' => '--mc:#7046c9;--ms:#efe9fb;--mb:#d3c4f3']
    ];

    private const FUNDO_LOGO = ['o2' => '#d81a60', 'o3' => '#0a4386'];

    public static function getBusca(): string
    {
        $obUsuario = self::exigirAutenticacao();
        $obRoteiro = self::exigirRoteiroContexto($obUsuario);
        $modo      = self::parametroPermitido('modo', self::MODOS, 'onibus');

        $conteudo = $modo === 'veiculo'
            ? self::getEstimativaVeiculo($obRoteiro)
            : self::getOfertas($obRoteiro, $modo);

        return self::renderizarPagina('transporte/busca.php', [
            'abas'     => self::getAbasModo('/transporte?roteiro=' . $obRoteiro->getIdRoteiro(), self::MODOS, $modo),
            'conteudo' => $conteudo
        ], 'transporte', 'Transporte', [
            'cabecalhoPagina' => self::getCabecalhoPagina($obRoteiro, 'Transporte', 'Compare todas as formas de chegar ao destino, inclusive com o seu próprio veículo.', self::MODOS[$modo]['icone'], self::MODOS[$modo]['cores']),
            'barraEtapas'     => self::getBarraEtapas($obRoteiro, 'Transporte')
        ]);
    }

    private static function getOfertas(Roteiro $obRoteiro, string $modo): string
    {
        $origem      = explode(' - ', $obRoteiro->getOrigem())[0];
        $destino     = self::getNomeDestino($obRoteiro);
        $tipo        = self::MODOS[$modo]['tipo'];
        $pessoas     = $obRoteiro->getQuantidadePessoas();
        $filtroPreco = (int) ($_GET['precoMaximo'] ?? self::PRECO_MAXIMO_FILTRO);
        $filtroPreco = max(50, min(self::PRECO_MAXIMO_FILTRO, $filtroPreco));

        $todas  = Transporte::pesquisar($origem, $destino, $obRoteiro->getDataInicio(), null, $tipo);
        $opcoes = $filtroPreco >= self::PRECO_MAXIMO_FILTRO
            ? $todas
            : Transporte::pesquisar($origem, $destino, $obRoteiro->getDataInicio(), (float) $filtroPreco, $tipo);

        $filtro = ViewRenderer::getComponente([
            'idRoteiro'    => $obRoteiro->getIdRoteiro(),
            'modo'         => $modo,
            'valorFiltro'  => $filtroPreco,
            'maximoFiltro' => self::PRECO_MAXIMO_FILTRO,
            'textoFiltro'  => $filtroPreco >= self::PRECO_MAXIMO_FILTRO ? 'qualquer valor' : 'até ' . self::moeda($filtroPreco),
            'contagem'     => count($opcoes) . ' de ' . count($todas) . ' opções'
        ], self::$pathComponentesTransporte, 'filtro.php');

        if($origem === ''){
            return $filtro . self::getCartaoVazio('Informe a cidade de origem do roteiro para pesquisarmos as passagens.');
        }

        if(empty($opcoes)){
            $mensagem = empty($todas)
                ? 'Não encontramos opções desse tipo de transporte de ' . $origem . ' para ' . $destino . '. Tente outro tipo de transporte.'
                : 'Nenhuma opção até ' . self::moeda($filtroPreco) . ' por pessoa. Arraste o filtro para a direita para ver opções mais caras.';

            return $filtro . self::getCartaoVazio($mensagem);
        }

        $maisBarato = min(array_map(fn(Transporte $obTransporte) => $obTransporte->getPreco(), $opcoes));
        $maisRapido = min(array_map(fn(Transporte $obTransporte) => $obTransporte->getDuracaoMinutos(), $opcoes));
        $noRoteiro  = array_map(fn(ItemRoteiro $obItem) => (string) $obItem->getIdServico(), $obRoteiro->getItens());
        $variasOpcoes = count($opcoes) > 1;

        $cards = array_map(function(Transporte $obTransporte) use ($obRoteiro, $modo, $pessoas, $origem, $destino, $maisBarato, $maisRapido, $noRoteiro, $variasOpcoes){
            $apresentacao = ApiTransporteSimulada::getApresentacao($obTransporte->getIdTransporte());
            $diasDepois   = (int) (new \DateTime(substr($obTransporte->getDataHoraPartida(), 0, 10)))->diff(new \DateTime(substr($obTransporte->getDataHoraChegada(), 0, 10)))->days;
            $etiquetas    = '';

            if($variasOpcoes and $obTransporte->getPreco() == $maisBarato){
                $etiquetas .= '<span class="rounded-full bg-ok-suave px-2.5 py-0.5 text-[11.5px] font-extrabold text-ok">Mais barato</span>';
            }

            if($variasOpcoes and $obTransporte->getDuracaoMinutos() === $maisRapido){
                $etiquetas .= '<span class="rounded-full bg-[#e8f0fd] px-2.5 py-0.5 text-[11.5px] font-extrabold text-[#2563c9]">Mais rápido</span>';
            }

            $fundoLogo = self::FUNDO_LOGO[$obTransporte->getIdTransporte()] ?? '';

            return [
                'cores'       => self::MODOS[$modo]['cores'],
                'estiloLogo'  => $fundoLogo ? 'background:' . $fundoLogo . ';border-color:' . $fundoLogo : '',
                'logo'        => '/public/img/transporte/' . self::e($apresentacao['logo'] ?? ''),
                'empresa'     => self::e($modo === 'carona' ? 'BlaBlaCar' : $obTransporte->getEmpresa()),
                'motorista'   => $modo === 'carona' ? '<span class="whitespace-nowrap text-[11.5px] font-bold text-tinta">' . self::e($obTransporte->getEmpresa() . ' · ' . str_replace('via BlaBlaCar · ', '', $apresentacao['subtitulo'] ?? '')) . '</span>' : '',
                'etiquetas'   => $etiquetas ? '<div class="mb-1.5 flex flex-wrap gap-1.5">' . $etiquetas . '</div>' : '',
                'horaPartida' => Formatador::hora($obTransporte->getDataHoraPartida()),
                'horaChegada' => Formatador::hora($obTransporte->getDataHoraChegada()),
                'diasDepois'  => $diasDepois > 0 ? '<sup class="text-[11px] text-laranja-escuro">+' . $diasDepois . ' dia</sup>' : '',
                'icone'       => Icones::get(self::MODOS[$modo]['icone'], 'box-content h-[22px] w-[22px] bg-white px-[3px] text-(--mc)'),
                'duracao'     => intdiv($obTransporte->getDuracaoMinutos(), 60) . 'h' . str_pad((string) ($obTransporte->getDuracaoMinutos() % 60), 2, '0', STR_PAD_LEFT),
                'detalhes'    => self::e($apresentacao['detalhes'] ?? $obTransporte->getDetalhesServico()),
                'total'       => self::moeda($obTransporte->calcularCusto($pessoas)),
                'porPessoa'   => self::moeda($obTransporte->getPreco()) . ' por pessoa · ' . $pessoas . ($pessoas === 1 ? ' pessoa' : ' pessoas'),
                'linkSite'    => self::e($apresentacao['linkSite'] ?? '#'),
                'iconeSite'   => Icones::get('externo', 'h-4 w-4'),
                'botao'       => self::getBotaoAdicionar($obRoteiro, [
                    'tipoServico' => ItemRoteiro::TIPO_TRANSPORTE,
                    'idServico'   => $obTransporte->getIdTransporte(),
                    'origem'      => $origem,
                    'destino'     => $destino,
                    'data'        => $obRoteiro->getDataInicio(),
                    'pessoas'     => $pessoas
                ], in_array($obTransporte->getIdTransporte(), $noRoteiro, true))
            ];
        }, $opcoes);

        return $filtro . '<div class="flex flex-col gap-3">' . ViewRenderer::getLista($cards, self::$pathComponentesTransporte, 'card.php') . '</div>';
    }

    private static function getEstimativaVeiculo(Roteiro $obRoteiro): string
    {
        $opcoesVeiculo     = array_combine(VeiculoPessoal::TIPOS_VEICULO, VeiculoPessoal::TIPOS_VEICULO);
        $opcoesCombustivel = array_combine(VeiculoPessoal::COMBUSTIVEIS, VeiculoPessoal::COMBUSTIVEIS);

        return ViewRenderer::getComponente([
            'idRoteiro'         => $obRoteiro->getIdRoteiro(),
            'opcoesVeiculo'     => self::opcoesSelect($opcoesVeiculo, 'Carro'),
            'opcoesCombustivel' => self::opcoesSelect($opcoesCombustivel, 'Gasolina'),
            'voltar'            => self::e($_SERVER['REQUEST_URI'] ?? '/transporte'),
            'iconeMais'         => Icones::get('mais', 'h-4 w-4')
        ], self::$pathComponentesTransporte, 'veiculo.php');
    }

    private static function getCartaoVazio(string $mensagem): string
    {
        return '<div class="cartao text-center text-apagado">' . self::e($mensagem) . '</div>';
    }
}
