<?php

namespace Controllers;

use Models\Destino;
use Models\GuiaTuristico;
use Models\ItemRoteiro;
use Models\Passeio;
use Utils\Formatador;
use Utils\Texto;
use Utils\Validador;
use Views\Icones;
use Views\ViewRenderer;

/**
 * RF04: pesquisa de guias turísticos por localidade, e atrações (passeios) do destino.
 */
class TurismoController extends BuscaController
{
    private static $pathComponentesTurismo = __DIR__ . '/../Views/turismo/components/';

    public static function getBusca(): string
    {
        $obUsuario = self::exigirAutenticacao();
        self::carregarRoteiros($obUsuario);

        $contexto = self::$roteiroContexto;
        $filtros  = [
            'cidade'        => trim($_GET['cidade'] ?? self::getPrimeiraCidadeContexto()),
            'especialidade' => trim($_GET['especialidade'] ?? ''),
            'data'          => $_GET['data'] ?? ($contexto ? max($contexto->getDataInicio(), date('Y-m-d')) : ''),
            'pessoas'       => $_GET['pessoas'] ?? '2'
        ];

        $buscou = isset($_GET['cidade']) || $contexto;

        $feedback = '';
        $guias    = '';
        $passeios = '';
        $inicial  = self::getEstadoInicialBusca('Descubra guias e passeios', 'Informe a cidade para ver os guias turísticos cadastrados e as atrações do destino.');

        if($buscou){
            $erros = self::validarFiltros($filtros);

            if(!empty($erros)){
                $feedback = self::getAlertaLista('erro', $erros);
            }else{
                $guias    = self::getSecao('guias', 'Guias turísticos', 'contatos cadastrados no Mundear', self::getSecaoGuias($filtros));
                $passeios = self::getSecao('passeios', 'Passeios e atrações', 'ingressos e pontos turísticos do destino', self::getSecaoPasseios($filtros));
            }

            $inicial = '';
        }

        return self::renderizarPagina('turismo/busca.php', [
            'bannerContexto'     => self::getBannerContexto(),
            'campoContexto'      => self::getCampoRoteiroContexto(),
            'feedback'           => $feedback,
            'valorCidade'        => self::e($filtros['cidade']),
            'valorEspecialidade' => self::e($filtros['especialidade']),
            'valorData'          => self::e($filtros['data']),
            'valorPessoas'       => self::e($filtros['pessoas']),
            'dataMinima'         => date('Y-m-d'),
            'datalistCidades'    => self::getDatalistCidades(),
            'estadoInicial'      => $inicial,
            'guias'              => $guias,
            'passeios'           => $passeios
        ], 'turismo', 'Turismo');
    }

    private static function validarFiltros(array $filtros): array
    {
        $erros = [];

        if($filtros['cidade'] === '') $erros[] = 'Informe a cidade para pesquisar.';

        if($filtros['data'] !== ''){
            if(!Validador::data($filtros['data']))      $erros[] = 'Informe uma data válida.';
            elseif($filtros['data'] < date('Y-m-d'))    $erros[] = 'A data não pode estar no passado.';
        }

        if(!Validador::inteiroEntre($filtros['pessoas'], 1, ItemRoteiro::QUANTIDADE_MAXIMA_PESSOAS)){
            $erros[] = 'A quantidade de pessoas deve ficar entre 1 e ' . ItemRoteiro::QUANTIDADE_MAXIMA_PESSOAS . '.';
        }

        return $erros;
    }

    private static function getSecaoGuias(array $filtros): string
    {
        $guias = GuiaTuristico::pesquisar($filtros['cidade'], $filtros['especialidade']);

        if(empty($guias)){
            return self::getEstadoVazio('Nenhum guia encontrado', 'Não há guias ativos para essa localidade' . ($filtros['especialidade'] !== '' ? ' com essa especialidade' : '') . '.');
        }

        $temData = $filtros['data'] !== '';
        $cards   = array_map(function(GuiaTuristico $obGuia) use ($filtros, $temData){
            $motivo = null;
            if(!$temData){
                $motivo = 'Escolha uma data na busca para adicionar';
            }elseif(!$obGuia->consultarDisponibilidade($filtros['data'])){
                $motivo = 'Indisponível em ' . Formatador::data($filtros['data']) . ' (folga)';
            }

            return [
                'iniciais'       => self::e(Texto::iniciais($obGuia->getNome())),
                'nome'           => self::e($obGuia->getNome()),
                'especialidade'  => self::e($obGuia->getEspecialidade()),
                'cidade'         => self::e($obGuia->getCidadeAtuacao()),
                'telefone'       => self::e(Formatador::telefone($obGuia->getTelefone())),
                'telefoneLink'   => self::e($obGuia->getTelefone()),
                'folga'          => self::e($obGuia->getDescricaoFolga()),
                'valorDiaria'    => self::moeda($obGuia->getValorDiaria()),
                'disponibilidade'=> $temData ? self::getBadgeDisponibilidade($obGuia->consultarDisponibilidade($filtros['data']), $filtros['data']) : '',
                'iconePin'       => Icones::get('pin', 'h-4 w-4 shrink-0 text-slate-400'),
                'iconeTelefone'  => Icones::get('telefone', 'h-4 w-4 shrink-0 text-slate-400'),
                'iconeCalendario'=> Icones::get('calendario', 'h-4 w-4 shrink-0 text-slate-400'),
                'formAdicionar'  => self::getFormAdicionar([
                    'tipoServico' => ItemRoteiro::TIPO_GUIA,
                    'idServico'   => $obGuia->getIdGuia(),
                    'data'        => $filtros['data'],
                    'pessoas'     => (int) $filtros['pessoas']
                ], $motivo)
            ];
        }, $guias);

        return '<div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">' . ViewRenderer::getLista($cards, self::$pathComponentesTurismo, 'card-guia.php') . '</div>';
    }

    private static function getSecaoPasseios(array $filtros): string
    {
        $passeios = [];
        foreach(Destino::listar() as $obDestino){
            if(Texto::contem($obDestino->getCidade(), $filtros['cidade'])){
                foreach($obDestino->listarAtracoes() as $obPasseio){
                    $passeios[] = [$obDestino, $obPasseio];
                }
            }
        }

        if(empty($passeios)){
            return self::getEstadoVazio('Nenhum passeio encontrado', 'Ainda não temos atrações cadastradas para essa cidade.');
        }

        $temData = $filtros['data'] !== '';
        $pessoas = (int) $filtros['pessoas'];

        $cards = array_map(function(array $par) use ($filtros, $temData, $pessoas){
            /** @var Destino $obDestino */
            /** @var Passeio $obPasseio */
            [$obDestino, $obPasseio] = $par;

            $motivo = null;
            if(!$temData){
                $motivo = 'Escolha uma data na busca para adicionar';
            }elseif(!$obPasseio->verificarDisponibilidade($filtros['data'])){
                $motivo = 'Fechado em ' . Formatador::data($filtros['data']);
            }

            $gratuito = $obPasseio->getPrecoIngresso() <= 0;

            return [
                'nome'            => self::e($obPasseio->getNomeAtracao()),
                'cidade'          => self::e($obDestino->getCidade() . ' - ' . $obDestino->getEstado()),
                'localizacao'     => self::e($obPasseio->getLocalizacao()),
                'duracao'         => Formatador::duracao($obPasseio->getDuracaoMinutos()),
                'funcionamento'   => self::e($obPasseio->getDescricaoFuncionamento()),
                'preco'           => $gratuito ? 'Gratuito' : self::moeda($obPasseio->getPrecoIngresso()),
                'detalhePreco'    => $gratuito ? 'entrada livre' : ($pessoas > 1 ? 'Total p/ ' . $pessoas . ': ' . self::moeda($obPasseio->getPrecoIngresso() * $pessoas) : 'por pessoa'),
                'disponibilidade' => $temData ? self::getBadgeDisponibilidade($obPasseio->verificarDisponibilidade($filtros['data']), $filtros['data'], 'Aberto', 'Fechado') : '',
                'iconeIngresso'   => Icones::get('ingresso', 'h-6 w-6'),
                'iconePin'        => Icones::get('pin', 'h-4 w-4 shrink-0 text-slate-400'),
                'iconeRelogio'    => Icones::get('relogio', 'h-4 w-4 shrink-0 text-slate-400'),
                'formAdicionar'   => self::getFormAdicionar([
                    'tipoServico' => ItemRoteiro::TIPO_PASSEIO,
                    'idServico'   => $obPasseio->getIdPasseio(),
                    'data'        => $filtros['data'],
                    'pessoas'     => $pessoas
                ], $motivo)
            ];
        }, $passeios);

        return '<div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">' . ViewRenderer::getLista($cards, self::$pathComponentesTurismo, 'card-passeio.php') . '</div>';
    }

    private static function getSecao(string $id, string $titulo, string $subtitulo, string $conteudo): string
    {
        return sprintf(
            '<section id="%s" class="mt-8 scroll-mt-24"><div class="mb-4 flex flex-wrap items-baseline gap-x-2"><h2 class="text-xl font-bold text-blue-950">%s</h2><span class="text-sm text-slate-500">· %s</span></div>%s</section>',
            $id,
            $titulo,
            $subtitulo,
            $conteudo
        );
    }

    private static function getBadgeDisponibilidade(bool $disponivel, string $data, string $textoSim = 'Disponível', string $textoNao = 'Indisponível'): string
    {
        $texto = ($disponivel ? $textoSim : $textoNao) . ' em ' . Formatador::dataCurta($data);

        return $disponivel
            ? '<span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">' . $texto . '</span>'
            : '<span class="inline-flex rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-red-200">' . $texto . '</span>';
    }
}
