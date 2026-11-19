<?php

declare(strict_types=1);

namespace App\Domain\Cliente;

interface ClienteRepository
{
    public function existeDocumento(string $documento): bool;

    /** Grava e devolve o cliente com id e data de criação. */
    public function salvar(Cliente $cliente, string $criadoPor): Cliente;

    public function porId(string $id): ?Cliente;

    /**
     * Paginação por cursor (keyset): estável e rápida mesmo com milhões de linhas.
     *
     * @return array{itens: list<Cliente>, proximo: ?string}
     */
    public function listar(?string $busca, int $limite, ?string $cursor): array;
}
