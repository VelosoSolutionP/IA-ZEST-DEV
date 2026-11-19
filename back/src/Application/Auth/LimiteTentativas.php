<?php

declare(strict_types=1);

namespace App\Application\Auth;

/** Freio contra força bruta no login (implementação real em Redis). */
interface LimiteTentativas
{
    public function bloqueado(string $chave): bool;

    public function registrarFalha(string $chave): void;

    public function limpar(string $chave): void;
}
