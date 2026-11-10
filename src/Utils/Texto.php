<?php

namespace Utils;

class Texto
{
    private const ACENTOS = [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
        'Á' => 'a', 'À' => 'a', 'Â' => 'a', 'Ã' => 'a', 'Ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'É' => 'e', 'È' => 'e', 'Ê' => 'e', 'Ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'Í' => 'i', 'Ì' => 'i', 'Î' => 'i', 'Ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'Ó' => 'o', 'Ò' => 'o', 'Ô' => 'o', 'Õ' => 'o', 'Ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'Ú' => 'u', 'Ù' => 'u', 'Û' => 'u', 'Ü' => 'u',
        'ç' => 'c', 'Ç' => 'c', 'ñ' => 'n', 'Ñ' => 'n',
    ];

    public static function normalizar(string $texto): string
    {
        $texto = strtolower(strtr(trim($texto), self::ACENTOS));
        return preg_replace('/\s+/', ' ', $texto);
    }

    public static function iguais(string $a, string $b): bool
    {
        return self::normalizar($a) === self::normalizar($b);
    }

    public static function contem(string $texto, string $busca): bool
    {
        return str_contains(self::normalizar($texto), self::normalizar($busca));
    }

    public static function primeiroNome(string $nome): string
    {
        return explode(' ', trim($nome))[0] ?? $nome;
    }

    public static function iniciais(string $nome): string
    {
        $partes = array_values(array_filter(explode(' ', trim($nome))));
        if(empty($partes)){
            return '?';
        }

        $primeira = self::primeiraLetra($partes[0]);
        $segunda  = count($partes) > 1 ? self::primeiraLetra(end($partes)) : self::primeiraLetra(substr($partes[0], strlen($primeira)));

        return strtoupper($primeira . $segunda);
    }

    private static function primeiraLetra(string $palavra): string
    {
        return preg_match('/./u', $palavra, $letra) ? $letra[0] : '';
    }
}
