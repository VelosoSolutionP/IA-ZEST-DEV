<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Cliente\Cliente;
use App\Domain\Shared\ValidacaoException;
use PHPUnit\Framework\TestCase;

final class ClienteTest extends TestCase
{
    public function testNormalizaDadosValidos(): void
    {
        $c = Cliente::novo([
            'nome' => '  Maria   da  Silva ',
            'documento' => '529.982.247-25',
            'email' => ' MARIA@Exemplo.dev ',
            'telefone' => '(31) 98888-7777',
            'cidade' => 'Contagem',
            'uf' => 'mg',
        ]);
        self::assertSame('Maria da Silva', $c->nome);
        self::assertSame('maria@exemplo.dev', $c->email->valor());
        self::assertSame('31988887777', $c->telefone);
        self::assertSame('MG', $c->uf);
    }

    public function testJuntaTodosOsErrosDeUmaVez(): void
    {
        try {
            Cliente::novo(['nome' => 'A', 'documento' => '123', 'email' => 'x', 'telefone' => '1', 'uf' => 'XX']);
            self::fail('deveria lançar ValidacaoException');
        } catch (ValidacaoException $e) {
            self::assertEqualsCanonicalizing(['nome', 'documento', 'email', 'telefone', 'uf'], array_keys($e->erros()));
        }
    }

    public function testCidadeEUfSaoOpcionais(): void
    {
        $c = Cliente::novo(['nome' => 'Oficina X', 'documento' => '11222333000181', 'email' => 'a@b.dev', 'telefone' => '1133334444']);
        self::assertNull($c->cidade);
        self::assertNull($c->uf);
        self::assertSame('PJ', $c->paraArray()['tipo_pessoa']);
    }
}
