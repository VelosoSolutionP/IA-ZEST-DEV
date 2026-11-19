<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use App\Application\Auth\CredenciaisInvalidasException;
use App\Application\Auth\Login;
use App\Application\Auth\MuitasTentativasException;
use App\Domain\Shared\ValidacaoException;
use App\Domain\Usuario\Usuario;
use App\Infrastructure\Security\BcryptPasswordHasher;
use App\Infrastructure\Security\JwtTokenService;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\LimiteEmMemoria;
use Tests\Fakes\UsuariosEmMemoria;

final class LoginTest extends TestCase
{
    private UsuariosEmMemoria $usuarios;
    private LimiteEmMemoria $limite;
    private Login $login;
    private JwtTokenService $tokens;

    protected function setUp(): void
    {
        $hasher = new BcryptPasswordHasher(4);
        $this->usuarios = new UsuariosEmMemoria();
        $this->usuarios->adicionar(new Usuario('u-1', 'Ana', 'ana@iazest.dev', $hasher->hash('Senha@123')));
        $this->usuarios->adicionar(new Usuario('u-2', 'Inativo', 'off@iazest.dev', $hasher->hash('Senha@123'), false));
        $this->limite = new LimiteEmMemoria(3);
        $this->tokens = new JwtTokenService(str_repeat('k', 40));
        $this->login = new Login($this->usuarios, $hasher, $this->tokens, $this->limite);
    }

    public function testLoginCorretoDevolveTokenValidoEUsuario(): void
    {
        $r = $this->login->executar('ANA@iazest.dev', 'Senha@123', '10.0.0.1');
        self::assertSame('u-1', $this->tokens->validar($r['token'])['sub']);
        self::assertSame(['id' => 'u-1', 'nome' => 'Ana', 'email' => 'ana@iazest.dev'], $r['usuario']);
        self::assertGreaterThan(time(), $r['expira_em']);
    }

    public function testSenhaErradaFalhaERegistraTentativa(): void
    {
        $this->expectException(CredenciaisInvalidasException::class);
        try {
            $this->login->executar('ana@iazest.dev', 'errada', '10.0.0.1');
        } finally {
            self::assertCount(1, $this->limite->falhas);
        }
    }

    public function testEmailInexistenteDaMesmaMensagemQueSenhaErrada(): void
    {
        $this->expectExceptionMessage('E-mail ou senha incorretos.');
        $this->login->executar('ninguem@iazest.dev', 'qualquer', '10.0.0.1');
    }

    public function testUsuarioInativoNaoEntra(): void
    {
        $this->expectException(CredenciaisInvalidasException::class);
        $this->login->executar('off@iazest.dev', 'Senha@123', '10.0.0.1');
    }

    public function testBloqueiaDepoisDoLimiteMesmoComSenhaCerta(): void
    {
        for ($i = 0; $i < 3; $i++) {
            try {
                $this->login->executar('ana@iazest.dev', 'errada', '10.0.0.9');
            } catch (CredenciaisInvalidasException) {
            }
        }
        $this->expectException(MuitasTentativasException::class);
        $this->login->executar('ana@iazest.dev', 'Senha@123', '10.0.0.9');
    }

    public function testSucessoZeraContadorDeFalhas(): void
    {
        try {
            $this->login->executar('ana@iazest.dev', 'errada', '10.0.0.2');
        } catch (CredenciaisInvalidasException) {
        }
        $this->login->executar('ana@iazest.dev', 'Senha@123', '10.0.0.2');
        self::assertSame([], $this->limite->falhas);
    }

    public function testRefazHashQuandoCustoDoBcryptMuda(): void
    {
        $login = new Login($this->usuarios, new BcryptPasswordHasher(5), $this->tokens, $this->limite);
        $login->executar('ana@iazest.dev', 'Senha@123', '10.0.0.3');
        self::assertSame(1, $this->usuarios->rehashes);
        self::assertStringStartsWith('$2y$05$', $this->usuarios->porId('u-1')->senhaHash);
    }

    public function testSenhaVaziaEhErroDeValidacao(): void
    {
        $this->expectException(ValidacaoException::class);
        $this->login->executar('ana@iazest.dev', '  ', '10.0.0.1');
    }
}
