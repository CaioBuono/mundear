<?php

namespace Utils;

/**
 * Validações reutilizáveis (RN05: bloquear dados inválidos antes de salvar).
 */
class Validador
{
    public static function email(string $email): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email);
    }

    /**
     * Confere se a string é uma data real no formato Y-m-d (ex.: rejeita 2026-02-30).
     */
    public static function data(?string $data): bool
    {
        if(!is_string($data) or $data === '') return false;

        $obData = \DateTime::createFromFormat('!Y-m-d', $data);
        return $obData !== false && $obData->format('Y-m-d') === $data;
    }

    public static function telefone(string $telefone): bool
    {
        return (bool) preg_match('/^\d{10,11}$/', preg_replace('/\D/', '', $telefone));
    }

    public static function decimalPositivo(mixed $valor): bool
    {
        return is_numeric($valor) && (float) $valor > 0;
    }

    public static function inteiroEntre(mixed $valor, int $minimo, int $maximo): bool
    {
        return filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => $minimo, 'max_range' => $maximo]]) !== false;
    }

    public static function diasEntre(string $dataInicio, string $dataFim): int
    {
        return (int) (new \DateTime($dataInicio))->diff(new \DateTime($dataFim))->format('%r%a');
    }
}
