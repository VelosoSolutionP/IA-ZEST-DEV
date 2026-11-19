<?php

declare(strict_types=1);

namespace App\Domain\Shared;

final class Email
{
    private function __construct(private readonly string $valor)
    {
    }

    public static function de(string $valor, string $campo = 'email'): self
    {
        $normalizado = mb_strtolower(trim($valor));
        if ($normalizado === '' || strlen($normalizado) > 180 || filter_var($normalizado, FILTER_VALIDATE_EMAIL) === false) {
            throw new ValidacaoException([$campo => 'E-mail inválido.']);
        }
        return new self($normalizado);
    }

    public function valor(): string
    {
        return $this->valor;
    }
}
