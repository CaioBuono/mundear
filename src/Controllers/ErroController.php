<?php

namespace Controllers;

use Session\Session;

class ErroController extends Controller
{
    public static function exibir(int $status, string $titulo, string $mensagem): string
    {
        http_response_code($status);

        $logado = Session::verifyUsuarioLogado();

        return self::renderizarPaginaAvulsa('erro/erro.php', [
            'status'     => $status,
            'tituloErro' => self::e($titulo),
            'mensagem'   => self::e($mensagem),
            'linkVoltar' => $logado ? '/home' : '/login',
            'textoVoltar'=> $logado ? 'Voltar ao início' : 'Ir para o login'
        ], $titulo);
    }
}
