<?php

namespace Controllers;

use Models\Roteiro;
use Models\Usuario;
use Session\Session;
use Utils\Validador;
use Views\Icones;

class CompartilhamentoController extends Controller
{
    public static function getVisualizar(): string
    {
        $obRoteiro = Roteiro::getPorTokenCompartilhamento((string) ($_GET['token'] ?? ''));

        if(!$obRoteiro instanceof Roteiro){
            return ErroController::exibir(404, 'Link inválido', 'Não encontramos nenhum roteiro para este link. Confira se o endereço foi copiado por completo.');
        }

        $obCompartilhamento = $obRoteiro->getCompartilhamento();
        $obDono             = Usuario::getUsuarioPorId($obRoteiro->getIdUsuario());

        if(!$obCompartilhamento->isValido() or !$obDono instanceof Usuario or !$obDono->isAtivo()){
            return ErroController::exibir(410, 'Link indisponível', 'Este link de compartilhamento expirou ou foi desativado pelo autor do roteiro.');
        }

        $expiracao     = $obCompartilhamento->getDataExpiracao();
        $diasRestantes = $expiracao ? max(0, Validador::diasEntre(date('Y-m-d'), substr($expiracao, 0, 10))) : null;
        $ehDono        = Session::getIdUsuarioLogado() === $obRoteiro->getIdUsuario();

        return self::renderizarPaginaAvulsa('roteiro/compartilhado.php', RoteiroController::getResumoViagem($obRoteiro) + [
            'titulo'        => self::e($obRoteiro->getTitulo()),
            'autor'         => self::e($obDono->getNome()),
            'total'         => self::moeda($obRoteiro->calcularValorTotal()),
            'porPessoa'     => self::moeda($obRoteiro->calcularValorPorPessoa()),
            'totaisPorTipo' => RoteiroController::getTotaisPorTipoHtml($obRoteiro),
            'cronograma'    => RoteiroController::getCronogramaHtml($obRoteiro, false),
            'validade'      => $diasRestantes === null ? 'Link sem data de expiração' : 'Link válido por ' . $diasRestantes . ($diasRestantes === 1 ? ' dia' : ' dias'),
            'voltar'        => $ehDono ? '<a href="/roteiros/editar?id=' . $obRoteiro->getIdRoteiro() . '" class="btn-vidro ml-auto !h-9 !px-3 !text-[13px]">Voltar à prévia</a>' : '',
            'iconeOlho'     => Icones::get('olho', 'h-[18px] w-[18px] flex-none')
        ], $obRoteiro->getTitulo());
    }
}
