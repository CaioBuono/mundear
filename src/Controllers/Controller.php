<?php

namespace Controllers;

use Models\Administrador;
use Models\Destino;
use Models\Usuario;
use Session\Session;
use Utils\Formatador;
use Utils\Texto;
use Views\ViewRenderer;

abstract class Controller
{

    protected static $pathComponentsUsuario = __DIR__ . '/../Views/usuario/components/';

    protected static $pathComponentes = __DIR__ . '/../Views/components/';

    private const CLASSE_NAV_ATIVO   = 'flex items-center gap-2 border-b-2 border-orange-500 py-5 font-semibold text-blue-950';
    private const CLASSE_NAV_INATIVO = 'flex items-center gap-2 border-b-2 border-transparent py-5 text-slate-600 transition hover:text-blue-950';
    private const CLASSE_MOB_ATIVO   = 'flex flex-col items-center gap-1 py-2 text-[11px] font-semibold text-blue-950';
    private const CLASSE_MOB_INATIVO = 'flex flex-col items-center gap-1 py-2 text-[11px] text-slate-500';

    private static ?Usuario $usuarioLogado = null;

    // ------------------------------------------------------------------
    // Autenticação e permissões
    // ------------------------------------------------------------------

    protected static function verificarAutenticacao(): bool
    {
        if(!Session::verifyUsuarioLogado()){
            return false;
        }

        // RN08: um usuário inativado perde o acesso imediatamente
        $obUsuario = Usuario::getUsuarioPorId(Session::getIdUsuarioLogado());
        if(!$obUsuario instanceof Usuario or !$obUsuario->isAtivo()){
            Session::encerrar();
            Session::setFlash('erro', 'Sua conta está inativa e não pode acessar os roteiros. Fale com o suporte do Mundear.');
            return false;
        }

        // Mantém nome e perfil da sessão sincronizados com o banco
        Session::setUsuario($obUsuario);
        self::$usuarioLogado = $obUsuario;

        return true;
    }

    /**
     * RN01: páginas da área logada exigem autenticação.
     */
    protected static function exigirAutenticacao(): Usuario
    {
        if(!self::verificarAutenticacao()){
            self::redirecionar('/login');
        }

        return self::$usuarioLogado;
    }

    protected static function exigirAdministrador(): Administrador
    {
        $obUsuario = self::exigirAutenticacao();

        if(!$obUsuario instanceof Administrador){
            echo ErroController::exibir(403, 'Acesso restrito', 'Esta área é exclusiva para administradores do Mundear.');
            exit();
        }

        return $obUsuario;
    }

    protected static function redirecionar(string $url): never
    {
        header('Location: ' . $url);
        exit();
    }

    /**
     * Aceita somente caminhos internos (evita redirecionar para outros sites).
     */
    protected static function urlInterna(?string $url, string $padrao): string
    {
        $valida = is_string($url) && str_starts_with($url, '/') && !str_starts_with($url, '//') && !str_contains($url, '\\');

        return $valida ? $url : $padrao;
    }

    // ------------------------------------------------------------------
    // Renderização
    // ------------------------------------------------------------------

    /**
     * Página da área logada: header com menu (desktop e mobile) + view + footer.
     *
     * @param string $view      caminho relativo a src/Views (ex.: 'roteiro/lista.php')
     * @param string $menuAtivo inicio | transporte | hospedagem | turismo | roteiros | admin
     */
    protected static function renderizarPagina(string $view, array $variaveis, string $menuAtivo, string $titulo): string
    {
        $dadosUsuario = Session::getDadosUsuarioLogado();
        $isAdmin      = Session::isAdministrador();

        $perfilUsuario = ViewRenderer::getComponente([
            'icone'       => self::e(Texto::iniciais($dadosUsuario['nome'] ?? '')),
            'nomeUsuario' => self::e($dadosUsuario['nome'] ?? ''),
            'linkAdmin'   => $isAdmin ? '<a href="/admin" class="block px-5 py-3 text-sm hover:bg-slate-50">Administração</a>' : ''
        ], self::$pathComponentsUsuario, 'usuario-perfil.php');

        $variaveisEstrutura = [
            'titulo'        => self::e($titulo),
            'perfilUsuario' => $perfilUsuario,
            'flash'         => self::getFlashHtml()
        ];

        foreach(['inicio', 'transporte', 'hospedagem', 'turismo', 'roteiros', 'admin'] as $menu){
            $ativo = $menu === $menuAtivo;
            $variaveisEstrutura['nav' . ucfirst($menu)] = $ativo ? self::CLASSE_NAV_ATIVO : self::CLASSE_NAV_INATIVO;
            $variaveisEstrutura['mob' . ucfirst($menu)] = $ativo ? self::CLASSE_MOB_ATIVO : self::CLASSE_MOB_INATIVO;
        }

        if(!$isAdmin){
            $variaveisEstrutura['navAdmin'] = 'hidden';
        }

        return ViewRenderer::getLayout($variaveis, ['estrutura' => PATH_ESTRUTURA_PADRAO, 'layout' => PATH_VIEWS], $view, $variaveisEstrutura);
    }

    /**
     * Página avulsa (login, recuperação de senha, erros e link compartilhado).
     */
    protected static function renderizarPaginaAvulsa(string $view, array $variaveis, string $titulo): string
    {
        return ViewRenderer::getLayout($variaveis, ['estrutura' => PATH_ESTRUTURA_AUTH, 'layout' => PATH_VIEWS], $view, ['titulo' => self::e($titulo)]);
    }

    protected static function getFlashHtml(): string
    {
        $flash = Session::getFlash();
        if(!$flash){
            return '';
        }

        $acao = '';
        if(!empty($flash['link'])){
            $acao = '<a href="' . self::e($flash['link']) . '" class="mt-2 inline-flex font-semibold underline underline-offset-2">' . self::e($flash['textoLink'] ?? 'Ver') . '</a>';
        }

        return self::getAlerta($flash['tipo'], $flash['mensagem'], $acao);
    }

    /**
     * Caixa de mensagem (RNF04: mensagens compreensíveis ao usuário).
     */
    protected static function getAlerta(string $tipo, string $mensagem, string $acaoHtml = ''): string
    {
        return self::montarAlerta($tipo, '<p>' . self::e($mensagem) . '</p>', $acaoHtml);
    }

    protected static function getAlertaLista(string $tipo, array $mensagens): string
    {
        $itens = implode('', array_map(fn($mensagem) => '<li>' . self::e($mensagem) . '</li>', $mensagens));

        return self::montarAlerta($tipo, '<ul class="list-disc space-y-1 pl-5">' . $itens . '</ul>');
    }

    private static function montarAlerta(string $tipo, string $conteudoHtml, string $acaoHtml = ''): string
    {
        $estilos = [
            'sucesso' => ['border-emerald-200 bg-emerald-50 text-emerald-800', 'Tudo certo!'],
            'erro'    => ['border-red-200 bg-red-50 text-red-700', 'Não foi possível concluir'],
            'info'    => ['border-blue-200 bg-blue-50 text-blue-900', 'Atenção']
        ];

        [$classes, $titulo] = $estilos[$tipo] ?? $estilos['info'];

        return ViewRenderer::getComponente([
            'classes'  => $classes,
            'titulo'   => $titulo,
            'mensagem' => $conteudoHtml,
            'acao'     => $acaoHtml
        ], self::$pathComponentes, 'alerta.php');
    }

    protected static function getEstadoVazio(string $titulo, string $mensagem, string $acaoHtml = ''): string
    {
        return ViewRenderer::getComponente([
            'titulo'   => self::e($titulo),
            'mensagem' => self::e($mensagem),
            'acao'     => $acaoHtml
        ], self::$pathComponentes, 'vazio.php');
    }

    // ------------------------------------------------------------------
    // Helpers de formatação para as views
    // ------------------------------------------------------------------

    /**
     * Escapa valores antes de inseri-los no HTML (evita XSS e placeholders injetados).
     */
    protected static function e(mixed $valor): string
    {
        return str_replace(['{', '}'], ['&#123;', '&#125;'], htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    }

    protected static function moeda(float $valor): string
    {
        return Formatador::moeda($valor);
    }

    /**
     * Lê um parâmetro da URL aceitando apenas valores da lista permitida.
     */
    protected static function parametroPermitido(string $nome, array $opcoes, string $padrao): string
    {
        $valor = (string) ($_GET[$nome] ?? $padrao);

        return array_key_exists($valor, $opcoes) ? $valor : $padrao;
    }

    protected static function opcoesSelect(array $opcoes, string|int|null $selecionado): string
    {
        $html = '';
        foreach($opcoes as $valor => $rotulo){
            $marcado = (string) $valor === (string) $selecionado ? ' selected' : '';
            $html   .= '<option value="' . self::e($valor) . '"' . $marcado . '>' . self::e($rotulo) . '</option>';
        }

        return $html;
    }

    protected static function getDatalistCidades(): string
    {
        return implode('', array_map(
            fn(Destino $obDestino) => '<option value="' . self::e($obDestino->getCidade()) . '"></option>',
            Destino::listar()
        ));
    }

    protected static function getBadgeStatus(bool $ativo, string $textoAtivo = 'Ativo', string $textoInativo = 'Inativo'): string
    {
        return $ativo
            ? '<span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">' . self::e($textoAtivo) . '</span>'
            : '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">' . self::e($textoInativo) . '</span>';
    }

    protected static function validarCamposObrigatorios($camposRecebidos, $camposObrigatorios): array
    {
        $camposInvalidos = [];
        foreach($camposObrigatorios as $campo){
            $valor = trim((string) ($camposRecebidos[$campo] ?? ''));

            if($valor === ''){
                $camposInvalidos[] = $campo;
                continue;
            }

            if(str_contains($campo, 'email') and !self::regexEmailValido($valor)){
                $camposInvalidos[] = $campo;
            }
        }

        return $camposInvalidos;
    }

    private static function regexEmailValido($email): bool
    {
        return preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email);
    }
}
