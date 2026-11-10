<?php

namespace Controllers;

use Exceptions\RegraNegocioException;
use Models\Administrador;
use Models\Destino;
use Models\Roteiro;
use Session\Session;
use Utils\Formatador;
use Utils\Texto;
use Utils\Validador;
use Views\Icones;
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

    private static function validarCadastro(array $dados, bool $exigirConfirmacao): ?string
    {
        $camposObrigatorios = $exigirConfirmacao ? ['nome', 'email', 'senha', 'confirm'] : ['nome', 'email', 'senha'];

        $validacaoCampos = self::validarCamposObrigatorios($dados, $camposObrigatorios);
        if(!empty($validacaoCampos)){
            $nomesCampos = array_map(fn($campo) => self::NOMES_CAMPOS[$campo], array_unique($validacaoCampos));
            return 'Preencha corretamente: ' . implode(', ', $nomesCampos) . '.';
        }

        if(strlen(trim($dados['nome'])) < 3){
            return 'Informe seu nome com pelo menos 3 caracteres.';
        }

        if(Usuario::getUsuario($dados['email']) instanceof Usuario){
            return 'Já existe um usuário com esse e-mail.';
        }

        if(strlen($dados['senha']) < Usuario::TAMANHO_MINIMO_SENHA){
            return 'A senha deve ter pelo menos ' . Usuario::TAMANHO_MINIMO_SENHA . ' caracteres.';
        }

        if($exigirConfirmacao and $dados['senha'] !== $dados['confirm']){
            return 'O campo de confirmação deve ser igual à senha.';
        }

        return null;
    }

    public static function cadastrar(): string
    {
        $erro = self::validarCadastro($_POST, true);
        if($erro !== null){
            return self::retornarFeedbackErroCadastro($erro);
        }

        $obUsuario = new Usuario();
        if(!$obUsuario->cadastrar($_POST)){
            return self::retornarFeedbackErroCadastro('Ocorreu um erro ao cadastrar o usuário');
        }

        $mensagemSucesso = ViewRenderer::getComponente(['mensagem' => 'Usuário cadastrado com sucesso. Agora é só entrar com seu e-mail e senha.'], self::$pathComponentsUsuario, 'sucesso-cadastro.php');
        return self::renderizarLogin(['feedback' => $mensagemSucesso, 'valorEmail' => self::e($obUsuario->getEmail())]);
    }

    public static function postCadastro(): string
    {
        $erro = empty($_POST['aceiteLgpd'])
            ? 'Para criar a conta, aceite os termos de uso e a política de privacidade.'
            : self::validarCadastro($_POST, false);

        if($erro !== null){
            return self::renderizarLanding($_POST, $erro);
        }

        $obUsuario = new Usuario();
        if(!$obUsuario->cadastrar($_POST)){
            return self::renderizarLanding($_POST, 'Não foi possível criar sua conta agora. Tente novamente.');
        }

        Session::setFlash('sucesso', 'Conta criada! Agora é só entrar com seu e-mail e senha.');
        self::redirecionar('/login');
    }

    private static function renderizarLanding(array $valores, string $erro): string
    {
        return self::renderizarPaginaAvulsa('site/landing.php', [
            'valorNome'           => self::e($valores['nome'] ?? ''),
            'valorEmail'          => self::e($valores['email'] ?? ''),
            'aceiteMarcado'       => empty($valores['aceiteLgpd']) ? '' : 'checked',
            'erroCadastro'        => $erro === '' ? '' : '<div class="erro-campos">' . Icones::get('alerta', 'h-[18px] w-[18px] flex-none') . '<span>' . self::e($erro) . '</span></div>',
            'rolarParaCadastro'   => $erro === '' ? '' : '<script>document.getElementById("criar-conta").scrollIntoView();</script>',
            'iconeSeta'           => Icones::get('seta', 'h-[18px] w-[18px]'),
            'iconeCheck'          => Icones::get('check', 'h-[18px] w-[18px] flex-none'),
            'iconeBaixo'          => Icones::get('baixo', 'h-5 w-5'),
            'iconeOnibus'         => Icones::get('onibus', 'h-[17px] w-[17px]'),
            'iconeCama'           => Icones::get('cama', 'h-[17px] w-[17px]'),
            'iconePessoas'        => Icones::get('pessoas', 'h-[17px] w-[17px]'),
            'iconeAviaoGrande'    => Icones::get('aviao', 'h-[26px] w-[26px]'),
            'iconeCamaGrande'     => Icones::get('cama', 'h-[26px] w-[26px]'),
            'iconeCarteiraGrande' => Icones::get('carteira', 'h-[26px] w-[26px]')
        ], 'Planeje sua viagem');
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

        if(!$obUsuario->isAtivo()){
            return self::retornarFeedbackErroLogin('Conta inativa', 'Sua conta foi inativada por um administrador e não pode acessar os roteiros.', $email);
        }

        session_regenerate_id(true);
        Session::setUsuario($obUsuario);

        if(!empty($_POST['lembrar'])){
            Session::lembrarUsuario();
        }

        self::redirecionar($obUsuario->obterTelaInicial());
    }

    public static function logout(): void
    {
        Session::encerrar();
        Session::setFlash('sucesso', 'Você saiu da sua conta. Até a próxima viagem!');

        header('Location: /login');
        exit();
    }

    public static function getRaiz(): string
    {
        if(self::verificarAutenticacao()){
            self::redirecionar(self::exigirAutenticacao()->obterTelaInicial());
        }

        return self::renderizarLanding([], '');
    }

    public static function getLogin(): string
    {
        if(self::verificarAutenticacao()){
            self::redirecionar(self::exigirAutenticacao()->obterTelaInicial());
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

    public static function getHome(): string
    {
        return self::renderizarHome([], []);
    }

    public static function renderizarHome(array $valores, array $erros): string
    {
        $obUsuario = self::exigirAutenticacao();
        $roteiros  = Roteiro::listarPorUsuario($obUsuario->getIdUsuario());
        $proximos  = array_values(array_filter($roteiros, fn(Roteiro $obRoteiro) => $obRoteiro->getDataFim() >= date('Y-m-d')));

        $contarStatus = fn(string $status) => count(array_filter($roteiros, fn(Roteiro $obRoteiro) => $obRoteiro->getStatus() === $status));

        return self::renderizarPagina('usuario/home.php', [
            'primeiroNome'      => self::e(Texto::primeiroNome($obUsuario->getNome())),
            'proximaViagem'     => self::getDestaqueProximaViagem($proximos[0] ?? null),
            'feedbackPlano'     => empty($erros) ? '' : '<div class="erro-campos">' . Icones::get('alerta', 'h-[18px] w-[18px] flex-none') . '<span>' . self::e(implode(' ', $erros)) . '</span></div>',
            'valorTitulo'       => self::e($valores['titulo'] ?? ''),
            'valorOrigem'       => self::e($valores['origem'] ?? ''),
            'valorDestino'      => self::e($valores['destino'] ?? ''),
            'valorDataInicio'   => self::e($valores['dataInicio'] ?? ''),
            'valorDataFim'      => self::e($valores['dataFim'] ?? ''),
            'valorPessoas'      => self::e($valores['quantidadePessoas'] ?? '2'),
            'valorOrcamento'    => self::e($valores['orcamento'] ?? ''),
            'dataMinima'        => date('Y-m-d'),
            'datalistDestinos'  => implode('', array_map(fn(Destino $obDestino) => '<option value="' . self::e($obDestino->getCidade() . ' - ' . $obDestino->getEstado()) . '"></option>', Destino::listar())),
            'totalPlanejamento' => $contarStatus(Roteiro::STATUS_EM_PLANEJAMENTO),
            'totalProntos'      => $contarStatus(Roteiro::STATUS_PRONTO_PARA_VIAJAR),
            'totalRealizadas'   => $contarStatus(Roteiro::STATUS_VIAGEM_REALIZADA),
            'iconeSeta'         => Icones::get('seta', 'h-[18px] w-[18px]'),
            'iconeLista'        => Icones::get('lista', 'h-6 w-6'),
            'iconeCheck'        => Icones::get('check', 'h-6 w-6'),
            'iconeMapa'         => Icones::get('mapa', 'h-6 w-6')
        ], 'inicio', 'Início');
    }

    private static function getDestaqueProximaViagem(?Roteiro $obRoteiro): string
    {
        if(!$obRoteiro){
            return '<div class="flex w-full flex-col gap-1.5 rounded-xl border border-white/30 bg-[rgba(15,29,54,.3)] px-5 py-[18px] text-white backdrop-blur-[3px]">
                <span class="text-xs font-extrabold uppercase tracking-[.08em] text-laranja-claro">Próxima viagem</span>
                <b class="font-display text-[19px]">Nenhuma viagem marcada</b>
                <span class="text-[13.5px] text-[#dbe3f1]">Preencha o quadro abaixo e comece a planejar.</span>
            </div>';
        }

        $diasParaViagem = Validador::diasEntre(date('Y-m-d'), $obRoteiro->getDataInicio());
        $contagem = match(true){
            $diasParaViagem > 1  => 'Faltam ' . $diasParaViagem . ' dias',
            $diasParaViagem == 1 => 'É amanhã!',
            $diasParaViagem == 0 => 'Começa hoje!',
            default              => 'Viagem em andamento'
        };

        return sprintf(
            '<a href="/roteiros/editar?id=%d" class="flex w-full flex-col gap-1.5 rounded-xl border border-white/30 bg-[rgba(15,29,54,.3)] px-5 py-[18px] text-left text-white backdrop-blur-[3px] transition hover:bg-[rgba(15,29,54,.45)]">
                <span class="text-xs font-extrabold uppercase tracking-[.08em] text-laranja-claro">Próxima viagem · %s</span>
                <b class="font-display text-[19px]">%s</b>
                <span class="text-[13.5px] text-[#dbe3f1]">%s · %s – %s · %d %s</span>
                <span class="mt-1 flex items-baseline justify-between border-t border-white/20 pt-2.5 text-[13px] text-[#dbe3f1]">Valor total <strong class="font-display text-xl text-white">%s</strong></span>
            </a>',
            $obRoteiro->getIdRoteiro(),
            self::e($contagem),
            self::e($obRoteiro->getTitulo()),
            self::e($obRoteiro->getNomesDestinos()),
            Formatador::data($obRoteiro->getDataInicio()),
            Formatador::data($obRoteiro->getDataFim()),
            $obRoteiro->getQuantidadePessoas(),
            $obRoteiro->getQuantidadePessoas() > 1 ? 'pessoas' : 'pessoa',
            self::moeda($obRoteiro->calcularValorTotal())
        );
    }

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
            $obUsuario->atualizarDados($nome, $email, $obUsuario->isReceberEmails());
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
