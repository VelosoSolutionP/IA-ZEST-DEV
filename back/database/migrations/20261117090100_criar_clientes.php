<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CriarClientes extends AbstractMigration
{
    public function up(): void
    {
        $this->execute('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        $this->execute(<<<'SQL'
            CREATE TABLE clientes (
                id            uuid PRIMARY KEY DEFAULT gen_random_uuid(),
                nome          varchar(150) NOT NULL,
                tipo_pessoa   char(2)      NOT NULL CHECK (tipo_pessoa IN ('PF', 'PJ')),
                documento     varchar(14)  NOT NULL CHECK (documento ~ '^[0-9]{11}$|^[0-9]{14}$'),
                email         varchar(180) NOT NULL,
                telefone      varchar(11)  NOT NULL CHECK (telefone ~ '^[0-9]{10,11}$'),
                cidade        varchar(100),
                uf            char(2),
                criado_por    uuid         NOT NULL REFERENCES usuarios (id) ON DELETE RESTRICT,
                criado_em     timestamptz  NOT NULL DEFAULT now(),
                atualizado_em timestamptz  NOT NULL DEFAULT now()
            );
            -- CPF/CNPJ único: regra de negócio garantida no banco (protege contra corrida)
            CREATE UNIQUE INDEX clientes_documento_unico ON clientes (documento varchar_pattern_ops);
            -- listagem paginada por cursor (criado_em, id) sem sort em memória
            CREATE INDEX clientes_criado_em_id ON clientes (criado_em DESC, id DESC);
            -- busca por parte do nome (ILIKE '%...%') usando trigramas
            CREATE INDEX clientes_nome_trgm ON clientes USING gin (nome gin_trgm_ops);
            -- FK com índice: apagar/consultar por usuário não varre a tabela
            CREATE INDEX clientes_criado_por ON clientes (criado_por);
        SQL);
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS clientes');
    }
}
