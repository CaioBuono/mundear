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
use Views\Icones;
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
                '<li><div class="flex justify-between text-sm"><span class="font-bold">%s</span><span class="text-apagado">%d roteiro(s)</span></div><div class="mt-1.5 h-2 overflow-hidden rounded-full bg-suave"><div class="h-2 rounded-full bg-laranja" style="width:%d%%"></div></div></li>',
                self::e($cidade),
                $quantidade,
                (int) round($quantidade / $maiorDestino * 100)
            );
        }

        $rotulos = ItemRoteiro::getRotulosTipo();
        $itens   = '';
        foreach($dados['itensPorTipo'] as $tipo => $quantidade){
            $itens .= sprintf('<div class="flex items-center justify-between border-t border-linha py-[9px] text-sm first:border-t-0"><span class="text-apagado">%s</span><b>%d</b></div>', self::e($rotulos[$tipo]), $quantidade);
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
            'destinosPopulares'   => $destinos ?: '<li class="dica">Nenhum roteiro criado ainda.</li>',
            'itensPorTipo'        => $itens,
            'resumo'              => self::e($obAdmin->gerarRelatorio())
        ], 'admin', 'Administração', ['cabecalhoPagina' => self::getCabecalhoAdmin()]);
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
            'unidade'       => self::e($obGuia->getUnidadeValor()),
            'status'        => self::getBadgeStatus($obGuia->isAtivo()),
            'novoStatus'    => $obGuia->isAtivo() ? '0' : '1',
            'textoStatus'   => $obGuia->isAtivo() ? 'Inativar' : 'Reativar',
            'classeStatus'  => $obGuia->isAtivo() ? 'btn-perigo' : 'btn-secundario',
            'iniciais'      => self::e(Texto::iniciais($obGuia->getNome())),
            'classeLinha'   => $obGuia->isAtivo() ? '' : 'text-apagado',
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
                ? '<tr><td colspan="6" class="px-4 py-10 text-center text-apagado">Nenhum guia encontrado com esses filtros.</td></tr>'
                : ViewRenderer::getLista($linhas, self::$pathComponentesAdmin, 'linha-guia.php')
        ], 'admin', 'Guias turísticos', ['cabecalhoPagina' => self::getCabecalhoAdmin()]);
    }

    public static function getNovoGuia(): string
    {
        self::exigirAdministrador();

        return self::renderizarFormularioGuia(null, ['ativo' => '1', 'unidadeValor' => 'meio período', 'idiomas' => 'Português'], []);
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
            'unidadeValor'  => $obGuia->getUnidadeValor(),
            'idiomas'       => $obGuia->getIdiomas(),
            'regioes'       => $obGuia->getRegioes(),
            'apresentacao'  => $obGuia->getApresentacao(),
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
            'opcoesUnidade'      => self::opcoesSelect(array_combine(GuiaTuristico::UNIDADES_VALOR, array_map('ucfirst', GuiaTuristico::UNIDADES_VALOR)), $valores['unidadeValor'] ?? 'dia'),
            'valorIdiomas'       => self::e($valores['idiomas'] ?? ''),
            'valorRegioes'       => self::e($valores['regioes'] ?? ''),
            'valorApresentacao'  => self::e($valores['apresentacao'] ?? ''),
            'marcadoAtivo'       => !empty($valores['ativo']) ? 'checked' : '',
            'textoBotao'         => $edicao ? 'Salvar alterações' : 'Cadastrar guia'
        ], 'admin', $edicao ? 'Editar guia' : 'Novo guia', ['cabecalhoPagina' => self::getCabecalhoAdmin()]);
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
                ? '<span class="dica">Você</span>'
                : ViewRenderer::getComponente([
                    'id'           => $obUsuario->getIdUsuario(),
                    'novoStatus'   => $obUsuario->isAtivo() ? '0' : '1',
                    'textoStatus'  => $obUsuario->isAtivo() ? 'Inativar' : 'Reativar',
                    'classeStatus' => $obUsuario->isAtivo() ? 'btn-perigo' : 'btn-secundario',
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
        ], 'admin', 'Usuários', ['cabecalhoPagina' => self::getCabecalhoAdmin()]);
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

    private static function getCabecalhoAdmin(): string
    {
        return sprintf(
            '<div class="border-b border-linha bg-white"><div class="container-app flex flex-wrap items-center justify-between gap-x-8 gap-y-5 pb-6 pt-[22px]"><div class="flex min-w-0 items-center gap-4"><span class="grid h-14 w-14 flex-none place-items-center rounded-[14px] bg-navy text-white shadow-[0_8px_18px_-10px_var(--color-navy)]">%s</span><div class="min-w-0"><div class="flex flex-wrap items-center gap-2.5"><h1 class="text-[28px] font-extrabold leading-tight tracking-[-.01em]">Painel do administrador</h1><span class="rounded-full bg-laranja px-2.5 py-1 text-[11.5px] font-extrabold uppercase tracking-[.06em] text-white">Administrador</span></div><p class="mt-0.5 text-[14.5px] text-apagado">Acompanhe os usuários da plataforma e gerencie os guias turísticos cadastrados.</p></div></div><a href="/admin/guias/novo" class="btn-destaque btn-lg">%sCadastrar guia</a></div></div>',
            Icones::get('escudo', 'h-7 w-7'),
            Icones::get('mais', 'h-[18px] w-[18px]')
        );
    }

    private static function getAbas(string $abaAtiva): string
    {
        $variaveis = [];
        foreach(['relatorio', 'guias', 'usuarios'] as $aba){
            $variaveis['aba' . ucfirst($aba)] = $aba === $abaAtiva ? 'true' : 'false';
        }

        return ViewRenderer::getComponente($variaveis, self::$pathComponentesAdmin, 'abas.php');
    }
}
