<?php

declare(strict_types=1);

namespace App\Application\Clientes;

use App\Domain\Cliente\ClienteRepository;

final class ListarClientes
{
    public const LIMITE_PADRAO = 20;
    public const LIMITE_MAXIMO = 50;

    public function __construct(private readonly ClienteRepository $clientes)
    {
    }

    /** @return array{itens: list<array<string, mixed>>, proximo: ?string} */
    public function executar(?string $busca, ?int $limite, ?string $cursor): array
    {
        $limite = max(1, min($limite ?? self::LIMITE_PADRAO, self::LIMITE_MAXIMO));
        $busca = $busca !== null && trim($busca) !== '' ? mb_substr(trim($busca), 0, 100) : null;
        $pagina = $this->clientes->listar($busca, $limite, $cursor ?: null);

        return [
            'itens' => array_map(static fn ($c) => $c->paraArray(), $pagina['itens']),
            'proximo' => $pagina['proximo'],
        ];
    }
}
