<?php

namespace Controllers;

use Views\ViewRenderer;
use Models\Usuario;

class UsuarioController
{

    private static $pathEstruturaAuth = __DIR__ . '/../Views/layouts/auth/';

    private static $pathViewUsuario = __DIR__ . '/../Views/usuario/';

    public static function login()
    {
        $paths = [
            'estrutura' => self::$pathEstruturaAuth,
            'layout'    => self::$pathViewUsuario
        ];

        $obUsuario = Usuario::autenticar($_POST['email'], $_POST['senha']);
        if(!$obUsuario){
            $erroAuth = ViewRenderer::getComponente([], $paths['layout'], 'erro-login.php');
            return ViewRenderer::getLayout(['errorAuth' => $erroAuth], $paths, 'login.php');
        }

    }

    public static function getLogin(array $variaveisLayout = ['errorAuth' => '']): string
    {
        $paths = [
            'estrutura' => self::$pathEstruturaAuth,
            'layout'    => self::$pathViewUsuario,
        ];

        return ViewRenderer::getLayout($variaveisLayout, $paths,'login.php');
    }
}