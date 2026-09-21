<?php

namespace Controllers;

use Session\Session;
use Views\ViewRenderer;
use Models\Usuario;

class UsuarioController extends Controller
{

    private static $pathEstruturaAuth = __DIR__ . '/../Views/layouts/auth/';

    private static $pathViewUsuario = __DIR__ . '/../Views/usuario/';

    public static function login(): string
    {
        $paths = [
            'estrutura' => self::$pathEstruturaAuth,
            'layout'    => self::$pathViewUsuario
        ];

        $obUsuario = Usuario::autenticar($_POST['email'], $_POST['senha']);
        if(!$obUsuario){
            $erroAuth = ViewRenderer::getComponente([], self::$pathComponentsUsuario, 'erro-login.php');
            return ViewRenderer::getLayout(['errorAuth' => $erroAuth], $paths, 'login.php');
        }

        session_regenerate_id(true);
        Session::setUsuario($obUsuario);

        header('Location: /home');
        exit();
    }

    public static function logout(): void
    {
        session_unset();
        header('Location: /login');
        exit();
    }

    public static function getRaiz(): void
    {
        if(!self::verificarAutenticacao()){
            header('Location: /login');
            exit();
        }

        header('Location: /home');
        exit();
    }

    public static function getLogin(array $variaveisLayout = ['errorAuth' => '']): string
    {
        if(self::verificarAutenticacao()){
            header('Location: /home');
            exit();
        }

        $paths = [
            'estrutura' => self::$pathEstruturaAuth,
            'layout'    => self::$pathViewUsuario
        ];

        return ViewRenderer::getLayout($variaveisLayout, $paths,'login.php');
    }

    public static function getHome(): string
    {
        if(!self::verificarAutenticacao()){
            header('Location: /login');
            exit();
        }

        $paths = [
            'estrutura' => PATH_ESTRUTURA_PADRAO,
            'layout'    => self::$pathViewUsuario
        ];

        $layoutHome = ViewRenderer::getLayout([], $paths, 'home.php', true);
        return self::getLayoutAutenticado($layoutHome);
    }
}