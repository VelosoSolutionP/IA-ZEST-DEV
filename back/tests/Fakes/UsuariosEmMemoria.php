<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Domain\Usuario\Usuario;
use App\Domain\Usuario\UsuarioRepository;

final class UsuariosEmMemoria implements UsuarioRepository
{
    /** @var array<string, Usuario> */
    public array $porId = [];
    public int $rehashes = 0;

    public function adicionar(Usuario $u): void
    {
        $this->porId[$u->id] = $u;
    }

    public function porEmail(string $email): ?Usuario
    {
        foreach ($this->porId as $u) {
            if (strtolower($u->email) === strtolower($email)) {
                return $u;
            }
        }
        return null;
    }

    public function porId(string $id): ?Usuario
    {
        return $this->porId[$id] ?? null;
    }

    public function atualizarSenhaHash(string $id, string $hash): void
    {
        $u = $this->porId[$id];
        $this->porId[$id] = new Usuario($u->id, $u->nome, $u->email, $hash, $u->ativo);
        $this->rehashes++;
    }
}
