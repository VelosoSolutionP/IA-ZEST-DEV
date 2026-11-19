<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use App\Application\Auth\TokenInvalidoException;
use App\Domain\Usuario\Usuario;
use App\Infrastructure\Security\BcryptPasswordHasher;
use App\Infrastructure\Security\JwtTokenService;
use PHPUnit\Framework\TestCase;

final class JwtTokenServiceTest extends TestCase
{
    private Usuario $u;

    protected function setUp(): void
    {
        $this->u = new Usuario('u-1', 'Ana', 'ana@iazest.dev', 'x');
    }

    public function testEmiteEValida(): void
    {
        $s = new JwtTokenService(str_repeat('a', 32), 60);
        $claims = $s->validar($s->emitir($this->u)['token']);
        self::assertSame('u-1', $claims['sub']);
        self::assertSame('ia-zest-api', $claims['iss']);
    }

    public function testRejeitaTokenAssinadoComOutroSegredo(): void
    {
        $token = (new JwtTokenService(str_repeat('a', 32)))->emitir($this->u)['token'];
        $this->expectException(TokenInvalidoException::class);
        (new JwtTokenService(str_repeat('b', 32)))->validar($token);
    }

    public function testRejeitaTokenExpirado(): void
    {
        $s = new JwtTokenService(str_repeat('a', 32), -10);
        $this->expectException(TokenInvalidoException::class);
        $s->validar($s->emitir($this->u)['token']);
    }

    public function testRejeitaTokenAdulterado(): void
    {
        $s = new JwtTokenService(str_repeat('a', 32));
        [$h, $p, $a] = explode('.', $s->emitir($this->u)['token']);
        $p = rtrim(strtr(base64_encode('{"iss":"ia-zest-api","sub":"admin","exp":9999999999}'), '+/', '-_'), '=');
        $this->expectException(TokenInvalidoException::class);
        $s->validar("{$h}.{$p}.{$a}");
    }

    public function testSegredoCurtoEhRecusado(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new JwtTokenService('curto');
    }

    public function testBcryptGeraHashDiferenteACadaVezEConfere(): void
    {
        $h = new BcryptPasswordHasher(4);
        $a = $h->hash('Senha@123');
        self::assertNotSame($a, $h->hash('Senha@123'));
        self::assertStringStartsWith('$2y$04$', $a);
        self::assertTrue($h->confere('Senha@123', $a));
        self::assertFalse($h->confere('senha@123', $a));
    }
}
