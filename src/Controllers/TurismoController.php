<?php

namespace Controllers;

use BD\TabelaJson;
use Models\GuiaTuristico;
use Models\ItemRoteiro;
use Models\Passeio;
use Models\Roteiro;
use Utils\Formatador;
use Utils\Texto;
use Views\Icones;
use Views\ViewRenderer;

class TurismoController extends BuscaController
{
    private static $pathComponentesTurismo = __DIR__ . '/../Views/turismo/components/';

    private const MODOS = [
        'guias'    => ['rotulo' => 'Guias locais', 'legenda' => 'guias credenciados da região', 'icone' => 'pessoas', 'cores' => '--mc:#1f8a5b;--ms:#e3f4ec;--mb:#b4e0c9'],
        'passeios' => ['rotulo' => 'Passeios e atrações', 'legenda' => 'ingressos e passeios', 'icone' => 'ingresso', 'cores' => '--mc:#7046c9;--ms:#efe9fb;--mb:#d3c4f3']
    ];

    public static function getBusca(): string
    {
        $obUsuario = self::exigirAutenticacao();
        $obRoteiro = self::exigirRoteiroContexto($obUsuario);
        $modo      = self::parametroPermitido('modo', self::MODOS, 'guias');
        $termo     = trim((string) ($_GET['q'] ?? ''));
        $datas     = $obRoteiro->getDatasDaViagem();
        $diaPedido = in_array($_GET['data'] ?? '', $datas, true) ? $_GET['data'] : null;
        $destino   = self::getNomeDestino($obRoteiro);

        [$cards, $componente, $unidade] = $modo === 'guias'
            ? [self::getCardsGuias($obRoteiro, $termo, $diaPedido ?? $datas[min(1, count($datas) - 1)]), 'card-guia.php', 'guia']
            : [self::getCardsPasseios($obRoteiro, $termo, $diaPedido ?? $datas[min(2, count($datas) - 1)]), 'card-passeio.php', 'passeio'];

        $notaDia = $diaPedido ? ' · adicionando ao dia ' . (array_search($diaPedido, $datas, true) + 1) . ' (' . Formatador::dataCurta($diaPedido) . ')' : '';

        $resultados = empty($cards)
            ? sprintf(
                '<div class="cartao flex flex-col items-center gap-2 text-center text-apagado"><b class="text-tinta">Nenhum %s encontrado%s.</b><span>Tente outra palavra ou veja todas as sugestões.</span><a href="/turismo?roteiro=%d&modo=%s" class="btn-secundario !h-9 !text-[13px]">Ver todas as sugestões</a></div>',
                $unidade,
                $termo !== '' ? ' para "' . self::e($termo) . '"' : '',
                $obRoteiro->getIdRoteiro(),
                $modo
            )
            : ViewRenderer::getLista($cards, self::$pathComponentesTurismo, $componente);

        return self::renderizarPagina('turismo/busca.php', [
            'abas'        => self::getAbasModo('/turismo?roteiro=' . $obRoteiro->getIdRoteiro() . ($diaPedido ? '&data=' . $diaPedido : ''), self::MODOS, $modo),
            'idRoteiro'   => $obRoteiro->getIdRoteiro(),
            'modo'        => $modo,
            'campoData'   => $diaPedido ? '<input type="hidden" name="data" value="' . $diaPedido . '">' : '',
            'termo'       => self::e($termo),
            'dicaBusca'   => $modo === 'guias' ? 'Busque por nome, especialidade, idioma ou bairro. Ex.: trilha, inglês' : 'Busque por passeio, tipo ou bairro. Ex.: mirante, museu, Urca',
            'iconeBusca'  => Icones::get('busca', 'h-5 w-5 flex-none text-apagado'),
            'rotulo'      => self::e(($termo !== '' ? 'Resultados em ' : 'Sugestões de ' . $unidade . 's em ') . $destino . $notaDia),
            'contagem'    => count($cards) . ' ' . $unidade . (count($cards) === 1 ? '' : 's'),
            'resultados'  => $resultados
        ], 'turismo', 'Turismo', [
            'cabecalhoPagina' => self::getCabecalhoPagina($obRoteiro, 'Turismo', 'Encontre guias locais e passeios no destino e monte a programação de cada dia.', self::MODOS[$modo]['icone'], self::MODOS[$modo]['cores']),
            'barraEtapas'     => self::getBarraEtapas($obRoteiro, 'Turismo')
        ]);
    }

    private static function getIdsNoRoteiro(Roteiro $obRoteiro, string $tipoServico): array
    {
        $itens = array_filter($obRoteiro->getItens(), fn(ItemRoteiro $obItem) => $obItem->getTipoServico() === $tipoServico);

        return array_map(fn(ItemRoteiro $obItem) => (string) $obItem->getIdServico(), $itens);
    }

    private static function getOpcoesDias(Roteiro $obRoteiro, string $dataSelecionada): string
    {
        $opcoes = [];
        foreach($obRoteiro->getDatasDaViagem() as $indice => $data){
            $opcoes[$data] = 'Dia ' . ($indice + 1) . ' · ' . Formatador::dataCurta($data);
        }

        return self::opcoesSelect($opcoes, $dataSelecionada);
    }

    private static function getCardsGuias(Roteiro $obRoteiro, string $termo, string $dataPadrao): array
    {
        $guias     = GuiaTuristico::pesquisar(self::getPrimeiraCidadeContexto(), $termo);
        $noRoteiro = self::getIdsNoRoteiro($obRoteiro, ItemRoteiro::TIPO_GUIA);
        $tabela    = new TabelaJson('guia', 'idGuia');

        $cards = array_map(function(GuiaTuristico $obGuia) use ($obRoteiro, $dataPadrao, $noRoteiro, $tabela){
            $apresentacao = $tabela->buscarPorId($obGuia->getIdGuia()) ?? [];
            $jaNoRoteiro  = in_array((string) $obGuia->getIdGuia(), $noRoteiro, true);
            $unidade      = $apresentacao['unidadeValor'] ?? 'diária';
            $nota         = isset($apresentacao['avaliacao']) ? str_replace('.', ',', (string) $apresentacao['avaliacao']) : '–';
            $campos       = ['tipoServico' => ItemRoteiro::TIPO_GUIA, 'idServico' => $obGuia->getIdGuia(), 'pessoas' => $obRoteiro->getQuantidadePessoas()];

            $foto = empty($apresentacao['foto'])
                ? self::e(Texto::iniciais($obGuia->getNome()))
                : '<img src="/public/img/' . self::e($apresentacao['foto']) . '" alt="Foto de ' . self::e($obGuia->getNome()) . '. Clique para ampliar" class="block h-full w-full cursor-zoom-in rounded-full object-cover" data-ampliar>';

            return [
                'nota'          => (float) ($apresentacao['avaliacao'] ?? 0),
                'cores'         => self::MODOS['guias']['cores'],
                'foto'          => $foto,
                'nome'          => self::e($obGuia->getNome()),
                'especialidade' => self::e($obGuia->getEspecialidade()),
                'avaliacao'     => $nota,
                'idiomas'       => self::getEtiquetas([$obGuia->getIdiomas()]),
                'valor'         => self::moeda($obGuia->getValorDiaria()),
                'unidade'       => self::e($unidade),
                'idPerfil'      => 'perfil-guia-' . $obGuia->getIdGuia(),
                'apresentacao'  => self::e($obGuia->getApresentacao()),
                'regioes'       => self::e($obGuia->getRegioes()),
                'idiomasTexto'  => self::e($obGuia->getIdiomas()),
                'contato'       => self::e(Formatador::telefone($obGuia->getTelefone()) . ' (WhatsApp)'),
                'iconeFechar'   => Icones::get('fechar', 'h-5 w-5'),
                'botao'         => self::getBotaoAdicionar($obRoteiro, $campos + ['data' => $dataPadrao], $jaNoRoteiro),
                'formPerfil'    => $jaNoRoteiro
                    ? '<button class="btn-secundario !cursor-default !border-transparent !bg-ok-suave !text-ok" disabled>No roteiro ✓</button>'
                    : '<form method="POST" action="/roteiros/itens/adicionar" class="flex flex-col gap-3.5">' . self::getCamposAdicionar($obRoteiro, $campos)
                        . '<div><label class="rotulo" for="dia-guia-' . $obGuia->getIdGuia() . '">Dia do passeio</label><select id="dia-guia-' . $obGuia->getIdGuia() . '" name="data" class="campo">' . self::getOpcoesDias($obRoteiro, $dataPadrao) . '</select></div>'
                        . '<button type="submit" class="btn-destaque">' . Icones::get('mais', 'h-[18px] w-[18px]') . 'Adicionar ao roteiro</button></form>'
            ];
        }, $guias);

        if($termo === ''){
            usort($cards, fn($a, $b) => $b['nota'] <=> $a['nota']);
        }

        return $cards;
    }

    private static function getCardsPasseios(Roteiro $obRoteiro, string $termo, string $dataPadrao): array
    {
        $passeios  = Passeio::pesquisar($termo, self::getPrimeiraCidadeContexto());
        $noRoteiro = self::getIdsNoRoteiro($obRoteiro, ItemRoteiro::TIPO_PASSEIO);
        $tabela    = new TabelaJson('passeio', 'idPasseio');
        $pessoas   = $obRoteiro->getQuantidadePessoas();

        return array_map(function(Passeio $obPasseio) use ($obRoteiro, $dataPadrao, $noRoteiro, $tabela, $pessoas){
            $apresentacao = $tabela->buscarPorId($obPasseio->getIdPasseio()) ?? [];
            $etiquetas    = empty($apresentacao['nivel'])
                ? self::getEtiquetas($apresentacao['etiquetas'] ?? [$obPasseio->getCategoria()])
                : self::getEtiquetas([$apresentacao['nivel'], 'Guia incluso'], [$apresentacao['nivel']]);

            return [
                'cores'     => self::MODOS['passeios']['cores'],
                'galeria'   => self::getGaleria($apresentacao['fotos'] ?? [], $obPasseio->getNomeAtracao()),
                'nome'      => self::e($obPasseio->getNomeAtracao()),
                'local'     => self::e($obPasseio->getLocalizacao()),
                'duracao'   => Formatador::duracao($obPasseio->getDuracaoMinutos()),
                'etiquetas' => $etiquetas,
                'total'     => self::moeda($obPasseio->getPrecoIngresso() * $pessoas),
                'porPessoa' => self::moeda($obPasseio->getPrecoIngresso()) . ' por pessoa · ' . $pessoas . ($pessoas === 1 ? ' pessoa' : ' pessoas'),
                'iconePin'  => Icones::get('pin', 'h-3.5 w-3.5'),
                'botao'     => self::getBotaoAdicionar($obRoteiro, [
                    'tipoServico' => ItemRoteiro::TIPO_PASSEIO,
                    'idServico'   => $obPasseio->getIdPasseio(),
                    'data'        => $dataPadrao,
                    'pessoas'     => $pessoas
                ], in_array((string) $obPasseio->getIdPasseio(), $noRoteiro, true))
            ];
        }, $passeios);
    }
}
