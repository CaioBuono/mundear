<?php

namespace Views;

class ViewRenderer
{
    public static function getLayout(array $variaveisLayout, array $paths, string $arquivo)
    {
        $pathLayout = $paths['layout'] . $arquivo;

        return self::getEstrutura(
            $paths['estrutura'],
            self::replace($variaveisLayout, file_get_contents($pathLayout)),
        );
    }

    private static function getEstrutura(string $path, string $conteudo)
    {
        return file_get_contents($path . 'header.php') .
               $conteudo .
               file_get_contents($path . 'footer.php');
    }

    private static function replace(array $variaveisLayout, string $conteudo)
    {
        if(empty($variaveisLayout)) return $conteudo;

        foreach($variaveisLayout as $key => $value){
            $conteudo = str_replace('{{'.$key.'}}', $value, $conteudo);
        }

        return $conteudo;
    }
}