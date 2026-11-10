<?php

namespace Controllers;

use Models\Roteiro;
use Models\Usuario;
use Utils\Formatador;
use Utils\Texto;
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

        return self::renderizarPaginaAvulsa('roteiro/compartilhado.php', [
            'titulo'          => self::e($obRoteiro->getTitulo()),
            'autor'           => self::e(Texto::primeiroNome($obDono->getNome())),
            'destinos'        => self::e($obRoteiro->getNomesDestinos()),
            'periodo'         => Formatador::data($obRoteiro->getDataInicio()) . ' a ' . Formatador::data($obRoteiro->getDataFim()),
            'dias'            => $obRoteiro->getQuantidadeDias() . ($obRoteiro->getQuantidadeDias() > 1 ? ' dias' : ' dia'),
            'total'           => self::moeda($obRoteiro->getValorTotalEstimado()),
            'totaisPorTipo'   => RoteiroController::getTotaisPorTipoHtml($obRoteiro),
            'cronograma'      => RoteiroController::getCronogramaHtml($obRoteiro, false),
            'validade'        => $obCompartilhamento->getDataExpiracao() ? 'Link válido até ' . Formatador::data($obCompartilhamento->getDataExpiracao()) : 'Link sem data de expiração',
            'atualizacao'     => Formatador::dataHora($obRoteiro->getDataAtualizacao()),
            'iconePin'        => Icones::get('pin', 'h-4 w-4 text-slate-400'),
            'iconeCalendario' => Icones::get('calendario', 'h-4 w-4 text-slate-400'),
            'iconeOlho'       => Icones::get('olho', 'h-4 w-4')
        ], $obRoteiro->getTitulo());
    }
}
