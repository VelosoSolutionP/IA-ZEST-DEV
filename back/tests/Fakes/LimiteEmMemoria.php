<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Application\Auth\LimiteTentativas;

final class LimiteEmMemoria implements LimiteTentativas
{
    /** @var array<string, int> */
    public array $falhas = [];

    public function __construct(private readonly int $maximo = 5)
    {
    }

    public function bloqueado(string $chave): bool
    {
        return ($this->falhas[$chave] ?? 0) >= $this->maximo;
    }

    public function registrarFalha(string $chave): void
    {
        $this->falhas[$chave] = ($this->falhas[$chave] ?? 0) + 1;
    }

    public function limpar(string $chave): void
    {
        unset($this->falhas[$chave]);
    }
}
