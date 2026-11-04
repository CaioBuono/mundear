<?php

namespace Controllers;

use Models\ItemRoteiro;
use Models\Transporte;
use Utils\Formatador;
use Utils\Texto;
use Utils\Validador;
use Views\Icones;
use Views\ViewRenderer;

/**
 * RF02: busca de voos e ônibus com filtros de data, origem e destino.
 */
class TransporteController extends BuscaController
{
    private static $pathComponentesTransporte = __DIR__ . '/../Views/transporte/components/';

    private const TIPOS  = ['todos' => 'Voos e ônibus', 'voo' => 'Somente voos', 'onibus' => 'Somente ônibus'];
    private const ORDENS = ['horario' => 'Horário de partida', 'preco' => 'Menor preço', 'duracao' => 'Menor duração'];

    public static function getBusca(): string
    {
        $obUsuario = self::exigirAutenticacao();
        self::carregarRoteiros($obUsuario);

        $contexto = self::$roteiroContexto;
        $filtros  = [
            'origem'  => trim($_GET['origem'] ?? ''),
            'destino' => trim($_GET['destino'] ?? self::getPrimeiraCidadeContexto()),
            'data'    => $_GET['data'] ?? ($contexto ? max($contexto->getDataInicio(), date('Y-m-d')) : ''),
            'tipo'    => self::parametroPermitido('tipo', self::TIPOS, 'todos'),
            'ordem'   => self::parametroPermitido('ordem', self::ORDENS, 'horario'),
            'pessoas' => $_GET['pessoas'] ?? '1'
        ];

        $feedback   = '';
        $resultados = self::getEstadoInicialBusca(
            'Pesquise passagens',
            'Informe origem, destino e data. Os resultados simulam a consulta a companhias aéreas e rodoviárias parceiras.'
        );

        if(isset($_GET['origem'])){
            $erros = self::validarFiltros($filtros);

            if(!empty($erros)){
                $feedback   = self::getAlertaLista('erro', $erros);
                $resultados = '';
            }else{
                $resultados = self::getResultados($filtros);
            }
        }

        return self::renderizarPagina('transporte/busca.php', [
            'bannerContexto'  => self::getBannerContexto(),
            'campoContexto'   => self::getCampoRoteiroContexto(),
            'feedback'        => $feedback,
            'valorOrigem'     => self::e($filtros['origem']),
            'valorDestino'    => self::e($filtros['destino']),
            'valorData'       => self::e($filtros['data']),
            'valorPessoas'    => self::e($filtros['pessoas']),
            'dataMinima'      => date('Y-m-d'),
            'opcoesTipo'      => self::opcoesSelect(self::TIPOS, $filtros['tipo']),
            'opcoesOrdem'     => self::opcoesSelect(self::ORDENS, $filtros['ordem']),
            'datalistCidades' => self::getDatalistCidades(),
            'resultados'      => $resultados
        ], 'transporte', 'Transporte');
    }

    private static function validarFiltros(array $filtros): array
    {
        $erros = [];

        if($filtros['origem'] === '')  $erros[] = 'Informe a cidade de origem.';
        if($filtros['destino'] === '') $erros[] = 'Informe a cidade de destino.';

        if($filtros['origem'] !== '' and Texto::iguais($filtros['origem'], $filtros['destino'])){
            $erros[] = 'A origem e o destino precisam ser cidades diferentes.';
        }

        if(!Validador::data($filtros['data'])){
            $erros[] = 'Informe uma data de viagem válida.';
        }elseif($filtros['data'] < date('Y-m-d')){
            $erros[] = 'A data da viagem não pode estar no passado.';
        }

        if(!Validador::inteiroEntre($filtros['pessoas'], 1, ItemRoteiro::QUANTIDADE_MAXIMA_PESSOAS)){
            $erros[] = 'A quantidade de passageiros deve ficar entre 1 e ' . ItemRoteiro::QUANTIDADE_MAXIMA_PESSOAS . '.';
        }

        return $erros;
    }

    private static function getResultados(array $filtros): string
    {
        $opcoes  = Transporte::pesquisar($filtros['origem'], $filtros['destino'], $filtros['data'], $filtros['tipo']);
        $pessoas = (int) $filtros['pessoas'];

        if($filtros['ordem'] === 'preco')   usort($opcoes, fn($a, $b) => $a->getPreco() <=> $b->getPreco());
        if($filtros['ordem'] === 'duracao') usort($opcoes, fn($a, $b) => $a->getDuracaoMinutos() <=> $b->getDuracaoMinutos());

        if(empty($opcoes)){
            return self::getEstadoVazio('Nenhuma opção encontrada', 'Não encontramos opções para esse trecho. Tente outra data ou outro tipo de transporte.');
        }

        $cards = array_map(function(Transporte $obTransporte) use ($filtros, $pessoas){
            $disponivel = $obTransporte->verificarDisponibilidade($obTransporte->getDataHoraPartida(), $pessoas);
            $assentos   = $obTransporte->getAssentosDisponiveis();

            $motivo = null;
            if(!$disponivel){
                $motivo = $assentos === 0 ? 'Esgotado' : ($assentos < $pessoas ? 'Assentos insuficientes' : 'Partida já realizada');
            }

            $diasDepois = Validador::diasEntre(substr($obTransporte->getDataHoraPartida(), 0, 10), substr($obTransporte->getDataHoraChegada(), 0, 10));

            return [
                'icone'        => Icones::get($obTransporte->isVoo() ? 'aviao' : 'onibus', 'h-6 w-6'),
                'corIcone'     => $obTransporte->isVoo() ? 'bg-blue-50 text-blue-900' : 'bg-amber-50 text-amber-700',
                'tipo'         => $obTransporte->getRotuloTipo(),
                'empresa'      => self::e($obTransporte->getEmpresa()),
                'horaPartida'  => Formatador::hora($obTransporte->getDataHoraPartida()),
                'horaChegada'  => Formatador::hora($obTransporte->getDataHoraChegada()),
                'diasDepois'   => $diasDepois > 0 ? '<sup class="ml-0.5 text-xs font-semibold text-orange-600">+' . $diasDepois . '</sup>' : '',
                'origem'       => self::e($obTransporte->getLocalOrigem()),
                'destino'      => self::e($obTransporte->getLocalDestino()),
                'duracao'      => Formatador::duracao($obTransporte->getDuracaoMinutos()),
                'assentos'     => $assentos === 0
                    ? '<span class="text-red-600">Sem assentos</span>'
                    : ($assentos <= 5 ? '<span class="text-orange-600">Últimos ' . $assentos . ' assentos</span>' : $assentos . ' assentos disponíveis'),
                'preco'        => self::moeda($obTransporte->getPreco()),
                'total'        => $pessoas > 1 ? 'Total para ' . $pessoas . ' pessoas: ' . self::moeda($obTransporte->getPreco() * $pessoas) : 'por pessoa',
                'codigo'       => self::e($obTransporte->getIdTransporte()),
                'formAdicionar'=> self::getFormAdicionar([
                    'tipoServico' => ItemRoteiro::TIPO_TRANSPORTE,
                    'idServico'   => $obTransporte->getIdTransporte(),
                    'origem'      => $filtros['origem'],
                    'destino'     => $filtros['destino'],
                    'data'        => $filtros['data'],
                    'pessoas'     => $pessoas
                ], $motivo)
            ];
        }, $opcoes);

        $titulo = sprintf(
            '<p class="mb-4 text-sm text-slate-500"><strong class="text-blue-950">%d opções</strong> de %s para %s em %s · %s</p>',
            count($opcoes),
            self::e($filtros['origem']),
            self::e($filtros['destino']),
            Formatador::data($filtros['data']),
            strtolower(Formatador::diaSemana($filtros['data']))
        );

        return $titulo . '<div class="space-y-4">' . ViewRenderer::getLista($cards, self::$pathComponentesTransporte, 'card.php') . '</div>';
    }
}
