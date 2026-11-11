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
            'email'     => $obUsuario->getEmail(),
            'perfil'    => $obUsuario->getPerfil()
        ];
    }

    public static function verifyUsuarioLogado(): bool
    {
        return (isset($_SESSION['usuario']['idUsuario']));
    }

    public static function getDadosUsuarioLogado(): array
    {
        return $_SESSION['usuario'] ?? [];
    }

    public static function getIdUsuarioLogado(): ?int
    {
        return isset($_SESSION['usuario']['idUsuario']) ? (int) $_SESSION['usuario']['idUsuario'] : null;
    }

    public static function isAdministrador(): bool
    {
        return ($_SESSION['usuario']['perfil'] ?? '') === Usuario::PERFIL_ADMINISTRADOR;
    }

    public static function encerrar(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }

    public static function lembrarUsuario(): void
    {
        setcookie(session_name(), session_id(), [
            'expires'  => time() + 60 * 60 * 24 * 30,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    public static function setFlash(string $tipo, string $mensagem, ?string $link = null, ?string $textoLink = null): void
    {
        $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem, 'link' => $link, 'textoLink' => $textoLink];
    }

    public static function getFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        return $flash;
    }

    public static function setDadosFormulario(string $formulario, array $dados): void
    {
        $_SESSION['formularios'][$formulario] = $dados;
    }

    public static function getDadosFormulario(string $formulario): ?array
    {
        $dados = $_SESSION['formularios'][$formulario] ?? null;
        unset($_SESSION['formularios'][$formulario]);

        return $dados;
    }

    public static function getTokenCsrf(): string
    {
        if(empty($_SESSION['csrf'])){
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf'];
    }

    public static function validarTokenCsrf(string $token): bool
    {
        return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
    }
}
