<?php

namespace Controllers;

use Exceptions\RegraNegocioException;
use Models\Administrador;
use Models\Roteiro;
use Session\Session;
use Utils\Formatador;
use Utils\Texto;
use Utils\Validador;
use Views\ViewRenderer;
use Models\Usuario;

class UsuarioController extends Controller
{

    private static $pathEstruturaAuth = __DIR__ . '/../Views/layouts/auth/';

    private static $pathViewUsuario = __DIR__ . '/../Views/usuario/';

    private const NOMES_CAMPOS = [
        'nome'    => 'Nome',
        'email'   => 'E-mail',
        'senha'   => 'Senha',
        'confirm' => 'Confirmar senha'
    ];

    // ------------------------------------------------------------------
    // Cadastro, login e logout (RF01)
    // ------------------------------------------------------------------

    public static function cadastrar(): string
    {
        $validacaoCampos = self::validarCamposObrigatorios($_POST, ['nome', 'email', 'senha', 'confirm']);
        if(!empty($validacaoCampos)){
            $nomesCampos = array_map(fn($campo) => self::NOMES_CAMPOS[$campo], array_unique($validacaoCampos));
            return self::retornarFeedbackErroCadastro('Preencha corretamente: ' . implode(', ', $nomesCampos) . '.');
        }

        if(strlen(trim($_POST['nome'])) < 3){
            return self::retornarFeedbackErroCadastro('Informe seu nome com pelo menos 3 caracteres.');
        }

        $obUsuario = Usuario::getUsuario($_POST['email']);
        if($obUsuario instanceof  Usuario){
            return self::retornarFeedbackErroCadastro('Já existe um usuário com esse e-mail');
        }

        //VALIDAR SENHAS
        if(strlen($_POST['senha']) < Usuario::TAMANHO_MINIMO_SENHA){
            return self::retornarFeedbackErroCadastro('A senha deve ter pelo menos ' . Usuario::TAMANHO_MINIMO_SENHA . ' caracteres.');
        }

        if($_POST['senha'] != $_POST['confirm']){
            return self::retornarFeedbackErroCadastro('O campo de confirmação deve ser igual a senha');
        }

        $obUsuario = new Usuario();
        $sucessoCadastro = $obUsuario->cadastrar($_POST);
        if(!$sucessoCadastro){
            return self::retornarFeedbackErroCadastro('Ocorreu um erro ao cadastrar o usuário');
        }

        $mensagemSucesso = ViewRenderer::getComponente(['mensagem' => 'Usuário cadastrado com sucesso. Agora é só entrar com seu e-mail e senha.'], self::$pathComponentsUsuario, 'sucesso-cadastro.php');
        return self::renderizarLogin(['feedback' => $mensagemSucesso, 'valorEmail' => self::e($obUsuario->getEmail())]);
    }

    public static function login(): string
    {
        if(isset($_POST['nome']) and isset($_POST['confirm'])){
            return self::cadastrar();
        }

        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if($email === '' or $senha === ''){
            return self::retornarFeedbackErroLogin('Campos obrigatórios', 'Informe seu e-mail e sua senha para entrar.', $email);
        }

        $obUsuario = Usuario::autenticar($email, $senha);
        if(!$obUsuario){
            return self::retornarFeedbackErroLogin('Credenciais inválidas', 'Tente autenticar novamente', $email);
        }

        // RN08: usuário inativado não acessa o sistema
        if(!$obUsuario->isAtivo()){
            return self::retornarFeedbackErroLogin('Conta inativa', 'Sua conta foi inativada por um administrador e não pode acessar os roteiros.', $email);
        }

        session_regenerate_id(true);
        Session::setUsuario($obUsuario);

        if(!empty($_POST['lembrar'])){
            Session::lembrarUsuario();
        }

        self::redirecionar($obUsuario->isAdministrador() ? '/admin' : '/home');
    }

    public static function logout(): void
    {
        Session::encerrar();
        Session::setFlash('sucesso', 'Você saiu da sua conta. Até a próxima viagem!');

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

    public static function getLogin(): string
    {
        if(self::verificarAutenticacao()){
            header('Location: /home');
            exit();
        }

        $flash = self::getFlashHtml();

        return self::renderizarLogin(['feedback' => $flash ? '<div class="mt-6">' . $flash . '</div>' : '']);
    }

    private static function renderizarLogin(array $variaveisLayout): string
    {
        $variaveisLayout += ['feedback' => '', 'modo' => 'login', 'valorEmail' => '', 'valorNome' => ''];

        $paths = [
            'estrutura' => self::$pathEstruturaAuth,
            'layout'    => self::$pathViewUsuario
        ];

        return ViewRenderer::getLayout($variaveisLayout, $paths, 'login.php', ['titulo' => 'Entrar']);
    }

    private static function retornarFeedbackErroLogin(string $tipoErro, string $mensagem, string $email): string
    {
        $erroAuth = ViewRenderer::getComponente(['tipoErro' => self::e($tipoErro), 'mensagem' => self::e($mensagem)], self::$pathComponentsUsuario, 'erro-login.php');

        return self::renderizarLogin(['feedback' => $erroAuth, 'valorEmail' => self::e($email)]);
    }

    private static function retornarFeedbackErroCadastro($mensagem): string
    {
        $mensagemErro = ViewRenderer::getComponente(['tipoErro' => 'Cadastro incompleto', 'mensagem' => self::e($mensagem)], self::$pathComponentsUsuario, 'erro-login.php');

        return self::renderizarLogin([
            'feedback'   => $mensagemErro,
            'modo'       => 'cadastro',
            'valorNome'  => self::e($_POST['nome'] ?? ''),
            'valorEmail' => self::e($_POST['email'] ?? '')
        ]);
    }

    // ------------------------------------------------------------------
    // Página inicial (painel do viajante)
    // ------------------------------------------------------------------

    public static function getHome(): string
    {
        $obUsuario = self::exigirAutenticacao();
        $roteiros  = Roteiro::listarPorUsuario($obUsuario->getIdUsuario());
        $hoje      = date('Y-m-d');

        $proximos  = array_values(array_filter($roteiros, fn(Roteiro $obRoteiro) => $obRoteiro->getDataFim() >= $hoje));
        $recentes  = $roteiros;
        usort($recentes, fn($a, $b) => strcmp((string) $b->getDataAtualizacao(), (string) $a->getDataAtualizacao()));

        $roteirosRecentes = empty($recentes)
            ? self::getEstadoVazio('Você ainda não tem roteiros', 'Comece criando um roteiro e vá adicionando transporte, hospedagem, guias e passeios.', '<a href="/roteiros/novo" class="btn-destaque mt-5">Criar meu primeiro roteiro</a>')
            : '<div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">' . RoteiroController::getCardsRoteiros(array_slice($recentes, 0, 3)) . '</div>';

        return self::renderizarPagina('usuario/home.php', [
            'primeiroNome'      => self::e(Texto::primeiroNome($obUsuario->getNome())),
            'totalRoteiros'     => count($roteiros),
            'totalFinalizados'  => count(array_filter($roteiros, fn(Roteiro $obRoteiro) => $obRoteiro->isFinalizado())),
            'valorPlanejado'    => self::moeda(array_sum(array_map(fn(Roteiro $obRoteiro) => $obRoteiro->getValorTotalEstimado(), $roteiros))),
            'proximaViagem'     => self::getDestaqueProximaViagem($proximos[0] ?? null),
            'roteirosRecentes'  => $roteirosRecentes,
            'linkVerTodos'      => count($roteiros) > 3 ? '<a href="/roteiros" class="text-sm font-semibold text-blue-700 hover:underline">Ver todos</a>' : ''
        ], 'inicio', 'Início');
    }

    private static function getDestaqueProximaViagem(?Roteiro $obRoteiro): string
    {
        if(!$obRoteiro){
            return '<p class="text-sm text-white/80">Nenhuma viagem marcada. Que tal planejar a próxima?</p>';
        }

        $diasParaViagem = Validador::diasEntre(date('Y-m-d'), $obRoteiro->getDataInicio());
        $contagem = match(true){
            $diasParaViagem > 1  => 'Faltam ' . $diasParaViagem . ' dias',
            $diasParaViagem == 1 => 'É amanhã!',
            $diasParaViagem == 0 => 'Começa hoje!',
            default              => 'Viagem em andamento'
        };

        return sprintf(
            '<a href="/roteiros/editar?id=%d" class="block rounded-2xl bg-white/15 p-4 ring-1 ring-white/25 backdrop-blur transition hover:bg-white/20">
                <p class="text-xs font-semibold uppercase tracking-wide text-orange-300">Próxima viagem · %s</p>
                <p class="mt-1 text-lg font-semibold">%s</p>
                <p class="text-sm text-white/80">%s · %s a %s</p>
            </a>',
            $obRoteiro->getIdRoteiro(),
            self::e($contagem),
            self::e($obRoteiro->getTitulo()),
            self::e($obRoteiro->getNomesDestinos()),
            Formatador::data($obRoteiro->getDataInicio()),
            Formatador::data($obRoteiro->getDataFim())
        );
    }

    // ------------------------------------------------------------------
    // Gerenciamento de perfil
    // ------------------------------------------------------------------

    public static function getPerfil(): string
    {
        $obUsuario = self::exigirAutenticacao();

        $infoAdmin = '';
        if($obUsuario instanceof Administrador){
            $infoAdmin = sprintf(
                '<div class="flex justify-between gap-4 py-3"><dt class="text-slate-500">Credencial</dt><dd class="font-medium text-blue-950">%s · nível %d</dd></div>',
                self::e($obUsuario->getCodigoCredencial()),
                $obUsuario->getNivelAcesso()
            );
        }

        return self::renderizarPagina('usuario/perfil.php', [
            'iniciais'      => self::e(Texto::iniciais($obUsuario->getNome())),
            'nome'          => self::e($obUsuario->getNome()),
            'email'         => self::e($obUsuario->getEmail()),
            'perfil'        => $obUsuario->isAdministrador() ? 'Administrador' : 'Viajante',
            'dataCadastro'  => Formatador::data($obUsuario->getDataCadastro()),
            'totalRoteiros' => count(Roteiro::listarPorUsuario($obUsuario->getIdUsuario())),
            'infoAdmin'     => $infoAdmin,
            'tamanhoSenha'  => Usuario::TAMANHO_MINIMO_SENHA
        ], '', 'Meu perfil');
    }

    public static function postPerfil(): void
    {
        $obUsuario = self::exigirAutenticacao();

        $nome  = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if(strlen($nome) < 3 or !Validador::email($email)){
            Session::setFlash('erro', 'Informe um nome com pelo menos 3 caracteres e um e-mail válido.');
            self::redirecionar('/perfil');
        }

        try{
            $obUsuario->atualizarDados($nome, $email);
            Session::setUsuario($obUsuario);
            Session::setFlash('sucesso', 'Seus dados foram atualizados.');
        }catch(RegraNegocioException $e){
            Session::setFlash('erro', $e->getMessage());
        }

        self::redirecionar('/perfil');
    }

    public static function postSenha(): void
    {
        $obUsuario = self::exigirAutenticacao();

        $senhaAtual = $_POST['senhaAtual'] ?? '';
        $novaSenha  = $_POST['novaSenha'] ?? '';
        $confirmar  = $_POST['confirmarSenha'] ?? '';

        if(strlen($novaSenha) < Usuario::TAMANHO_MINIMO_SENHA){
            Session::setFlash('erro', 'A nova senha deve ter pelo menos ' . Usuario::TAMANHO_MINIMO_SENHA . ' caracteres.');
            self::redirecionar('/perfil');
        }

        if($novaSenha !== $confirmar){
            Session::setFlash('erro', 'A confirmação precisa ser igual à nova senha.');
            self::redirecionar('/perfil');
        }

        try{
            $obUsuario->alterarSenha($senhaAtual, $novaSenha);
            Session::setFlash('sucesso', 'Senha alterada com sucesso.');
        }catch(RegraNegocioException $e){
            Session::setFlash('erro', $e->getMessage());
        }

        self::redirecionar('/perfil');
    }

    // ------------------------------------------------------------------
    // Recuperação de senha
    // ------------------------------------------------------------------

    public static function getRecuperarSenha(): string
    {
        if(self::verificarAutenticacao()){
            self::redirecionar('/home');
        }

        return self::renderizarPaginaAvulsa('usuario/recuperar-senha.php', ['feedback' => '', 'valorEmail' => ''], 'Recuperar senha');
    }

    public static function postRecuperarSenha(): string
    {
        $email = trim($_POST['email'] ?? '');

        if(!Validador::email($email)){
            return self::renderizarPaginaAvulsa('usuario/recuperar-senha.php', [
                'feedback'   => self::getAlerta('erro', 'Informe um e-mail válido.'),
                'valorEmail' => self::e($email)
            ], 'Recuperar senha');
        }

        // A resposta é a mesma exista ou não a conta (não revela e-mails cadastrados)
        Usuario::recuperarSenha($email);

        $mensagem = 'Se houver uma conta ativa com esse e-mail, enviamos um link para criar uma nova senha. O link vale por 1 hora.';
        $aviso    = '<p class="mt-2 text-xs opacity-80">Ambiente acadêmico: o envio de e-mail é simulado — a mensagem com o link fica registrada em <code>src/BD/table_email.json</code> e no terminal do servidor.</p>';

        return self::renderizarPaginaAvulsa('usuario/recuperar-senha.php', [
            'feedback'   => self::getAlerta('sucesso', $mensagem, $aviso),
            'valorEmail' => ''
        ], 'Recuperar senha');
    }

    public static function getRedefinirSenha(): string
    {
        $token = (string) ($_GET['token'] ?? '');

        if(!Usuario::getUsuarioPorTokenRecuperacao($token) instanceof Usuario){
            return self::renderizarLinkRecuperacaoInvalido();
        }

        return self::renderizarPaginaAvulsa('usuario/redefinir-senha.php', [
            'feedback'     => '',
            'token'        => self::e($token),
            'tamanhoSenha' => Usuario::TAMANHO_MINIMO_SENHA
        ], 'Nova senha');
    }

    public static function postRedefinirSenha(): string
    {
        $token     = (string) ($_POST['token'] ?? '');
        $novaSenha = $_POST['senha'] ?? '';
        $confirmar = $_POST['confirm'] ?? '';

        if(!Usuario::getUsuarioPorTokenRecuperacao($token) instanceof Usuario){
            return self::renderizarLinkRecuperacaoInvalido();
        }

        $erro = null;
        if(strlen($novaSenha) < Usuario::TAMANHO_MINIMO_SENHA) $erro = 'A senha deve ter pelo menos ' . Usuario::TAMANHO_MINIMO_SENHA . ' caracteres.';
        elseif($novaSenha !== $confirmar)                     $erro = 'A confirmação precisa ser igual à nova senha.';

        if($erro){
            return self::renderizarPaginaAvulsa('usuario/redefinir-senha.php', [
                'feedback'     => self::getAlerta('erro', $erro),
                'token'        => self::e($token),
                'tamanhoSenha' => Usuario::TAMANHO_MINIMO_SENHA
            ], 'Nova senha');
        }

        Usuario::redefinirSenha($token, $novaSenha);
        Session::setFlash('sucesso', 'Senha redefinida! Entre com a nova senha.');

        self::redirecionar('/login');
    }

    private static function renderizarLinkRecuperacaoInvalido(): string
    {
        return self::renderizarPaginaAvulsa('usuario/recuperar-senha.php', [
            'feedback'   => self::getAlerta('erro', 'Este link de recuperação é inválido ou expirou. Solicite um novo abaixo.'),
            'valorEmail' => ''
        ], 'Recuperar senha');
    }
}
