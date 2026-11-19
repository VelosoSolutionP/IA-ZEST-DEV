<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Shared\Documento;
use App\Domain\Shared\ValidacaoException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DocumentoTest extends TestCase
{
    public function testCpfValidoComMascaraViraSoDigitosEPessoaFisica(): void
    {
        $d = Documento::de('529.982.247-25');
        self::assertSame('52998224725', $d->valor());
        self::assertSame('PF', $d->tipoPessoa());
        self::assertSame('529.982.247-25', $d->formatado());
    }

    public function testCnpjValidoEPessoaJuridica(): void
    {
        $d = Documento::de('11.222.333/0001-81');
        self::assertSame('PJ', $d->tipoPessoa());
        self::assertSame('11.222.333/0001-81', $d->formatado());
    }

    /** @return iterable<string, array{string}> */
    public static function invalidos(): iterable
    {
        yield 'cpf com dígito errado' => ['529.982.247-26'];
        yield 'cpf repetido' => ['111.111.111-11'];
        yield 'cnpj com dígito errado' => ['11.222.333/0001-82'];
        yield 'cnpj repetido' => ['00000000000000'];
        yield 'tamanho errado' => ['12345'];
        yield 'vazio' => [''];
    }

    #[DataProvider('invalidos')]
    public function testRejeitaDocumentoInvalido(string $valor): void
    {
        $this->expectException(ValidacaoException::class);
        Documento::de($valor);
    }
}
