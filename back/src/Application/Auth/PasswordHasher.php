<?php

declare(strict_types=1);

namespace App\Application\Auth;

interface PasswordHasher
{
    public function hash(string $senha): string;

    public function confere(string $senha, string $hash): bool;

    public function precisaRehash(string $hash): bool;
}
