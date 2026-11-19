<?php

declare(strict_types=1);

namespace App\Application\Clientes;

use App\Domain\Cliente\Cliente;
use App\Domain\Cliente\ClienteRepository;
use App\Domain\Shared\ConflitoException;

final class CadastrarCliente
{
    public function __construct(private readonly ClienteRepository $clientes)
    {
    }

    /** @param array<string, mixed> $dados */
    public function executar(array $dados, string $usuarioId): Cliente
    {
        $cliente = Cliente::novo($dados);
        if ($this->clientes->existeDocumento($cliente->documento->valor())) {
            throw new ConflitoException('Já existe cliente com este CPF/CNPJ.');
        }
        return $this->clientes->salvar($cliente, $usuarioId);
    }
}
