<?php

namespace Views;

use Session\Session;

class ViewRenderer
{
    public static function getLayout(array $variaveisLayout, array $paths, string $arquivo, array $variaveisEstrutura = []): string
    {
        $conteudo = self::getComponente($variaveisLayout, $paths['layout'], $arquivo);

        return self::getEstrutura($paths['estrutura'], $conteudo, $variaveisEstrutura);
    }

    public static function getComponente(array $variaveisLayout, string $pathComponente, string $arquivo): string
    {
        return self::replace($variaveisLayout, file_get_contents($pathComponente . $arquivo));
    }

    public static function getLista(array $listaVariaveis, string $pathComponente, string $arquivo): string
    {
        $template = file_get_contents($pathComponente . $arquivo);

        return implode(PHP_EOL, array_map(fn($variaveis) => self::replace($variaveis, $template), $listaVariaveis));
    }

    private static function getEstrutura(string $path, string $conteudo, array $variaveisEstrutura): string
    {
        $variaveisEstrutura += ['titulo' => 'Mundear', 'tema' => file_get_contents(__DIR__ . '/layouts/tema.php')];

        $pagina = self::replace($variaveisEstrutura, file_get_contents($path . 'header.php')) .
                  $conteudo .
                  self::replace($variaveisEstrutura, file_get_contents($path . 'footer.php'));

        return preg_replace('/\{\{\w+\}\}/', '', $pagina);
    }

    private static function replace(array $variaveisLayout, string $conteudo): string
    {
        $variaveisLayout += ['csrf' => '<input type="hidden" name="_csrf" value="' . Session::getTokenCsrf() . '">'];

        foreach($variaveisLayout as $key => $value){
            $conteudo = str_replace('{{'.$key.'}}', (string) $value, $conteudo);
        }

        return $conteudo;
    }
}
