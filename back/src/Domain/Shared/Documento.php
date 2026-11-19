<?php

declare(strict_types=1);

namespace App\Domain\Shared;

/**
 * CPF (pessoa física) ou CNPJ (pessoa jurídica), guardado só com dígitos.
 * Valida os dígitos verificadores — não basta ter 11 ou 14 números.
 */
final class Documento
{
    private function __construct(private readonly string $digitos)
    {
    }

    public static function de(string $valor): self
    {
        $digitos = preg_replace('/\D/', '', $valor) ?? '';
        $valido = match (strlen($digitos)) {
            11 => self::cpfValido($digitos),
            14 => self::cnpjValido($digitos),
            default => false,
        };
        if (!$valido) {
            throw new ValidacaoException(['documento' => 'CPF ou CNPJ inválido.']);
        }
        return new self($digitos);
    }

    public function valor(): string
    {
        return $this->digitos;
    }

    public function tipoPessoa(): string
    {
        return strlen($this->digitos) === 11 ? 'PF' : 'PJ';
    }

    public function formatado(): string
    {
        $d = $this->digitos;
        return strlen($d) === 11
            ? sprintf('%s.%s.%s-%s', substr($d, 0, 3), substr($d, 3, 3), substr($d, 6, 3), substr($d, 9, 2))
            : sprintf('%s.%s.%s/%s-%s', substr($d, 0, 2), substr($d, 2, 3), substr($d, 5, 3), substr($d, 8, 4), substr($d, 12, 2));
    }

    private static function cpfValido(string $d): bool
    {
        if (preg_match('/^(\d)\1{10}$/', $d)) {
            return false;
        }
        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($i = 0; $i < $t; $i++) {
                $soma += (int) $d[$i] * (($t + 1) - $i);
            }
            $dv = ((10 * $soma) % 11) % 10;
            if ((int) $d[$t] !== $dv) {
                return false;
            }
        }
        return true;
    }

    private static function cnpjValido(string $d): bool
    {
        if (preg_match('/^(\d)\1{13}$/', $d)) {
            return false;
        }
        $pesos = [[5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2], [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]];
        foreach ($pesos as $n => $p) {
            $soma = 0;
            foreach ($p as $i => $peso) {
                $soma += (int) $d[$i] * $peso;
            }
            $resto = $soma % 11;
            $dv = $resto < 2 ? 0 : 11 - $resto;
            if ((int) $d[12 + $n] !== $dv) {
                return false;
            }
        }
        return true;
    }
}
