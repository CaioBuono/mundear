<?php

namespace Controllers;

use Models\Hospedagem;
use Models\ItemRoteiro;
use Models\OfertaHospedagem;
use Models\Roteiro;
use Services\ApiHospedagemSimulada;
use Utils\Formatador;
use Utils\Validador;
use Views\Icones;
use Views\ViewRenderer;

class HospedagemController extends BuscaController
{
    private static $pathComponentesHospedagem = __DIR__ . '/../Views/hospedagem/components/';

    private const MODOS = [
        'hotel'     => ['rotulo' => 'Hotel', 'legenda' => 'quarto com serviço', 'tipos' => ['Hotel'], 'icone' => 'cama', 'cores' => '--mc:#2563c9;--ms:#e8f0fd;--mb:#bcd2f6'],
        'hostel'    => ['rotulo' => 'Hostel', 'legenda' => 'econômico', 'tipos' => ['Hostel'], 'icone' => 'pessoas', 'cores' => '--mc:#d9580f;--ms:#fdeee4;--mb:#f6caa9'],
        'fazenda'   => ['rotulo' => 'Hotel fazenda', 'legenda' => 'resort', 'tipos' => ['Hotel fazenda'], 'icone' => 'arvore', 'cores' => '--mc:#1f8a5b;--ms:#e3f4ec;--mb:#b4e0c9'],
        'temporada' => ['rotulo' => 'Pousada e apartamento', 'legenda' => 'temporada', 'tipos' => ['Pousada', 'Apartamento'], 'icone' => 'chave', 'cores' => '--mc:#7046c9;--ms:#efe9fb;--mb:#d3c4f3']
    ];

    public static function getBusca(): string
    {
        $obUsuario = self::exigirAutenticacao();
        $obRoteiro = self::exigirRoteiroContexto($obUsuario);
        $modo      = self::parametroPermitido('modo', self::MODOS, 'hotel');
        $noites    = max(1, Validador::diasEntre($obRoteiro->getDataInicio(), $obRoteiro->getDataFim()));
        $textoNoites = $noites . ($noites === 1 ? ' noite' : ' noites');

        $hospedagens = array_values(array_filter(
            Hospedagem::pesquisarPorCidade(self::getPrimeiraCidadeContexto(), $obRoteiro->getDataInicio(), $obRoteiro->getDataFim()),
            fn(Hospedagem $obHospedagem) => in_array($obHospedagem->getTipo(), self::MODOS[$modo]['tipos'], true)
        ));

        $cards = array_map(fn(Hospedagem $obHospedagem) => self::getDadosCard($obRoteiro, $obHospedagem, $modo, $noites, $textoNoites), $hospedagens);

        $resultados = empty($cards)
            ? '<div class="cartao text-center text-apagado">Ainda não temos hospedagens desse tipo em ' . self::e(self::getNomeDestino($obRoteiro)) . '. Tente outro tipo de hospedagem.</div>'
            : ViewRenderer::getLista($cards, self::$pathComponentesHospedagem, 'card.php');

        return self::renderizarPagina('hospedagem/busca.php', [
            'abas'       => self::getAbasModo('/hospedagem?roteiro=' . $obRoteiro->getIdRoteiro(), self::MODOS, $modo),
            'rotulo'     => 'Ordenado por menor preço · valores para ' . $textoNoites,
            'contagem'   => count($cards) . (count($cards) === 1 ? ' opção' : ' opções'),
            'resultados' => $resultados
        ], 'hospedagem', 'Hospedagem', [
            'cabecalhoPagina' => self::getCabecalhoPagina($obRoteiro, 'Hospedagem', 'Compare o mesmo lugar em vários sites e escolha o menor preço.', self::MODOS[$modo]['icone'], self::MODOS[$modo]['cores']),
            'barraEtapas'     => self::getBarraEtapas($obRoteiro, 'Hospedagem')
        ]);
    }

    private static function getSiteNoRoteiro(Roteiro $obRoteiro, Hospedagem $obHospedagem): ?string
    {
        foreach($obRoteiro->getItens() as $obItem){
            if($obItem->getTipoServico() === ItemRoteiro::TIPO_HOSPEDAGEM and (string) $obItem->getIdServico() === (string) $obHospedagem->getIdHospedagem()){
                $partes = explode(' · via ', $obItem->getDetalhes());
                return end($partes);
            }
        }

        return null;
    }

    private static function getDadosCard(Roteiro $obRoteiro, Hospedagem $obHospedagem, string $modo, int $noites, string $textoNoites): array
    {
        $apresentacao = ApiHospedagemSimulada::buscarPorId($obHospedagem->getIdHospedagem()) ?? [];
        $ofertas      = $obHospedagem->compararPrecos();
        $obMenor      = $obHospedagem->obterMenorPreco();
        $siteEscolhido = self::getSiteNoRoteiro($obRoteiro, $obHospedagem);

        $camposAdicionar = fn(OfertaHospedagem $obOferta) => [
            'tipoServico' => ItemRoteiro::TIPO_HOSPEDAGEM,
            'idServico'   => $obHospedagem->getIdHospedagem(),
            'dataInicio'  => $obRoteiro->getDataInicio(),
            'dataFim'     => $obRoteiro->getDataFim(),
            'pessoas'     => $obRoteiro->getQuantidadePessoas(),
            'site'        => $obOferta->getNomeSite()
        ];

        $linhas = '';
        foreach($ofertas as $indice => $obOferta){
            $melhor    = $indice === 0;
            $diferenca = ($obOferta->getPrecoDiaria() - $obMenor->getPrecoDiaria()) * $noites;
            $acao      = $siteEscolhido === $obOferta->getNomeSite()
                ? '<button class="rounded-lg bg-ok-suave px-3 py-[7px] text-[13px] font-bold text-ok" disabled>No roteiro ✓</button>'
                : '<form method="POST" action="/roteiros/itens/adicionar">' . self::getCamposAdicionar($obRoteiro, $camposAdicionar($obOferta)) . '<button type="submit" class="rounded-lg bg-laranja px-3 py-[7px] text-[13px] font-bold text-white hover:bg-laranja-escuro">Escolher</button></form>';

            $linhas .= sprintf(
                '<tr class="%s"><td class="border-t border-linha px-2 py-2.5 %s"><span class="font-bold text-navy">%s</span> %s</td><td class="border-t border-linha px-2 py-2.5 text-right">%s</td><td class="border-t border-linha px-2 py-2.5 text-right"><b>%s</b></td><td class="border-t border-linha px-2 py-2.5 text-right">%s</td><td class="border-t border-linha px-2 py-2.5 %s"><div class="flex justify-end gap-2"><a href="%s" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-lg border border-linha bg-white px-3 py-[7px] text-[13px] font-bold text-navy hover:border-navy">%sVer no site</a>%s</div></td></tr>',
                $melhor ? 'bg-ok-suave' : '',
                $melhor ? 'rounded-l-md' : '',
                self::e($obOferta->getNomeSite()),
                $melhor ? '<span class="ml-1 rounded-full bg-white px-2 py-0.5 text-[11.5px] font-extrabold text-ok">Menor preço</span>' : '',
                self::moeda($obOferta->getPrecoDiaria()),
                self::moeda($obOferta->getPrecoDiaria() * $noites),
                $melhor ? '–' : '+ ' . self::moeda($diferenca),
                $melhor ? 'rounded-r-md' : '',
                self::e($obOferta->getLinkReserva()),
                Icones::get('externo', 'h-3.5 w-3.5'),
                $acao
            );
        }

        $rotuloBotao = $siteEscolhido !== null ? 'Trocar pelo menor preço' : 'Adicionar ao roteiro';

        return [
            'cores'            => self::MODOS[$modo]['cores'],
            'galeria'          => self::getGaleria($apresentacao['fotos'] ?? [], $obHospedagem->getNomeLocal()),
            'nome'             => self::e($obHospedagem->getNomeLocal()),
            'local'            => self::e($obHospedagem->getEndereco()),
            'nota'             => str_replace('.', ',', (string) $obHospedagem->getAvaliacao()),
            'quarto'           => self::e($apresentacao['quarto'] ?? $obHospedagem->getTipo()),
            'comodidades'      => self::getEtiquetas($apresentacao['comodidades'] ?? [], ['Resort']),
            'siteMenor'        => self::e($obMenor->getNomeSite()),
            'total'            => self::moeda($obHospedagem->calcularSubtotalEstadia($noites)),
            'porNoite'         => self::moeda($obMenor->getPrecoDiaria()) . ' por noite · ' . $textoNoites,
            'idComparar'       => 'comparar-' . $obHospedagem->getIdHospedagem(),
            'quantidadeSites'  => count($ofertas),
            'textoNoites'      => $textoNoites,
            'linhasComparacao' => $linhas,
            'iconePin'         => Icones::get('pin', 'h-3.5 w-3.5'),
            'iconeBaixo'       => Icones::get('baixo', 'h-4 w-4 transition group-aria-expanded:rotate-180'),
            'botao'            => self::getBotaoAdicionar($obRoteiro, $camposAdicionar($obMenor), $siteEscolhido === $obMenor->getNomeSite(), $rotuloBotao)
        ];
    }
}
