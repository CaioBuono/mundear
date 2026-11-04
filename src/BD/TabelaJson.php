<?php

namespace BD;

/**
 * Acesso genérico às "tabelas" do banco NoSQL (um arquivo JSON por coleção).
 *
 * Cada arquivo src/BD/table_{nome}.json guarda uma lista de documentos.
 * A classe cuida de leitura, geração de id incremental e gravação com trava
 * de arquivo (LOCK_EX) para evitar que duas requisições sobrescrevam dados.
 */
class TabelaJson
{
    private string $caminhoArquivo;

    private string $chavePrimaria;

    public function __construct(string $nomeTabela, string $chavePrimaria)
    {
        $this->caminhoArquivo = __DIR__ . '/table_' . $nomeTabela . '.json';
        $this->chavePrimaria  = $chavePrimaria;
    }

    /**
     * Lista todos os documentos, opcionalmente filtrados por uma função.
     */
    public function listar(?callable $filtro = null): array
    {
        $registros = $this->ler();

        return $filtro ? array_values(array_filter($registros, $filtro)) : $registros;
    }

    public function buscarPorId(int $id): ?array
    {
        foreach($this->ler() as $registro){
            if((int) ($registro[$this->chavePrimaria] ?? 0) === $id){
                return $registro;
            }
        }

        return null;
    }

    public function buscarPrimeiro(callable $filtro): ?array
    {
        foreach($this->ler() as $registro){
            if($filtro($registro)){
                return $registro;
            }
        }

        return null;
    }

    /**
     * Insere um documento e devolve o id gerado (maior id + 1).
     */
    public function inserir(array $registro): int
    {
        $registros = $this->ler();
        $ids       = array_map('intval', array_column($registros, $this->chavePrimaria));
        $novoId    = empty($ids) ? 1 : max($ids) + 1;

        unset($registro[$this->chavePrimaria]);
        $registros[] = [$this->chavePrimaria => $novoId] + $registro;

        $this->salvar($registros);

        return $novoId;
    }

    /**
     * Mescla os dados informados no documento com o id indicado.
     */
    public function atualizar(int $id, array $dados): bool
    {
        $registros = $this->ler();

        foreach($registros as $indice => $registro){
            if((int) ($registro[$this->chavePrimaria] ?? 0) === $id){
                $registros[$indice] = array_merge($registro, $dados, [$this->chavePrimaria => $id]);
                $this->salvar($registros);
                return true;
            }
        }

        return false;
    }

    public function excluir(int $id): bool
    {
        $registros = $this->ler();
        $restantes = array_filter($registros, fn($registro) => (int) ($registro[$this->chavePrimaria] ?? 0) !== $id);

        if(count($restantes) === count($registros)){
            return false;
        }

        $this->salvar($restantes);
        return true;
    }

    private function ler(): array
    {
        if(!is_file($this->caminhoArquivo)){
            return [];
        }

        $conteudo = file_get_contents($this->caminhoArquivo);
        $dados    = json_decode($conteudo === false || trim($conteudo) === '' ? '[]' : $conteudo, true);

        if(!is_array($dados)){
            throw new \RuntimeException('Arquivo de dados inválido: ' . basename($this->caminhoArquivo));
        }

        return $dados;
    }

    private function salvar(array $registros): void
    {
        // Caracteres com codificação inválida são substituídos em vez de impedir a gravação
        $json = json_encode(
            array_values($registros),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );

        if($json === false){
            throw new \RuntimeException('Falha ao converter ' . basename($this->caminhoArquivo) . ' para JSON: ' . json_last_error_msg());
        }

        if(file_put_contents($this->caminhoArquivo, $json . PHP_EOL, LOCK_EX) === false){
            throw new \RuntimeException('Não foi possível gravar o arquivo ' . basename($this->caminhoArquivo));
        }
    }
}
