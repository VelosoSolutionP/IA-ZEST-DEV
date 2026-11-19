<?php

declare(strict_types=1);

namespace App\Domain\Usuario;

final class Usuario
{
    public function __construct(
        public readonly string $id,
        public readonly string $nome,
        public readonly string $email,
        public readonly string $senhaHash,
        public readonly bool $ativo = true,
    ) {
    }

    /** @return array{id: string, nome: string, email: string} */
    public function publico(): array
    {
        return ['id' => $this->id, 'nome' => $this->nome, 'email' => $this->email];
    }
}
