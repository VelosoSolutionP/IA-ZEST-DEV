<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application\Auth\LimiteTentativas;
use App\Application\Auth\PasswordHasher;
use App\Application\Auth\TokenService;
use App\Bootstrap\AppFactory;
use App\Domain\Cliente\ClienteRepository;
use App\Domain\Usuario\Usuario;
use App\Domain\Usuario\UsuarioRepository;
use App\Infrastructure\Security\BcryptPasswordHasher;
use App\Infrastructure\Security\JwtTokenService;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use Tests\Fakes\ClientesEmMemoria;
use Tests\Fakes\LimiteEmMemoria;
use Tests\Fakes\UsuariosEmMemoria;

/** Exercita rotas, middleware JWT, CORS e o mapeamento de erros sem banco (repositórios em memória). */
final class ApiTest extends TestCase
{
    private App $app;

    protected function setUp(): void
    {
        $hasher = new BcryptPasswordHasher(4);
        $usuarios = new UsuariosEmMemoria();
        $usuarios->adicionar(new Usuario('11111111-1111-4111-8111-111111111111', 'Demo', 'demo@iazest.dev', $hasher->hash('Demo@2026')));
        putenv('CORS_ORIGENS=http://localhost:8092');
        $this->app = AppFactory::criar([
            UsuarioRepository::class => $usuarios,
            ClienteRepository::class => new ClientesEmMemoria(),
            PasswordHasher::class => $hasher,
            TokenService::class => new JwtTokenService(str_repeat('t', 40)),
            LimiteTentativas::class => new LimiteEmMemoria(5),
        ]);
    }

    /** @return array{0: int, 1: mixed, 2: \Psr\Http\Message\ResponseInterface} */
    private function chamar(string $metodo, string $uri, ?array $corpo = null, ?string $token = null, array $cab = []): array
    {
        $req = (new ServerRequestFactory())->createServerRequest($metodo, $uri, ['REMOTE_ADDR' => '127.0.0.1']);
        if ($corpo !== null) {
            $req = $req->withHeader('Content-Type', 'application/json');
            $req->getBody()->write(json_encode($corpo));
            $req->getBody()->rewind();
        }
        if ($token !== null) {
            $req = $req->withHeader('Authorization', 'Bearer ' . $token);
        }
        foreach ($cab as $k => $v) {
            $req = $req->withHeader($k, $v);
        }
        $res = $this->app->handle($req);
        return [$res->getStatusCode(), json_decode((string) $res->getBody(), true), $res];
    }

    private function token(): string
    {
        [, $corpo] = $this->chamar('POST', '/api/v1/auth/login', ['email' => 'demo@iazest.dev', 'senha' => 'Demo@2026']);
        return $corpo['token'];
    }

    public function testLoginOkDevolveJwt(): void
    {
        [$st, $corpo] = $this->chamar('POST', '/api/v1/auth/login', ['email' => 'demo@iazest.dev', 'senha' => 'Demo@2026']);
        self::assertSame(200, $st);
        self::assertCount(3, explode('.', $corpo['token']));
        self::assertSame('Demo', $corpo['usuario']['nome']);
    }

    public function testLoginErradoDa401(): void
    {
        [$st, $corpo] = $this->chamar('POST', '/api/v1/auth/login', ['email' => 'demo@iazest.dev', 'senha' => 'x']);
        self::assertSame(401, $st);
        self::assertSame('E-mail ou senha incorretos.', $corpo['erro']);
    }

    public function testSextaTentativaErradaDa429(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->chamar('POST', '/api/v1/auth/login', ['email' => 'demo@iazest.dev', 'senha' => 'x']);
        }
        [$st, , $res] = $this->chamar('POST', '/api/v1/auth/login', ['email' => 'demo@iazest.dev', 'senha' => 'Demo@2026']);
        self::assertSame(429, $st);
        self::assertSame('900', $res->getHeaderLine('Retry-After'));
    }

    public function testRotaPrivadaSemTokenDa401(): void
    {
        self::assertSame(401, $this->chamar('GET', '/api/v1/clientes')[0]);
        self::assertSame(401, $this->chamar('GET', '/api/v1/me', null, 'lixo')[0]);
    }

    public function testMeDevolveUsuarioDoToken(): void
    {
        [$st, $corpo] = $this->chamar('GET', '/api/v1/me', null, $this->token());
        self::assertSame(200, $st);
        self::assertSame('demo@iazest.dev', $corpo['email']);
        self::assertArrayNotHasKey('senha_hash', $corpo);
    }

    public function testFluxoCompletoDeCadastroDeCliente(): void
    {
        $t = $this->token();
        $dados = ['nome' => 'Padaria Pão Quente', 'documento' => '11.222.333/0001-81', 'email' => 'p@q.dev', 'telefone' => '(31) 3222-1100', 'uf' => 'MG'];

        [$st, $criado, $res] = $this->chamar('POST', '/api/v1/clientes', $dados, $t);
        self::assertSame(201, $st);
        self::assertSame('PJ', $criado['tipo_pessoa']);
        self::assertSame('/api/v1/clientes/' . $criado['id'], $res->getHeaderLine('Location'));

        self::assertSame(409, $this->chamar('POST', '/api/v1/clientes', $dados, $t)[0]);

        [$st, $lista] = $this->chamar('GET', '/api/v1/clientes?busca=padaria', null, $t);
        self::assertSame(200, $st);
        self::assertSame('Padaria Pão Quente', $lista['itens'][0]['nome']);

        self::assertSame(200, $this->chamar('GET', '/api/v1/clientes/' . $criado['id'], null, $t)[0]);
        self::assertSame(404, $this->chamar('GET', '/api/v1/clientes/00000000-0000-4000-8000-999999999999', null, $t)[0]);
    }

    public function testCadastroInvalidoDa422ComCampos(): void
    {
        [$st, $corpo] = $this->chamar('POST', '/api/v1/clientes', ['nome' => 'X'], $this->token());
        self::assertSame(422, $st);
        self::assertArrayHasKey('documento', $corpo['campos']);
        self::assertArrayHasKey('email', $corpo['campos']);
    }

    public function testRotaInexistenteDa404EmJson(): void
    {
        [$st, $corpo] = $this->chamar('GET', '/nao-existe');
        self::assertSame(404, $st);
        self::assertArrayHasKey('erro', $corpo);
    }

    public function testCorsSoParaOrigemPermitida(): void
    {
        [$st, , $res] = $this->chamar('OPTIONS', '/api/v1/clientes', null, null, ['Origin' => 'http://localhost:8092']);
        self::assertSame(204, $st);
        self::assertSame('http://localhost:8092', $res->getHeaderLine('Access-Control-Allow-Origin'));

        [, , $res] = $this->chamar('OPTIONS', '/api/v1/clientes', null, null, ['Origin' => 'https://malicioso.dev']);
        self::assertSame('', $res->getHeaderLine('Access-Control-Allow-Origin'));
    }
}
