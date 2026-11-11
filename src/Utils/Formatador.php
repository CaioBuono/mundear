<?php

namespace Utils;

class Formatador
{
    private const DIAS_SEMANA = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];

    private const DIAS_SEMANA_CURTO = ['dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sáb'];

    private const MESES_CURTO = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];

    public static function moeda(float $valor): string
    {
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }

    public static function data(?string $data): string
    {
        return $data ? date('d/m/Y', strtotime($data)) : '';
    }

    public static function dataCurta(?string $data): string
    {
        return $data ? date('d/m', strtotime($data)) : '';
    }

    public static function mesAno(?string $data): string
    {
        if(!$data) return '–';

        $momento = strtotime($data);

        return self::MESES_CURTO[(int) date('n', $momento) - 1] . '/' . date('Y', $momento);
    }

    public static function dataHora(?string $dataHora): string
    {
        return $dataHora ? date('d/m/Y \à\s H:i', strtotime($dataHora)) : '';
    }

    public static function hora(?string $dataHora): string
    {
        return $dataHora ? date('H:i', strtotime($dataHora)) : '';
    }

    public static function diaSemana(string $data): string
    {
        return self::DIAS_SEMANA[(int) date('w', strtotime($data))];
    }

    public static function diaSemanaCurto(string $data): string
    {
        return self::DIAS_SEMANA_CURTO[(int) date('w', strtotime($data))];
    }

    public static function nomeDiaSemana(int $diaSemana): string
    {
        return self::DIAS_SEMANA[$diaSemana] ?? '';
    }

    public static function duracao(int $minutos): string
    {
        $horas = intdiv($minutos, 60);
        $resto = $minutos % 60;

        if($horas === 0) return $resto . 'min';
        if($resto === 0) return $horas . 'h';

        return $horas . 'h' . str_pad((string) $resto, 2, '0', STR_PAD_LEFT);
    }

    public static function telefone(string $telefone): string
    {
        $digitos = preg_replace('/\D/', '', $telefone);

        return match(strlen($digitos)){
            11      => sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 5), substr($digitos, 7)),
            10      => sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 4), substr($digitos, 6)),
            default => $telefone,
        };
    }

    public static function avaliacao(float $nota): string
    {
        return number_format($nota, 1, ',', '');
    }
}
