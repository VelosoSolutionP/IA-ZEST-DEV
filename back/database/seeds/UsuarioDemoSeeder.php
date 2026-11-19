<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/** Usuário de demonstração. Idempotente: rodar de novo não duplica. */
final class UsuarioDemoSeeder extends AbstractSeed
{
    public function run(): void
    {
        $email = getenv('SEED_EMAIL') ?: 'demo@iazest.dev';
        $senha = getenv('SEED_SENHA') ?: 'Demo@2026';
        $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => (int) (getenv('BCRYPT_COST') ?: 12)]);

        $st = $this->getAdapter()->getConnection()->prepare(
            'INSERT INTO usuarios (nome, email, senha_hash) VALUES (:n, :e, :h)
             ON CONFLICT (lower(email)) DO NOTHING',
        );
        $st->execute(['n' => 'Usuário Demo', 'e' => $email, 'h' => $hash]);
    }
}
