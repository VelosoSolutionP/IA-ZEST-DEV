<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Auth\PasswordHasher;

final class BcryptPasswordHasher implements PasswordHasher
{
    public function __construct(private readonly int $custo = 12)
    {
    }

    public function hash(string $senha): string
    {
        return password_hash($senha, PASSWORD_BCRYPT, ['cost' => $this->custo]);
    }

    public function confere(string $senha, string $hash): bool
    {
        return password_verify($senha, $hash);
    }

    public function precisaRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => $this->custo]);
    }
}
