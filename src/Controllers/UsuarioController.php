<?php

namespace Controllers;

use Session\Session;
use Views\ViewRenderer;
use Models\Usuario;

class UsuarioController extends Controller
{

    private static $pathEstruturaAuth = __DIR__ . '/../Views/layouts/auth/';

    private static $pathViewUsuario = __DIR__ . '/../Views/usuario/';

    public static function cadastrar(): string
    {
        $paths = [
            'estrutura' => self::$pathEstruturaAuth,
            'layout'    => self::$pathViewUsuario
        ];

        $validacaoCampos = self::validarCamposObrigatorios($_POST, ['nome', 'email', 'senha', 'confirm']);
        if(!empty($validacaoCampos)){
            return self::retornarFeedbackErroCadastro('Um ou mais campos inválidos: ' .implode(', ', $validacaoCampos));
        }

        $obUsuario = Usuario::getUsuario($_POST['email']);
        if($obUsuario instanceof  Usuario){
            return self::retornarFeedbackErroCadastro('Já existe um usuário com esse e-mail');
        }

        //VALIDAR SENHAS
        if($_POST['senha'] != $_POST['confirm']){
            return self::retornarFeedbackErroCadastro('O campo de confirmação deve ser igual a senha');
        }

        $obUsuario = new Usuario();
        $sucessoCadastro = $obUsuario->cadastrar($_POST);
        if(!$sucessoCadastro){
            return self::retornarFeedbackErroCadastro('Ocorreu um erro ao cadastrar o usuário');
        }

        $mensagemSucesso = ViewRenderer::getComponente(['mensagem' => 'Usuário cadastrado com sucesso'], self::$pathComponentsUsuario, 'sucesso-cadastro.php');
        return ViewRenderer::getLayout(['feedback' => $mensagemSucesso], $paths, 'login.php');
    }

    public static function login(): string
    {
        $paths = [
            'estrutura' => self::$pathEstruturaAuth,
            'layout'    => self::$pathViewUsuario
        ];

        if(isset($_POST['nome']) and isset($_POST['confirm'])){
            return self::cadastrar();
        }

        $obUsuario = Usuario::autenticar($_POST['email'], $_POST['senha']);
        if(!$obUsuario){
            $erroAuth = ViewRenderer::getComponente(['tipoErro' => 'Credenciais inválidas', 'mensagem' => 'Tente autenticar novamente'], self::$pathComponentsUsuario, 'erro-login.php');
            return ViewRenderer::getLayout(['feedback' => $erroAuth], $paths, 'login.php');
        }

        session_regenerate_id(true);
        Session::setUsuario($obUsuario);

        header('Location: /home');
        exit();
    }

    public static function logout(): void
    {
        session_unset();
        header('Location: /login');
        exit();
    }

    public static function getRaiz(): void
    {
        if(!self::verificarAutenticacao()){
            header('Location: /login');
            exit();
        }

        header('Location: /home');
        exit();
    }

    public static function getLogin(array $variaveisLayout = ['feedback' => '']): string
    {
        if(self::verificarAutenticacao()){
            header('Location: /home');
            exit();
        }

        $paths = [
            'estrutura' => self::$pathEstruturaAuth,
            'layout'    => self::$pathViewUsuario
        ];

        return ViewRenderer::getLayout($variaveisLayout, $paths,'login.php');
    }

    public static function getHome(): string
    {
        if(!self::verificarAutenticacao()){
            header('Location: /login');
            exit();
        }

        $paths = [
            'estrutura' => PATH_ESTRUTURA_PADRAO,
            'layout'    => self::$pathViewUsuario
        ];

        $layoutHome = ViewRenderer::getLayout([], $paths, 'home.php', true);
        return self::getLayoutAutenticado($layoutHome);
    }

    private static function retornarFeedbackErroCadastro($mensagem): string
    {
        $mensagemErro = ViewRenderer::getComponente(['tipoErro' => 'Cadastro incompleto','mensagem' => $mensagem], self::$pathComponentsUsuario, 'erro-login.php');
        return ViewRenderer::getLayout(['feedback' => $mensagemErro], ['estrutura' => self::$pathEstruturaAuth, 'layout' => self::$pathViewUsuario], 'login.php');
    }
}