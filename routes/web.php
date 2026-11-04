<?php

use Controllers\AdminController;
use Controllers\CompartilhamentoController;
use Controllers\ErroController;
use Controllers\HospedagemController;
use Controllers\RoteiroController;
use Controllers\TransporteController;
use Controllers\TurismoController;
use Controllers\UsuarioController;
use Session\Session;

//MAPA DE ROTAS: [MÉTODO][PATH] => [CONTROLLER, MÉTODO]
$rotas = [
    'GET' => [
        '/'                   => [UsuarioController::class, 'getRaiz'],
        '/login'              => [UsuarioController::class, 'getLogin'],
        '/logout'             => [UsuarioController::class, 'logout'],
        '/recuperar-senha'    => [UsuarioController::class, 'getRecuperarSenha'],
        '/redefinir-senha'    => [UsuarioController::class, 'getRedefinirSenha'],
        '/home'               => [UsuarioController::class, 'getHome'],
        '/perfil'             => [UsuarioController::class, 'getPerfil'],

        '/transporte'         => [TransporteController::class, 'getBusca'],
        '/hospedagem'         => [HospedagemController::class, 'getBusca'],
        '/turismo'            => [TurismoController::class, 'getBusca'],

        '/roteiros'           => [RoteiroController::class, 'getLista'],
        '/roteiros/novo'      => [RoteiroController::class, 'getNovo'],
        '/roteiros/editar'    => [RoteiroController::class, 'getEditar'],

        '/compartilhado'      => [CompartilhamentoController::class, 'getVisualizar'],

        '/admin'              => [AdminController::class, 'getRelatorio'],
        '/admin/guias'        => [AdminController::class, 'getGuias'],
        '/admin/guias/novo'   => [AdminController::class, 'getNovoGuia'],
        '/admin/guias/editar' => [AdminController::class, 'getEditarGuia'],
        '/admin/usuarios'     => [AdminController::class, 'getUsuarios'],
    ],
    'POST' => [
        '/login'                     => [UsuarioController::class, 'login'],
        '/recuperar-senha'           => [UsuarioController::class, 'postRecuperarSenha'],
        '/redefinir-senha'           => [UsuarioController::class, 'postRedefinirSenha'],
        '/perfil'                    => [UsuarioController::class, 'postPerfil'],
        '/perfil/senha'              => [UsuarioController::class, 'postSenha'],

        '/roteiros/novo'             => [RoteiroController::class, 'postNovo'],
        '/roteiros/editar'           => [RoteiroController::class, 'postEditar'],
        '/roteiros/excluir'          => [RoteiroController::class, 'postExcluir'],
        '/roteiros/finalizar'        => [RoteiroController::class, 'postFinalizar'],
        '/roteiros/revogar-link'     => [RoteiroController::class, 'postRevogarLink'],
        '/roteiros/itens/adicionar'  => [RoteiroController::class, 'postAdicionarItem'],
        '/roteiros/itens/remover'    => [RoteiroController::class, 'postRemoverItem'],
        '/roteiros/itens/quantidade' => [RoteiroController::class, 'postQuantidadeItem'],

        '/admin/guias/novo'          => [AdminController::class, 'postNovoGuia'],
        '/admin/guias/editar'        => [AdminController::class, 'postEditarGuia'],
        '/admin/guias/status'        => [AdminController::class, 'postStatusGuia'],
        '/admin/guias/excluir'       => [AdminController::class, 'postExcluirGuia'],
        '/admin/usuarios/status'     => [AdminController::class, 'postStatusUsuario'],
    ],
];

//PATH REQUISITADO
$metodo = $_SERVER['REQUEST_METHOD'];
$path   = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/', '/') ?: '/';

try {
    if(!isset($rotas[$metodo][$path])){
        $existeEmOutroMetodo = isset($rotas['GET'][$path]) || isset($rotas['POST'][$path]);

        echo $existeEmOutroMetodo
            ? ErroController::exibir(405, 'Ação não permitida', 'Esta página não aceita esse tipo de requisição.')
            : ErroController::exibir(404, 'Página não encontrada', 'O endereço acessado não existe ou foi removido.');
        return;
    }

    //PROTEÇÃO CSRF EM TODOS OS FORMULÁRIOS
    if($metodo === 'POST' and !Session::validarTokenCsrf($_POST['_csrf'] ?? '')){
        echo ErroController::exibir(419, 'Sessão expirada', 'Por segurança, volte à página anterior, recarregue-a e tente novamente.');
        return;
    }

    echo call_user_func($rotas[$metodo][$path]);
} catch (\Throwable $erro) {
    // RNF04: registra o detalhe técnico no log e mostra mensagem amigável
    error_log('[Mundear] ' . $erro);
    echo ErroController::exibir(500, 'Algo deu errado', 'Não foi possível concluir sua solicitação agora. Tente novamente em instantes.');
}
