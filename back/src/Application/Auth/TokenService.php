<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\Usuario\Usuario;

interface TokenService
{
    /** @return array{token: string, expira_em: int} */
    public function emitir(Usuario $usuario): array;

    /**
     * Devolve as claims do token ou lança TokenInvalidoException.
     *
     * @return array<string, mixed>
     */
    public function validar(string $token): array;
}
