<?php

namespace Controllers;

use Views\ViewRenderer;
use Models\Usuario;

class UsuarioController
{

    private static $pathEstrutura = __DIR__ . '/../Views/layouts/auth/';

    private static $pathViewUsuario = __DIR__ . '/../Views/usuario/';

    public static function login()
    {
        echo 'Logado com sucesso!';
    }

    public static function getLogin(): string
    {
        $paths = [
            'estrutura' => self::$pathEstrutura,
            'layout'    => self::$pathViewUsuario,
        ];

        return ViewRenderer::getLayout([], $paths,'login.php');
    }
}