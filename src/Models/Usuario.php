<?php

namespace Models;

class Usuario
{
    private $idUsuario = null;

    private $nome = null;

    private $email = null;

    private $senha = null;

    private $dataCadastro = null;

    private function setIdUsuario($idUsuario)
    {
        $this->idUsuario = $idUsuario;
    }

    private function setNome($nome)
    {
        $this->nome = $nome;
    }

    private function setEmail($email){
        $this->email = $email;
    }

    private function setSenha($senha)
    {
        $this->senha = $senha;
    }

    private function setDataCadastro($dataCadastro)
    {
        $this->dataCadastro = $dataCadastro;
    }

    private function getSenha()
    {
        return $this->senha;
    }

    public function getIdUsuario()
    {
        return $this->idUsuario;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getDataCadastro()
    {
        return $this->dataCadastro;
    }


    private static function createUsuario(array $dadosUsuario): Usuario
    {
        $obUsuario = new Usuario();
        $obUsuario->setIdUsuario($dadosUsuario['idUsuario']);
        $obUsuario->setNome($dadosUsuario['nome']);
        $obUsuario->setEmail($dadosUsuario['email']);
        $obUsuario->setSenha($dadosUsuario['senha']);
        $obUsuario->setDataCadastro($dadosUsuario['dataCadastro']);

        return $obUsuario;
    }

    private function validarSenha($senha): bool
    {
        return password_verify($senha, $this->getSenha());
    }

    private static function getTabela(): array
    {
        return json_decode(file_get_contents(__DIR__ . '/../BD/table_usuario.json'), true);
    }

    public static function autenticar(string $email, string $senha): bool|Usuario
    {
        $obUsuario = self::getUsuarioLogin($email);
        if(
            !$obUsuario instanceof Usuario or
            !$obUsuario->validarSenha($senha)
        ){
            return false;
        }

        return $obUsuario;
    }

    private static function getUsuarioLogin(string $email): Usuario|null
    {
        $usuariosCadastrados = self::getTabela();
        return (self::validarUsuarioCadastrado($email, $usuariosCadastrados))
            ? self::createUsuario(self::getDados($email, $usuariosCadastrados))
            : null;
    }

    protected static function validarUsuarioCadastrado(string $email, array $usuariosCadastrados): bool
    {
        return in_array(
            $email,
            array_column($usuariosCadastrados, 'email')
        );
    }

    protected static function getDados(string $email, array $usuariosCadastrados): array
    {
        $chaveRegistro = array_find_key(
            array_column($usuariosCadastrados, 'email'),
            function ($e) use ($email) {
                return $e == $email;
            }
        );

        return $usuariosCadastrados[$chaveRegistro];
    }



}