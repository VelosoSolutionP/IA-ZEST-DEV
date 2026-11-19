<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use App\Application\Clientes\CadastrarCliente;
use App\Application\Clientes\ListarClientes;
use App\Domain\Shared\ConflitoException;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\ClientesEmMemoria;

final class ClientesTest extends TestCase
{
    private ClientesEmMemoria $repo;

    protected function setUp(): void
    {
        $this->repo = new ClientesEmMemoria();
    }

    private function dados(string $doc = '52998224725', string $nome = 'Maria Souza'): array
    {
        return ['nome' => $nome, 'documento' => $doc, 'email' => 'm@exemplo.dev', 'telefone' => '31988887777'];
    }

    public function testCadastraEDevolveComId(): void
    {
        $c = (new CadastrarCliente($this->repo))->executar($this->dados(), 'u-1');
        self::assertNotNull($c->id);
        self::assertCount(1, $this->repo->itens);
    }

    public function testNaoAceitaDocumentoRepetidoMesmoComMascaraDiferente(): void
    {
        $uc = new CadastrarCliente($this->repo);
        $uc->executar($this->dados('52998224725'), 'u-1');
        $this->expectException(ConflitoException::class);
        $uc->executar($this->dados('529.982.247-25'), 'u-1');
    }

    public function testListarRespeitaLimiteMaximoEDevolveCursor(): void
    {
        $uc = new CadastrarCliente($this->repo);
        foreach (['52998224725', '11222333000181', '45723174000110'] as $i => $doc) {
            $uc->executar($this->dados($doc, "Cliente {$i}"), 'u-1');
        }
        $pagina = (new ListarClientes($this->repo))->executar(null, 2, null);
        self::assertCount(2, $pagina['itens']);
        self::assertNotNull($pagina['proximo']);

        $resto = (new ListarClientes($this->repo))->executar(null, 2, $pagina['proximo']);
        self::assertCount(1, $resto['itens']);
        self::assertNull($resto['proximo']);
    }

    public function testLimiteForaDaFaixaEhAjustado(): void
    {
        $l = new ListarClientes($this->repo);
        self::assertSame([], $l->executar('  ', 9999, null)['itens']);
        self::assertSame([], $l->executar(null, -5, null)['itens']);
    }
}
