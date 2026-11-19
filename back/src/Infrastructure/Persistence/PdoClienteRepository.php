<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Cliente\Cliente;
use App\Domain\Cliente\ClienteRepository;
use App\Domain\Shared\ConflitoException;
use PDO;

final class PdoClienteRepository implements ClienteRepository
{
    private const COLUNAS = 'id, nome, documento, email, telefone, cidade, uf, criado_em';

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function existeDocumento(string $documento): bool
    {
        $st = $this->pdo->prepare('SELECT 1 FROM clientes WHERE documento = :d');
        $st->execute(['d' => $documento]);
        return $st->fetchColumn() !== false;
    }

    public function salvar(Cliente $c, string $criadoPor): Cliente
    {
        $st = $this->pdo->prepare(
            'INSERT INTO clientes (nome, tipo_pessoa, documento, email, telefone, cidade, uf, criado_por)
             VALUES (:nome, :tipo, :doc, :email, :tel, :cidade, :uf, :por)
             RETURNING ' . self::COLUNAS,
        );
        try {
            $st->execute([
                'nome' => $c->nome,
                'tipo' => $c->documento->tipoPessoa(),
                'doc' => $c->documento->valor(),
                'email' => $c->email->valor(),
                'tel' => $c->telefone,
                'cidade' => $c->cidade,
                'uf' => $c->uf,
                'por' => $criadoPor,
            ]);
        } catch (\PDOException $e) {
            // corrida entre duas requisições com o mesmo documento: o índice UNIQUE decide
            if ($e->getCode() === '23505') {
                throw new ConflitoException('Já existe cliente com este CPF/CNPJ.');
            }
            throw $e;
        }
        return $this->mapear($st->fetch());
    }

    public function porId(string $id): ?Cliente
    {
        if (!preg_match('/^[0-9a-f-]{36}$/i', $id)) {
            return null;
        }
        $st = $this->pdo->prepare('SELECT ' . self::COLUNAS . ' FROM clientes WHERE id = :id');
        $st->execute(['id' => $id]);
        $l = $st->fetch();
        return $l === false ? null : $this->mapear($l);
    }

    public function listar(?string $busca, int $limite, ?string $cursor): array
    {
        $where = [];
        $p = [];
        if ($busca !== null) {
            // nome usa índice trigram (pg_trgm); documento usa o índice UNIQUE por prefixo
            $where[] = '(nome ILIKE :busca OR documento LIKE :doc)';
            $p['busca'] = '%' . addcslashes($busca, '%_\\') . '%';
            $p['doc'] = (preg_replace('/\D/', '', $busca) ?: '#') . '%';
        }
        $pos = $this->lerCursor($cursor);
        if ($pos !== null) {
            $where[] = '(criado_em, id) < (:c_em, :c_id)';
            $p['c_em'] = $pos[0];
            $p['c_id'] = $pos[1];
        }
        $sql = 'SELECT ' . self::COLUNAS . ' FROM clientes'
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
            . ' ORDER BY criado_em DESC, id DESC LIMIT ' . ($limite + 1);
        $st = $this->pdo->prepare($sql);
        $st->execute($p);
        $linhas = $st->fetchAll();

        $temMais = count($linhas) > $limite;
        $linhas = array_slice($linhas, 0, $limite);
        $ultima = end($linhas);

        return [
            'itens' => array_map(fn (array $l) => $this->mapear($l), $linhas),
            'proximo' => $temMais && $ultima ? $this->criarCursor($ultima['criado_em'], $ultima['id']) : null,
        ];
    }

    /** @param array<string, mixed> $l */
    private function mapear(array $l): Cliente
    {
        return Cliente::reconstituir(
            $l['id'], $l['nome'], $l['documento'], $l['email'], $l['telefone'], $l['cidade'], $l['uf'],
            (new \DateTimeImmutable($l['criado_em']))->format(DATE_ATOM),
        );
    }

    private function criarCursor(string $criadoEm, string $id): string
    {
        return rtrim(strtr(base64_encode($criadoEm . '|' . $id), '+/', '-_'), '=');
    }

    /** @return array{0: string, 1: string}|null */
    private function lerCursor(?string $cursor): ?array
    {
        if ($cursor === null) {
            return null;
        }
        $bruto = base64_decode(strtr($cursor, '-_', '+/'), true);
        $partes = $bruto === false ? [] : explode('|', $bruto);
        if (count($partes) !== 2 || !preg_match('/^[0-9a-f-]{36}$/i', $partes[1]) || strtotime($partes[0]) === false) {
            return null;
        }
        return [$partes[0], $partes[1]];
    }
}
