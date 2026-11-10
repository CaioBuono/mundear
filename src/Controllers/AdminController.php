<?php

namespace Controllers;

use Exceptions\RegraNegocioException;
use Exceptions\RegistroNaoEncontradoException;
use Models\Destino;
use Models\GuiaTuristico;
use Models\ItemRoteiro;
use Models\Usuario;
use Session\Session;
use Utils\Formatador;
use Utils\Texto;
use Views\ViewRenderer;

class AdminController extends Controller
{
    private static $pathComponentesAdmin = __DIR__ . '/../Views/admin/components/';

    private const STATUS_FILTRO = ['' => 'Todos', 'ativos' => 'Somente ativos', 'inativos' => 'Somente inativos'];

    public static function getRelatorio(): string
    {
        $obAdmin = self::exigirAdministrador();
        $dados   = $obAdmin->obterDadosRelatorio();

        $maiorDestino = max([1, ...array_values($dados['destinosPopulares'])]);
        $destinos     = '';
        foreach($dados['destinosPopulares'] as $cidade => $quantidade){
            $destinos .= sprintf(
                '<li><div class="flex justify-between text-sm"><span class="font-medium text-blue-950">%s</span><span class="text-slate-500">%d roteiro(s)</span></div><div class="mt-1.5 h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-orange-500" style="width:%d%%"></div></div></li>',
                self::e($cidade),
                $quantidade,
                (int) round($quantidade / $maiorDestino * 100)
            );
        }

        $rotulos = ItemRoteiro::getRotulosTipo();
        $itens   = '';
        foreach($dados['itensPorTipo'] as $tipo => $quantidade){
            $itens .= sprintf('<div class="flex justify-between py-2"><dt class="text-slate-600">%s</dt><dd class="font-semibold text-blue-950">%d</dd></div>', self::e($rotulos[$tipo]), $quantidade);
        }

        return self::renderizarPagina('admin/relatorio.php', [
            'abas'                => self::getAbas('relatorio'),
            'nomeAdmin'           => self::e(Texto::primeiroNome($obAdmin->getNome())),
            'credencial'          => self::e($obAdmin->getCodigoCredencial() . ' · nível ' . $obAdmin->getNivelAcesso()),
            'usuariosTotal'       => $dados['usuariosTotal'],
            'usuariosInativos'    => $dados['usuariosTotal'] - $dados['usuariosAtivos'],
            'roteirosTotal'       => $dados['roteirosTotal'],
            'roteirosFinalizados' => $dados['roteirosFinalizados'],
            'linksAtivos'         => $dados['linksAtivos'],
            'valorPlanejado'      => self::moeda($dados['valorPlanejado']),
            'guiasAtivos'         => $dados['guiasAtivos'],
            'guiasInativos'       => $dados['guiasTotal'] - $dados['guiasAtivos'],
            'destinosPopulares'   => $destinos ?: '<li class="text-sm text-slate-400">Nenhum roteiro criado ainda.</li>',
            'itensPorTipo'        => $itens,
            'resumo'              => self::e($obAdmin->gerarRelatorio())
        ], 'admin', 'Administração');
    }

    public static function getGuias(): string
    {
        self::exigirAdministrador();

        $cidade = trim($_GET['cidade'] ?? '');
        $status = self::parametroPermitido('status', self::STATUS_FILTRO, '');
        $ativo  = match($status){ 'ativos' => true, 'inativos' => false, default => null };
        $guias  = GuiaTuristico::pesquisar($cidade, '', $ativo);

        $linhas = array_map(fn(GuiaTuristico $obGuia) => [
            'id'            => $obGuia->getIdGuia(),
            'nome'          => self::e($obGuia->getNome()),
            'especialidade' => self::e($obGuia->getEspecialidade()),
            'cidade'        => self::e($obGuia->getCidadeAtuacao()),
            'telefone'      => self::e(Formatador::telefone($obGuia->getTelefone())),
            'valorDiaria'   => self::moeda($obGuia->getValorDiaria()),
            'status'        => self::getBadgeStatus($obGuia->isAtivo()),
            'novoStatus'    => $obGuia->isAtivo() ? '0' : '1',
            'textoStatus'   => $obGuia->isAtivo() ? 'Inativar' : 'Reativar',
            'classeStatus'  => $obGuia->isAtivo() ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50',
            'confirmacao'   => $obGuia->isAtivo() ? 'Inativar este guia? Ele deixará de aparecer nas buscas dos viajantes.' : 'Reativar este guia?'
        ], $guias);

        $cidades = ['' => 'Todas as cidades'];
        foreach(Destino::listar() as $obDestino){
            $cidades[$obDestino->getCidade()] = $obDestino->getCidade();
        }

        return self::renderizarPagina('admin/guias.php', [
            'abas'          => self::getAbas('guias'),
            'opcoesCidade'  => self::opcoesSelect($cidades, $cidade),
            'opcoesStatus'  => self::opcoesSelect(self::STATUS_FILTRO, $status),
            'quantidade'    => count($guias) === 1 ? '1 guia encontrado' : count($guias) . ' guias encontrados',
            'linhas'        => empty($linhas)
                ? '<tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Nenhum guia encontrado com esses filtros.</td></tr>'
                : ViewRenderer::getLista($linhas, self::$pathComponentesAdmin, 'linha-guia.php')
        ], 'admin', 'Guias turísticos');
    }

    public static function getNovoGuia(): string
    {
        self::exigirAdministrador();

        return self::renderizarFormularioGuia(null, ['ativo' => '1'], []);
    }

    public static function postNovoGuia(): string
    {
        $obAdmin = self::exigirAdministrador();

        $erros = GuiaTuristico::validarDados($_POST);
        if(!empty($erros)){
            return self::renderizarFormularioGuia(null, $_POST, $erros);
        }

        $obGuia = new GuiaTuristico();
        $obGuia->preencherDados($_POST);
        $obAdmin->cadastrarNovoGuia($obGuia);

        Session::setFlash('sucesso', 'Guia ' . $obGuia->getNome() . ' cadastrado com sucesso.');
        self::redirecionar('/admin/guias');
    }

    public static function getEditarGuia(): string
    {
        self::exigirAdministrador();
        $obGuia = self::getGuiaOuErro($_GET['id'] ?? 0);

        return self::renderizarFormularioGuia($obGuia, [
            'nome'          => $obGuia->getNome(),
            'telefone'      => Formatador::telefone($obGuia->getTelefone()),
            'especialidade' => $obGuia->getEspecialidade(),
            'cidadeAtuacao' => $obGuia->getCidadeAtuacao(),
            'valorDiaria'   => number_format($obGuia->getValorDiaria(), 2, ',', ''),
            'diaFolga'      => $obGuia->getDiaFolga() ?? '',
            'ativo'         => $obGuia->isAtivo() ? '1' : ''
        ], []);
    }

    public static function postEditarGuia(): string
    {
        $obAdmin = self::exigirAdministrador();
        $obGuia  = self::getGuiaOuErro($_POST['idGuia'] ?? 0);

        $erros = GuiaTuristico::validarDados($_POST);
        if(!empty($erros)){
            return self::renderizarFormularioGuia($obGuia, $_POST, $erros);
        }

        $obGuia->preencherDados($_POST);
        $obAdmin->alterarGuia($obGuia);

        Session::setFlash('sucesso', 'Dados de ' . $obGuia->getNome() . ' atualizados.');
        self::redirecionar('/admin/guias');
    }

    public static function postStatusGuia(): void
    {
        $obAdmin = self::exigirAdministrador();
        $obGuia  = self::getGuiaOuErro($_POST['idGuia'] ?? 0);

        if(($_POST['ativo'] ?? '') === '1'){
            $obAdmin->reativarGuia($obGuia->getIdGuia());
            Session::setFlash('sucesso', $obGuia->getNome() . ' foi reativado(a) e volta a aparecer nas buscas.');
        }else{
            $obAdmin->inativarGuia($obGuia->getIdGuia());
            Session::setFlash('sucesso', $obGuia->getNome() . ' foi inativado(a) e não aparece mais nas buscas.');
        }

        self::redirecionar('/admin/guias');
    }

    public static function postExcluirGuia(): void
    {
        $obAdmin = self::exigirAdministrador();
        $obGuia  = self::getGuiaOuErro($_POST['idGuia'] ?? 0);

        try{
            $obAdmin->excluirGuia($obGuia->getIdGuia());
            Session::setFlash('sucesso', $obGuia->getNome() . ' foi excluído(a) definitivamente.');
        }catch(RegraNegocioException $e){
            Session::setFlash('erro', $e->getMessage());
        }

        self::redirecionar('/admin/guias');
    }

    private static function renderizarFormularioGuia(?GuiaTuristico $obGuia, array $valores, array $erros): string
    {
        $cidades = ['' => 'Selecione a cidade'];
        foreach(Destino::listar() as $obDestino){
            $cidades[$obDestino->getCidade()] = $obDestino->exibirInformacoes();
        }

        $folgas = ['' => 'Atende todos os dias'];
        for($dia = 0; $dia <= 6; $dia++){
            $folgas[$dia] = Formatador::nomeDiaSemana($dia);
        }

        $edicao = $obGuia instanceof GuiaTuristico;

        return self::renderizarPagina('admin/guia-form.php', [
            'abas'               => self::getAbas('guias'),
            'tituloPagina'       => $edicao ? 'Editar guia' : 'Novo guia turístico',
            'action'             => $edicao ? '/admin/guias/editar' : '/admin/guias/novo',
            'idGuia'             => $edicao ? $obGuia->getIdGuia() : '',
            'feedback'           => empty($erros) ? '' : self::getAlertaLista('erro', $erros),
            'valorNome'          => self::e($valores['nome'] ?? ''),
            'valorTelefone'      => self::e($valores['telefone'] ?? ''),
            'valorEspecialidade' => self::e($valores['especialidade'] ?? ''),
            'valorDiaria'        => self::e($valores['valorDiaria'] ?? ''),
            'opcoesCidade'       => self::opcoesSelect($cidades, $valores['cidadeAtuacao'] ?? ''),
            'opcoesFolga'        => self::opcoesSelect($folgas, $valores['diaFolga'] ?? ''),
            'marcadoAtivo'       => !empty($valores['ativo']) ? 'checked' : '',
            'textoBotao'         => $edicao ? 'Salvar alterações' : 'Cadastrar guia'
        ], 'admin', $edicao ? 'Editar guia' : 'Novo guia');
    }

    private static function getGuiaOuErro(mixed $idGuia): GuiaTuristico
    {
        $obGuia = GuiaTuristico::getPorId((int) $idGuia);

        if(!$obGuia instanceof GuiaTuristico){
            echo ErroController::exibir(404, 'Guia não encontrado', 'O guia informado não existe ou já foi excluído.');
            exit();
        }

        return $obGuia;
    }

    public static function getUsuarios(): string
    {
        $obAdmin = self::exigirAdministrador();

        $linhas = array_map(function(Usuario $obUsuario) use ($obAdmin){
            $proprio = $obUsuario->getIdUsuario() === $obAdmin->getIdUsuario();

            $acao = $proprio
                ? '<span class="text-xs text-slate-400">Você</span>'
                : ViewRenderer::getComponente([
                    'id'           => $obUsuario->getIdUsuario(),
                    'novoStatus'   => $obUsuario->isAtivo() ? '0' : '1',
                    'textoStatus'  => $obUsuario->isAtivo() ? 'Inativar' : 'Reativar',
                    'classeStatus' => $obUsuario->isAtivo() ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50',
                    'confirmacao'  => $obUsuario->isAtivo() ? 'Inativar este usuário? Ele perderá o acesso aos roteiros imediatamente.' : 'Reativar este usuário?'
                ], self::$pathComponentesAdmin, 'acao-usuario.php');

            return [
                'iniciais' => self::e(Texto::iniciais($obUsuario->getNome())),
                'nome'     => self::e($obUsuario->getNome()),
                'email'    => self::e($obUsuario->getEmail()),
                'perfil'   => $obUsuario->isAdministrador() ? 'Administrador' : 'Viajante',
                'cadastro' => Formatador::data($obUsuario->getDataCadastro()),
                'status'   => self::getBadgeStatus($obUsuario->isAtivo()),
                'acao'     => $acao
            ];
        }, Usuario::listar());

        return self::renderizarPagina('admin/usuarios.php', [
            'abas'   => self::getAbas('usuarios'),
            'linhas' => ViewRenderer::getLista($linhas, self::$pathComponentesAdmin, 'linha-usuario.php')
        ], 'admin', 'Usuários');
    }

    public static function postStatusUsuario(): void
    {
        $obAdmin = self::exigirAdministrador();
        $ativo   = ($_POST['ativo'] ?? '') === '1';

        try{
            if(!$obAdmin->alterarStatusUsuario((int) ($_POST['idUsuario'] ?? 0), $ativo)){
                throw new RegistroNaoEncontradoException('Usuário não encontrado.');
            }

            Session::setFlash('sucesso', $ativo ? 'Usuário reativado.' : 'Usuário inativado: ele não consegue mais entrar nem acessar seus roteiros.');
        }catch(RegraNegocioException $e){
            Session::setFlash('erro', $e->getMessage());
        }

        self::redirecionar('/admin/usuarios');
    }

    private static function getAbas(string $abaAtiva): string
    {
        $variaveis = [];
        foreach(['relatorio', 'guias', 'usuarios'] as $aba){
            $variaveis['aba' . ucfirst($aba)] = $aba === $abaAtiva
                ? 'border-orange-500 text-blue-950 font-semibold'
                : 'border-transparent text-slate-500 hover:text-blue-950';
        }

        return ViewRenderer::getComponente($variaveis, self::$pathComponentesAdmin, 'abas.php');
    }
}
