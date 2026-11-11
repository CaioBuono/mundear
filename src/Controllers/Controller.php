<?php

namespace Controllers;

use Models\Administrador;
use Models\Destino;
use Models\Usuario;
use Session\Session;
use Utils\Formatador;
use Utils\Texto;
use Views\Icones;
use Views\ViewRenderer;

abstract class Controller
{
    protected static $pathComponentsUsuario = __DIR__ . '/../Views/usuario/components/';

    protected static $pathComponentes = __DIR__ . '/../Views/components/';

    private const CLASSE_NAV_BASE    = 'relative flex flex-none items-center gap-2.5 whitespace-nowrap rounded-lg px-2.5 py-2 text-sm font-bold transition xl:px-3.5 xl:py-2.5 xl:text-[15px] [&_svg]:h-[22px] [&_svg]:w-[22px] [&_svg]:flex-none';
    private const CLASSE_NAV_ATIVO   = self::CLASSE_NAV_BASE . ' bg-navy-2 text-white [&_svg]:text-laranja-claro xl:after:absolute xl:after:inset-x-3.5 xl:after:-bottom-[15px] xl:after:h-[3px] xl:after:rounded-t xl:after:bg-laranja';
    private const CLASSE_NAV_INATIVO = self::CLASSE_NAV_BASE . ' text-menu hover:bg-navy-2 hover:text-white';

    private const ICONES_MENU = [
        'inicio'     => 'casa',
        'transporte' => 'onibus',
        'hospedagem' => 'cama',
        'turismo'    => 'bussola',
        'roteiros'   => 'lista',
        'admin'      => 'escudo'
    ];

    private static ?Usuario $usuarioLogado = null;

    protected static function verificarAutenticacao(): bool
    {
        if(!Session::verifyUsuarioLogado()){
            return false;
        }

        $obUsuario = Usuario::getUsuarioPorId(Session::getIdUsuarioLogado());
        if(!$obUsuario instanceof Usuario or !$obUsuario->isAtivo()){
            Session::encerrar();
            Session::setFlash('erro', 'Sua conta está inativa e não pode acessar os roteiros. Fale com o suporte do Mundear.');
            return false;
        }

        Session::setUsuario($obUsuario);
        self::$usuarioLogado = $obUsuario;

        return true;
    }

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

    protected static function urlInterna(?string $url, string $padrao): string
    {
        $valida = is_string($url) && str_starts_with($url, '/') && !str_starts_with($url, '//') && !str_contains($url, '\\');

        return $valida ? $url : $padrao;
    }

    protected static function renderizarPagina(string $view, array $variaveis, string $menuAtivo, string $titulo, array $estruturaExtra = []): string
    {
        $dadosUsuario = Session::getDadosUsuarioLogado();
        $isAdmin      = Session::isAdministrador();

        $perfilUsuario = ViewRenderer::getComponente([
            'icone'       => self::e(Texto::iniciais($dadosUsuario['nome'] ?? '')),
            'nomeUsuario' => self::e($dadosUsuario['nome'] ?? ''),
            'classeNome'  => $isAdmin ? '2xl:block' : 'md:block'
        ], self::$pathComponentsUsuario, 'usuario-perfil.php');

        $variaveisEstrutura = [
            'titulo'        => self::e($titulo),
            'perfilUsuario' => $perfilUsuario,
            'flash'         => self::getFlashHtml()
        ];

        foreach(self::ICONES_MENU as $menu => $icone){
            $variaveisEstrutura['nav' . ucfirst($menu)]    = $menu === $menuAtivo ? self::CLASSE_NAV_ATIVO : self::CLASSE_NAV_INATIVO;
            $variaveisEstrutura['icone' . ucfirst($menu)] = Icones::get($icone);
        }

        if(!$isAdmin){
            $variaveisEstrutura['navAdmin'] = 'hidden';
        }

        return ViewRenderer::getLayout($variaveis, ['estrutura' => PATH_ESTRUTURA_PADRAO, 'layout' => PATH_VIEWS], $view, $estruturaExtra + $variaveisEstrutura);
    }

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

    protected static function getAlerta(string $tipo, string $mensagem, string $acaoHtml = ''): string
    {
        return self::montarAlerta($tipo, '<p>' . self::e($mensagem) . '</p>', $acaoHtml);
    }

    protected static function getAlertaLista(string $tipo, array $mensagens): string
    {
        $itens = implode('', array_map(fn($mensagem) => '<li>' . self::e($mensagem) . '</li>', $mensagens));

        return self::montarAlerta($tipo, '<ul class="list-disc space-y-1 pl-5 font-medium">' . $itens . '</ul>');
    }

    private static function montarAlerta(string $tipo, string $conteudoHtml, string $acaoHtml = ''): string
    {
        $estilos = [
            'sucesso' => ['border-[#b4e0c9] bg-ok-suave text-ok', 'Tudo certo!'],
            'erro'    => ['border-[#f1b9a4] bg-alerta-suave text-alerta', 'Não foi possível concluir'],
            'info'    => ['border-linha bg-suave text-navy', 'Atenção']
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

    protected static function e(mixed $valor): string
    {
        return str_replace(['{', '}'], ['&#123;', '&#125;'], htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    }

    protected static function moeda(float $valor): string
    {
        return Formatador::moeda($valor);
    }

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

    protected static function getDatalistDestinos(): string
    {
        return implode('', array_map(
            fn(Destino $obDestino) => '<option value="' . self::e($obDestino->getCidade() . ' - ' . $obDestino->getEstado()) . '"></option>',
            Destino::listar()
        ));
    }

    protected static function getBadgeStatus(bool $ativo, string $textoAtivo = 'Ativo', string $textoInativo = 'Inativo'): string
    {
        return $ativo
            ? '<span class="inline-flex items-center whitespace-nowrap rounded-full bg-ok-suave px-[9px] py-[3px] text-[11.5px] font-bold text-ok">' . self::e($textoAtivo) . '</span>'
            : '<span class="inline-flex items-center whitespace-nowrap rounded-full bg-[#eef0f4] px-[9px] py-[3px] text-[11.5px] font-bold text-apagado">' . self::e($textoInativo) . '</span>';
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
