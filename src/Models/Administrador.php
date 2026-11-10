<?php

namespace Models;

use Exceptions\OperacaoNaoPermitidaException;
use Exceptions\RegistroNaoEncontradoException;
use Utils\Formatador;

class Administrador extends Usuario
{
    private ?string $codigoCredencial = null;

    private int $nivelAcesso = 1;

    public function getCodigoCredencial(): ?string { return $this->codigoCredencial; }
    public function getNivelAcesso(): int { return $this->nivelAcesso; }

    public function isAdministrador(): bool
    {
        return true;
    }

    public function obterTelaInicial(): string
    {
        return '/admin';
    }

    public function cadastrarNovoGuia(GuiaTuristico $guia): bool
    {
        return $guia->getIdGuia() === null && $guia->salvar();
    }

    public function alterarGuia(GuiaTuristico $guia): bool
    {
        return $guia->getIdGuia() !== null && $guia->salvar();
    }

    public function inativarGuia(int $idGuia): bool
    {
        return $this->alterarStatusGuia($idGuia, false);
    }

    public function reativarGuia(int $idGuia): bool
    {
        return $this->alterarStatusGuia($idGuia, true);
    }

    private function alterarStatusGuia(int $idGuia, bool $ativo): bool
    {
        $obGuia = GuiaTuristico::getPorId($idGuia);
        if(!$obGuia instanceof GuiaTuristico){
            return false;
        }

        $obGuia->setAtivo($ativo);
        return $obGuia->salvar();
    }

    public function excluirGuia(int $idGuia): bool
    {
        $obGuia = GuiaTuristico::getPorId($idGuia);
        if(!$obGuia instanceof GuiaTuristico){
            throw new RegistroNaoEncontradoException('Guia não encontrado.');
        }

        if($obGuia->isAtivo()){
            throw new OperacaoNaoPermitidaException('Inative ' . $obGuia->getNome() . ' antes de excluir.', 'RN14');
        }

        $quantidadeRoteiros = Roteiro::contarRoteirosComGuia($idGuia);
        if($quantidadeRoteiros > 0){
            throw new OperacaoNaoPermitidaException(sprintf(
                '%s está em %d roteiro(s) e não pode ser excluído(a). Use a opção "Inativar".',
                $obGuia->getNome(),
                $quantidadeRoteiros
            ), 'RN14');
        }

        return $obGuia->excluir();
    }

    public function alterarStatusUsuario(int $idUsuario, bool $ativo): bool
    {
        if($idUsuario === $this->getIdUsuario()){
            throw new OperacaoNaoPermitidaException('Você não pode inativar a sua própria conta.', 'RN13');
        }

        $obUsuario = Usuario::getUsuarioPorId($idUsuario);
        if(!$obUsuario instanceof Usuario){
            return false;
        }

        $obUsuario->alterarStatus($ativo);
        return true;
    }

    public function obterDadosRelatorio(): array
    {
        $usuarios  = Usuario::listar();
        $roteiros  = Roteiro::listarTodos();
        $guias     = GuiaTuristico::pesquisar('', '', null);
        $destinos  = [];
        $itensTipo = array_fill_keys(array_keys(ItemRoteiro::getRotulosTipo()), 0);

        foreach($roteiros as $obRoteiro){
            foreach($obRoteiro->getDestinos() as $obDestino){
                $destinos[$obDestino->getCidade()] = ($destinos[$obDestino->getCidade()] ?? 0) + 1;
            }

            foreach($obRoteiro->getItens() as $obItem){
                $itensTipo[$obItem->getTipoServico()]++;
            }
        }

        arsort($destinos);

        return [
            'usuariosTotal'      => count($usuarios),
            'usuariosAtivos'     => count(array_filter($usuarios, fn($obUsuario) => $obUsuario->isAtivo())),
            'roteirosTotal'      => count($roteiros),
            'roteirosFinalizados'=> count(array_filter($roteiros, fn($obRoteiro) => $obRoteiro->isFinalizado())),
            'linksAtivos'        => count(array_filter($roteiros, fn($obRoteiro) => $obRoteiro->possuiLinkAtivo())),
            'valorPlanejado'     => array_sum(array_map(fn($obRoteiro) => $obRoteiro->getValorTotalEstimado(), $roteiros)),
            'guiasTotal'         => count($guias),
            'guiasAtivos'        => count(array_filter($guias, fn($obGuia) => $obGuia->isAtivo())),
            'destinosPopulares'  => array_slice($destinos, 0, 5, true),
            'itensPorTipo'       => $itensTipo
        ];
    }

    public function gerarRelatorio(): string
    {
        $dados = $this->obterDadosRelatorio();

        return sprintf(
            'Relatório gerado em %s: %d usuário(s) (%d ativo(s)), %d roteiro(s) criados, %d finalizado(s) e %d link(s) de compartilhamento ativo(s). Valor total planejado: %s. Guias cadastrados: %d (%d ativo(s)).',
            Formatador::dataHora(date('Y-m-d H:i:s')),
            $dados['usuariosTotal'],
            $dados['usuariosAtivos'],
            $dados['roteirosTotal'],
            $dados['roteirosFinalizados'],
            $dados['linksAtivos'],
            Formatador::moeda($dados['valorPlanejado']),
            $dados['guiasTotal'],
            $dados['guiasAtivos']
        );
    }

    protected function preencher(array $registro): void
    {
        parent::preencher($registro);

        $this->codigoCredencial = $registro['codigoCredencial'] ?? null;
        $this->nivelAcesso      = (int) ($registro['nivelAcesso'] ?? 1);
    }

    protected function toArray(): array
    {
        return parent::toArray() + [
            'codigoCredencial' => $this->codigoCredencial,
            'nivelAcesso'      => $this->nivelAcesso
        ];
    }
}
