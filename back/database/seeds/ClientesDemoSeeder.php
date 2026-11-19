<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/** Alguns clientes de exemplo (documentos válidos gerados para teste, não são de pessoas reais). */
final class ClientesDemoSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['UsuarioDemoSeeder'];
    }

    public function run(): void
    {
        $pdo = $this->getAdapter()->getConnection();
        $dono = $pdo->query("SELECT id FROM usuarios ORDER BY criado_em LIMIT 1")->fetchColumn();
        if ($dono === false) {
            return;
        }
        $clientes = [
            ['Padaria Pão Quente Ltda', 'PJ', '11222333000181', 'contato@paoquente.dev', '3132221100', 'Belo Horizonte', 'MG'],
            ['Maria Aparecida Souza', 'PF', '52998224725', 'maria.souza@exemplo.dev', '31988887777', 'Contagem', 'MG'],
            ['Oficina Roda Viva ME', 'PJ', '45723174000110', 'oficina@rodaviva.dev', '1133334444', 'São Paulo', 'SP'],
        ];
        $st = $pdo->prepare(
            'INSERT INTO clientes (nome, tipo_pessoa, documento, email, telefone, cidade, uf, criado_por)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON CONFLICT (documento) DO NOTHING',
        );
        foreach ($clientes as $c) {
            $st->execute([...$c, $dono]);
        }
    }
}
