<?php

namespace Controllers;

use Models\Hospedagem;
use Models\ItemRoteiro;
use Models\Roteiro;
use Utils\Formatador;
use Utils\Validador;
use Views\Icones;
use Views\ViewRenderer;

/**
 * RF03: busca de hospedagens com filtros de cidade e período de estadia.
 */
class HospedagemController extends BuscaController
{
    private static $pathComponentesHospedagem = __DIR__ . '/../Views/hospedagem/components/';

    private const TIPOS  = ['' => 'Todos os tipos', 'Hotel' => 'Hotel', 'Pousada' => 'Pousada', 'Hostel' => 'Hostel'];
    private const ORDENS = ['avaliacao' => 'Melhor avaliação', 'preco' => 'Menor preço'];

    public static function getBusca(): string
    {
        $obUsuario = self::exigirAutenticacao();
        self::carregarRoteiros($obUsuario);

        $contexto = self::$roteiroContexto;
        $filtros  = [
            'cidade'     => trim($_GET['cidade'] ?? self::getPrimeiraCidadeContexto()),
            'dataInicio' => $_GET['dataInicio'] ?? ($contexto ? max($contexto->getDataInicio(), date('Y-m-d')) : ''),
            'dataFim'    => $_GET['dataFim'] ?? ($contexto ? $contexto->getDataFim() : ''),
            'pessoas'    => $_GET['pessoas'] ?? '2',
            'tipo'       => self::parametroPermitido('tipo', self::TIPOS, ''),
            'ordem'      => self::parametroPermitido('ordem', self::ORDENS, 'avaliacao')
        ];

        // Com um roteiro de contexto todos os filtros já são conhecidos: busca automaticamente
        $buscou = isset($_GET['cidade']) || ($contexto && $filtros['dataFim'] > $filtros['dataInicio']);

        $feedback   = '';
        $resultados = self::getEstadoInicialBusca(
            'Encontre onde ficar',
            'Informe a cidade e o período da estadia para ver hotéis, pousadas e hostels de parceiros.'
        );

        if($buscou){
            $erros = self::validarFiltros($filtros);

            if(!empty($erros)){
                $feedback   = self::getAlertaLista('erro', $erros);
                $resultados = '';
            }else{
                $resultados = self::getResultados($filtros);
            }
        }

        return self::renderizarPagina('hospedagem/busca.php', [
            'bannerContexto'  => self::getBannerContexto(),
            'campoContexto'   => self::getCampoRoteiroContexto(),
            'feedback'        => $feedback,
            'valorCidade'     => self::e($filtros['cidade']),
            'valorDataInicio' => self::e($filtros['dataInicio']),
            'valorDataFim'    => self::e($filtros['dataFim']),
            'valorPessoas'    => self::e($filtros['pessoas']),
            'dataMinima'      => date('Y-m-d'),
            'opcoesTipo'      => self::opcoesSelect(self::TIPOS, $filtros['tipo']),
            'opcoesOrdem'     => self::opcoesSelect(self::ORDENS, $filtros['ordem']),
            'datalistCidades' => self::getDatalistCidades(),
            'resultados'      => $resultados
        ], 'hospedagem', 'Hospedagem');
    }

    private static function validarFiltros(array $filtros): array
    {
        $erros = [];

        if($filtros['cidade'] === '') $erros[] = 'Informe a cidade da hospedagem.';

        $datasValidas = Validador::data($filtros['dataInicio']) && Validador::data($filtros['dataFim']);
        if(!$datasValidas){
            $erros[] = 'Informe as datas de entrada e de saída.';
        }elseif($filtros['dataInicio'] < date('Y-m-d')){
            $erros[] = 'A data de entrada não pode estar no passado.';
        }elseif($filtros['dataFim'] <= $filtros['dataInicio']){
            $erros[] = 'A data de saída deve ser posterior à data de entrada.';
        }elseif(Validador::diasEntre($filtros['dataInicio'], $filtros['dataFim']) > Roteiro::DURACAO_MAXIMA_DIAS){
            $erros[] = 'A estadia pode ter no máximo ' . Roteiro::DURACAO_MAXIMA_DIAS . ' diárias.';
        }

        if(!Validador::inteiroEntre($filtros['pessoas'], 1, ItemRoteiro::QUANTIDADE_MAXIMA_PESSOAS)){
            $erros[] = 'A quantidade de hóspedes deve ficar entre 1 e ' . ItemRoteiro::QUANTIDADE_MAXIMA_PESSOAS . '.';
        }

        return $erros;
    }

    private static function getResultados(array $filtros): string
    {
        $hospedagens = Hospedagem::pesquisarPorCidade($filtros['cidade'], $filtros['dataInicio'], $filtros['dataFim']);
        $diarias     = Validador::diasEntre($filtros['dataInicio'], $filtros['dataFim']);

        if($filtros['tipo'] !== ''){
            $hospedagens = array_values(array_filter($hospedagens, fn(Hospedagem $obHospedagem) => $obHospedagem->getTipo() === $filtros['tipo']));
        }

        $filtros['ordem'] === 'preco'
            ? usort($hospedagens, fn($a, $b) => $a->getPrecoDiaria() <=> $b->getPrecoDiaria())
            : usort($hospedagens, fn($a, $b) => $b->getAvaliacao() <=> $a->getAvaliacao());

        if(empty($hospedagens)){
            return self::getEstadoVazio('Nenhuma hospedagem encontrada', 'Não há opções dos nossos parceiros para essa cidade. Experimente um dos destinos sugeridos na lista.');
        }

        $cards = array_map(fn(Hospedagem $obHospedagem) => [
            'nome'          => self::e($obHospedagem->getNomeLocal()),
            'tipo'          => self::e($obHospedagem->getTipo()),
            'endereco'      => self::e($obHospedagem->getEndereco()),
            'cidade'        => self::e($obHospedagem->getCidade()),
            'avaliacao'     => $obHospedagem->getDescricaoAvaliacao(),
            'iconeEstrela'  => Icones::get('estrela', 'h-4 w-4 text-amber-400'),
            'iconePin'      => Icones::get('pin', 'h-4 w-4 shrink-0 text-slate-400'),
            'iconeCama'     => Icones::get('cama', 'h-8 w-8'),
            'precoDiaria'   => self::moeda($obHospedagem->getPrecoDiaria()),
            'resumoEstadia' => $diarias . ($diarias > 1 ? ' diárias' : ' diária') . ' · ' . Formatador::dataCurta($filtros['dataInicio']) . ' a ' . Formatador::dataCurta($filtros['dataFim']),
            'subtotal'      => self::moeda($obHospedagem->calcularSubtotalEstadia($diarias)),
            'formAdicionar' => self::getFormAdicionar([
                'tipoServico' => ItemRoteiro::TIPO_HOSPEDAGEM,
                'idServico'   => $obHospedagem->getIdHospedagem(),
                'dataInicio'  => $filtros['dataInicio'],
                'dataFim'     => $filtros['dataFim'],
                'pessoas'     => (int) $filtros['pessoas']
            ])
        ], $hospedagens);

        $titulo = sprintf(
            '<p class="mb-4 text-sm text-slate-500"><strong class="text-blue-950">%d opções</strong> em %s · %s a %s · %d hóspede(s)</p>',
            count($hospedagens),
            self::e($filtros['cidade']),
            Formatador::data($filtros['dataInicio']),
            Formatador::data($filtros['dataFim']),
            (int) $filtros['pessoas']
        );

        return $titulo . '<div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">' . ViewRenderer::getLista($cards, self::$pathComponentesHospedagem, 'card.php') . '</div>';
    }
}
