<?php

namespace Controllers;

use Session\Session;
use Views\ViewRenderer;

abstract class Controller
{

    protected static $pathComponentsUsuario = __DIR__ . '/../Views/usuario/components/';

    protected static function verificarAutenticacao(): bool
    {
        return Session::verifyUsuarioLogado();
    }

    protected static function getLayoutAutenticado($conteudo): string
    {
        $dadosUsuario = self::getDadosUsuario();
        $dadosUsuario = [
            'inicial' => self::getIniciais($dadosUsuario['nome']),
            'nome'    => $dadosUsuario['nome']
        ];

        return ViewRenderer::getHeaderUsuarioAutenticado($dadosUsuario, self::$pathComponentsUsuario) . $conteudo;
    }

    protected static function getDadosUsuario(): array
    {
        return Session::getDadosUsuarioLogado();
    }

    protected static function getIniciais(string $nome): string
    {
        $nomeUsuario  = $nome;
        $nomeSeparado = explode(' ', $nomeUsuario);

        if(count($nomeSeparado) > 1){
            return strtoupper($nomeSeparado[0][0] . $nomeSeparado[1][0]);
        }

        return strtoupper($nomeSeparado[0][0] . $nomeSeparado[0][0]);
    }
}