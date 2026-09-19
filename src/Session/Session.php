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
}