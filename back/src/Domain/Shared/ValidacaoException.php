<?php

declare(strict_types=1);

namespace App\Domain\Shared;

/** Dados de entrada inválidos. Carrega os erros por campo para a resposta 422. */
final class ValidacaoException extends \DomainException
{
    /** @param array<string, string> $erros */
    public function __construct(private readonly array $erros)
    {
        parent::__construct('Dados inválidos.');
    }

    /** @return array<string, string> */
    public function erros(): array
    {
        return $this->erros;
    }
}
