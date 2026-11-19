<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Domain\Cliente\Cliente;
use App\Domain\Cliente\ClienteRepository;

final class ClientesEmMemoria implements ClienteRepository
{
    /** @var list<Cliente> mais novo primeiro */
    public array $itens = [];
    private int $seq = 0;

    public function existeDocumento(string $documento): bool
    {
        foreach ($this->itens as $c) {
            if ($c->documento->valor() === $documento) {
                return true;
            }
        }
        return false;
    }

    public function salvar(Cliente $c, string $criadoPor): Cliente
    {
        $this->seq++;
        $id = sprintf('00000000-0000-4000-8000-%012d', $this->seq);
        $salvo = Cliente::reconstituir($id, $c->nome, $c->documento->valor(), $c->email->valor(), $c->telefone,
            $c->cidade, $c->uf, date(DATE_ATOM, 1_800_000_000 + $this->seq));
        array_unshift($this->itens, $salvo);
        return $salvo;
    }

    public function porId(string $id): ?Cliente
    {
        foreach ($this->itens as $c) {
            if ($c->id === $id) {
                return $c;
            }
        }
        return null;
    }

    public function listar(?string $busca, int $limite, ?string $cursor): array
    {
        $lista = array_values(array_filter($this->itens,
            fn (Cliente $c) => $busca === null || stripos($c->nome, $busca) !== false));
        $inicio = $cursor !== null ? (int) $cursor : 0;
        $pagina = array_slice($lista, $inicio, $limite);
        $proximo = count($lista) > $inicio + $limite ? (string) ($inicio + $limite) : null;
        return ['itens' => $pagina, 'proximo' => $proximo];
    }
}
