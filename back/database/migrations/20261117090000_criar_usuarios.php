<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CriarUsuarios extends AbstractMigration
{
    public function up(): void
    {
        $this->execute('CREATE EXTENSION IF NOT EXISTS pgcrypto');
        $this->execute(<<<'SQL'
            CREATE TABLE usuarios (
                id            uuid PRIMARY KEY DEFAULT gen_random_uuid(),
                nome          varchar(120) NOT NULL,
                email         varchar(180) NOT NULL,
                senha_hash    varchar(255) NOT NULL,
                ativo         boolean      NOT NULL DEFAULT true,
                criado_em     timestamptz  NOT NULL DEFAULT now(),
                atualizado_em timestamptz  NOT NULL DEFAULT now(),
                CONSTRAINT usuarios_email_formato CHECK (position('@' in email) > 1)
            );
            -- login busca por e-mail sem diferenciar maiúsculas: índice único na expressão
            CREATE UNIQUE INDEX usuarios_email_unico ON usuarios (lower(email));
        SQL);
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS usuarios');
    }
}
