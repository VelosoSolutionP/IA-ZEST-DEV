<?php

declare(strict_types=1);

namespace App\Domain\Usuario;

interface UsuarioRepository
{
    public function porEmail(string $email): ?Usuario;

    public function porId(string $id): ?Usuario;

    public function atualizarSenhaHash(string $id, string $hash): void;
}
