<?php

namespace Views;

use Session\Session;

class ViewRenderer
{
    /**
     * Monta uma página completa: estrutura (header/footer) + conteúdo da view.
     */
    public static function getLayout(array $variaveisLayout, array $paths, string $arquivo, array $variaveisEstrutura = []): string
    {
        $conteudo = self::getComponente($variaveisLayout, $paths['layout'], $arquivo);

        return self::getEstrutura($paths['estrutura'], $conteudo, $variaveisEstrutura);
    }

    public static function getComponente(array $variaveisLayout, string $pathComponente, string $arquivo): string
    {
        return self::replace($variaveisLayout, file_get_contents($pathComponente . $arquivo));
    }

    /**
     * Renderiza o mesmo componente uma vez para cada conjunto de variáveis.
     */
    public static function getLista(array $listaVariaveis, string $pathComponente, string $arquivo): string
    {
        $template = file_get_contents($pathComponente . $arquivo);

        return implode(PHP_EOL, array_map(fn($variaveis) => self::replace($variaveis, $template), $listaVariaveis));
    }

    private static function getEstrutura(string $path, string $conteudo, array $variaveisEstrutura): string
    {
        $variaveisEstrutura += ['titulo' => 'Mundear'];

        $pagina = self::replace($variaveisEstrutura, file_get_contents($path . 'header.php')) .
                  $conteudo .
                  self::replace($variaveisEstrutura, file_get_contents($path . 'footer.php'));

        // Remove placeholders que não receberam valor
        return preg_replace('/\{\{\w+\}\}/', '', $pagina);
    }

    private static function replace(array $variaveisLayout, string $conteudo): string
    {
        // {{csrf}} vira automaticamente o campo oculto com o token anti-CSRF
        $variaveisLayout += ['csrf' => '<input type="hidden" name="_csrf" value="' . Session::getTokenCsrf() . '">'];

        foreach($variaveisLayout as $key => $value){
            $conteudo = str_replace('{{'.$key.'}}', (string) $value, $conteudo);
        }

        return $conteudo;
    }
}
