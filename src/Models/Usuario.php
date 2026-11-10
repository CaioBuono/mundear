<?php

namespace Models;

use BD\TabelaJson;
use Exceptions\DadosInvalidosException;
use Services\EmailSimulado;

class Usuario
{
    const PERFIL_VIAJANTE      = 'viajante';
    const PERFIL_ADMINISTRADOR = 'administrador';

    const TAMANHO_MINIMO_SENHA = 8;

    protected $idUsuario = null;

    protected $nome = null;

    protected $email = null;

    protected $senha = null;

    protected $dataCadastro = null;

    protected $perfil = self::PERFIL_VIAJANTE;

    protected $ativo = true;

    protected $receberEmails = true;

    protected static function getTabela(): TabelaJson
    {
        return new TabelaJson('usuario', 'idUsuario');
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

    public function getPerfil(): string
    {
        return $this->perfil;
    }

    public function isAtivo(): bool
    {
        return (bool) $this->ativo;
    }

    public function isReceberEmails(): bool
    {
        return (bool) $this->receberEmails;
    }

    public function isAdministrador(): bool
    {
        return false;
    }

    public function obterTelaInicial(): string
    {
        return '/home';
    }

    private function getSenha()
    {
        return $this->senha;
    }

    public function cadastrar(array $dadosUsuario): bool
    {
        $this->nome         = trim($dadosUsuario['nome']);
        $this->email        = strtolower(trim($dadosUsuario['email']));
        $this->senha        = self::gerarHash($dadosUsuario['senha']);
        $this->dataCadastro = (new \DateTime())->format('Y-m-d H:i:s');
        $this->perfil       = self::PERFIL_VIAJANTE;
        $this->ativo        = true;
        $this->receberEmails = !empty($dadosUsuario['receberEmails']);

        $this->idUsuario = self::getTabela()->inserir($this->toArray());

        return $this->idUsuario > 0;
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

    private function validarSenha($senha): bool
    {
        return strlen((string) $this->getSenha()) > 0 && password_verify($senha, $this->getSenha());
    }

    private static function gerarHash(string $senha): string
    {
        return password_hash($senha, defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT);
    }

    public function atualizarDados(string $nome, string $email, bool $receberEmails): void
    {
        $nome  = trim($nome);
        $email = strtolower(trim($email));

        $obExistente = self::getUsuario($email);
        if($obExistente instanceof Usuario and $obExistente->getIdUsuario() !== $this->idUsuario){
            throw new DadosInvalidosException('Já existe outro usuário cadastrado com esse e-mail.', 'RN06');
        }

        $this->nome          = $nome;
        $this->email         = $email;
        $this->receberEmails = $receberEmails;

        self::getTabela()->atualizar($this->idUsuario, ['nome' => $nome, 'email' => $email, 'receberEmails' => $receberEmails]);
    }

    public function alterarSenha(string $senhaAtual, string $novaSenha): void
    {
        if(!$this->validarSenha($senhaAtual)){
            throw new DadosInvalidosException('A senha atual informada está incorreta.', 'RN06');
        }

        $this->definirNovaSenha($novaSenha);
    }

    private function definirNovaSenha(string $novaSenha): void
    {
        $this->senha = self::gerarHash($novaSenha);

        self::getTabela()->atualizar($this->idUsuario, [
            'senha'            => $this->senha,
            'tokenRecuperacao' => null,
            'expiracaoToken'   => null
        ]);
    }

    public static function recuperarSenha(string $email): bool
    {
        $obUsuario = self::getUsuario($email);
        if(!$obUsuario instanceof Usuario or !$obUsuario->isAtivo()){
            return false;
        }

        $token = bin2hex(random_bytes(32));

        self::getTabela()->atualizar($obUsuario->getIdUsuario(), [
            'tokenRecuperacao' => hash('sha256', $token),
            'expiracaoToken'   => (new \DateTime('+1 hour'))->format('Y-m-d H:i:s')
        ]);

        EmailSimulado::enviar(
            $obUsuario->getEmail(),
            'Mundear - Recuperação de senha',
            'Olá, ' . $obUsuario->getNome() . '! Use o link abaixo para criar uma nova senha. Ele expira em 1 hora.',
            '/redefinir-senha?token=' . $token
        );

        return true;
    }

    public static function getUsuarioPorTokenRecuperacao(string $token): ?Usuario
    {
        if($token === '') return null;

        $hashToken = hash('sha256', $token);
        $registro  = self::getTabela()->buscarPrimeiro(
            fn($usuario) => !empty($usuario['tokenRecuperacao']) && hash_equals($usuario['tokenRecuperacao'], $hashToken)
        );

        if(!$registro or strtotime($registro['expiracaoToken'] ?? '') < time()){
            return null;
        }

        return self::criarAPartirDoRegistro($registro);
    }

    public static function redefinirSenha(string $token, string $novaSenha): bool
    {
        $obUsuario = self::getUsuarioPorTokenRecuperacao($token);
        if(!$obUsuario instanceof Usuario){
            return false;
        }

        $obUsuario->definirNovaSenha($novaSenha);
        return true;
    }

    public function alterarStatus(bool $ativo): void
    {
        $this->ativo = $ativo;
        self::getTabela()->atualizar($this->idUsuario, ['ativo' => $ativo]);
    }

    public static function getUsuario(string $email): Usuario|null
    {
        $email    = strtolower(trim($email));
        $registro = self::getTabela()->buscarPrimeiro(fn($usuario) => strtolower($usuario['email']) === $email);

        return $registro ? self::criarAPartirDoRegistro($registro) : null;
    }

    public static function getUsuarioPorId(?int $idUsuario): ?Usuario
    {
        if(!$idUsuario) return null;

        $registro = self::getTabela()->buscarPorId($idUsuario);
        return $registro ? self::criarAPartirDoRegistro($registro) : null;
    }

    public static function listar(): array
    {
        return array_map(fn($registro) => self::criarAPartirDoRegistro($registro), self::getTabela()->listar());
    }

    protected static function criarAPartirDoRegistro(array $registro): Usuario
    {
        $obUsuario = ($registro['perfil'] ?? '') === self::PERFIL_ADMINISTRADOR ? new Administrador() : new Usuario();
        $obUsuario->preencher($registro);

        return $obUsuario;
    }

    protected function preencher(array $registro): void
    {
        $this->idUsuario    = (int) $registro['idUsuario'];
        $this->nome         = $registro['nome'];
        $this->email        = $registro['email'];
        $this->senha        = $registro['senha'] ?? '';
        $this->dataCadastro = $registro['dataCadastro'] ?? null;
        $this->perfil       = $registro['perfil'] ?? self::PERFIL_VIAJANTE;
        $this->ativo        = $registro['ativo'] ?? true;
        $this->receberEmails = $registro['receberEmails'] ?? true;
    }

    protected function toArray(): array
    {
        return [
            'idUsuario'    => $this->idUsuario,
            'nome'         => $this->nome,
            'email'        => $this->email,
            'senha'        => $this->senha,
            'dataCadastro' => $this->dataCadastro,
            'perfil'       => $this->perfil,
            'ativo'        => $this->ativo,
            'receberEmails' => $this->receberEmails
        ];
    }
}
