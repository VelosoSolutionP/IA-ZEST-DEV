<?php

declare(strict_types=1);

namespace App\Bootstrap;

/** Lê a configuração só do ambiente (12-factor). Segredo nunca fica no código. */
final class Config
{
    public static function env(string $nome, ?string $padrao = null): string
    {
        $v = getenv($nome);
        if ($v === false || $v === '') {
            if ($padrao === null) {
                throw new \RuntimeException("Variável de ambiente obrigatória ausente: {$nome}");
            }
            return $padrao;
        }
        return $v;
    }
}
