<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Usuario\Usuario;
use App\Domain\Usuario\UsuarioRepository;
use PDO;

final class PdoUsuarioRepository implements UsuarioRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function porEmail(string $email): ?Usuario
    {
        $st = $this->pdo->prepare('SELECT id, nome, email, senha_hash, ativo FROM usuarios WHERE lower(email) = lower(:email)');
        $st->execute(['email' => $email]);
        return $this->mapear($st->fetch());
    }

    public function porId(string $id): ?Usuario
    {
        $st = $this->pdo->prepare('SELECT id, nome, email, senha_hash, ativo FROM usuarios WHERE id = :id');
        $st->execute(['id' => $id]);
        return $this->mapear($st->fetch());
    }

    public function atualizarSenhaHash(string $id, string $hash): void
    {
        $st = $this->pdo->prepare('UPDATE usuarios SET senha_hash = :h, atualizado_em = now() WHERE id = :id');
        $st->execute(['h' => $hash, 'id' => $id]);
    }

    /** @param array<string, mixed>|false $l */
    private function mapear(array|false $l): ?Usuario
    {
        if ($l === false) {
            return null;
        }
        return new Usuario($l['id'], $l['nome'], $l['email'], $l['senha_hash'], (bool) $l['ativo']);
    }
}
