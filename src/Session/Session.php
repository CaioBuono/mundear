<?php

namespace Session;

use \Models\Usuario;

class Session
{
    public static function setUsuario(Usuario $obUsuario): void
    {
        $_SESSION['usuario'] = [
            'idUsuario' => $obUsuario->getIdUsuario(),
            'nome'      => $obUsuario->getNome(),
            'email'     => $obUsuario->getEmail()
        ];
    }

    public static function verifyUsuarioLogado(): bool
    {
        return (isset($_SESSION['usuario']));
    }

    public static function getDadosUsuarioLogado(): array
    {
        return $_SESSION['usuario'];
    }
}