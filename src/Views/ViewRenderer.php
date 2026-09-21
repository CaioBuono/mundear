<?php

namespace Views;

class ViewRenderer
{
    public static function getLayout(array $variaveisLayout, array $paths, string $arquivo, bool $isAuth = false): string
    {
        $pathLayout = $paths['layout'] . $arquivo;

        return self::getEstrutura(
            $paths['estrutura'],
            self::replace($variaveisLayout, file_get_contents($pathLayout)),
            $isAuth
        );
    }

    public static function getHeaderUsuarioAutenticado(array $dadosUsuario, string $path): string
    {
        $perfilUsuario = self::getPerfilUsuario($dadosUsuario, $path);

        return self::getComponente(
            ['perfilUsuario' => $perfilUsuario],
            PATH_ESTRUTURA_PADRAO,
            'header.php'
        );
    }

    public static function getComponente(array $variaveisLayout, string $pathComponente, string $arquivo): string
    {
        return self::replace($variaveisLayout, file_get_contents($pathComponente . $arquivo));
    }

    private static function getEstrutura(string $path, string $conteudo, bool $isAuth = false): string
    {
        if($isAuth){
            return $conteudo . file_get_contents($path . 'footer.php');
        }

        return file_get_contents($path . 'header.php') .
               $conteudo .
               file_get_contents($path . 'footer.php');
    }

    private static function replace(array $variaveisLayout, string $conteudo): string
    {
        if(empty($variaveisLayout)) return $conteudo;

        foreach($variaveisLayout as $key => $value){
            $conteudo = str_replace('{{'.$key.'}}', $value, $conteudo);
        }

        return $conteudo;
    }

    private static function getPerfilUsuario(array $dadosUsuario, string $pathComponent): string
    {
        $varLayout = [
            'icone'       => $dadosUsuario['inicial'],
            'nomeUsuario' => $dadosUsuario['nome']
        ];

        return self::getComponente(
            $varLayout,
            $pathComponent,
            'usuario-perfil.php'
        );
    }
}