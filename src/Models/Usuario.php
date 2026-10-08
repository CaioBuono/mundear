<?php

namespace Models;

class Usuario
{
    private $idUsuario = null;

    private $nome = null;

    private $email = null;

    private $senha = null;

    private $dataCadastro = null;

    const PATH_TABELA_USUARIOS = __DIR__ . '/../BD/table_usuario.json';

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

    public function cadastrar(array $dadosUsuario): bool
    {
        $usuariosCadastrados = self::getTabela();

        $obUsuario = self::createUsuario($dadosUsuario);
        $obUsuario->definirIdUsuario($usuariosCadastrados);
        return $obUsuario->insertUsuario($usuariosCadastrados);
    }

    private function insertUsuario(array $usuariosCadastrados): bool
    {
        $novoUsuario = [
            'idUsuario'    => $this->getIdUsuario(),
            'nome'         => $this->getNome(),
            'email'        => $this->getEmail(),
            'senha'        => password_hash($this->getSenha(), PASSWORD_ARGON2ID),
            'dataCadastro' => $this->getDataCadastro(),
        ];

        $usuariosCadastrados[] = $novoUsuario;
        return file_put_contents(self::PATH_TABELA_USUARIOS, json_encode($usuariosCadastrados));
    }

    private function definirIdUsuario(array $usuariosCadastrados): void
    {
        $idUltimoUsuario     = end($usuariosCadastrados)['idUsuario'];
        $idNovoUsuario       = $idUltimoUsuario + 1;

        $this->setIdUsuario($idNovoUsuario);
    }


    private static function createUsuario(array $dadosUsuario): Usuario
    {
        $obUsuario = new Usuario();
        $obUsuario->setIdUsuario($dadosUsuario['idUsuario'] ?? null);
        $obUsuario->setNome($dadosUsuario['nome']);
        $obUsuario->setEmail($dadosUsuario['email']);
        $obUsuario->setSenha($dadosUsuario['senha']);
        $obUsuario->setDataCadastro($dadosUsuario['dataCadastro'] ?? (new \DateTime())->format('Y-m-d H:i:s'));

        return $obUsuario;
    }

    private function validarSenha($senha): bool
    {
        return password_verify($senha, $this->getSenha());
    }

    private static function getTabela(): array
    {
        return json_decode(file_get_contents(self::PATH_TABELA_USUARIOS), true);
    }

    public static function autenticar(string $email, string $senha): bool|Usuario
    {
        $obUsuario = self::getUsuario($email);
        if(
            !$obUsuario instanceof Usuario or
            !$obUsuario->validarSenha($senha)
        ){
            return false;
        }

        return $obUsuario;
    }

    public static function getUsuario(string $email): Usuario|null
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