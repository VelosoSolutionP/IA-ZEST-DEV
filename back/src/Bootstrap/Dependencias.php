<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Application\Auth\LimiteTentativas;
use App\Application\Auth\PasswordHasher;
use App\Application\Auth\TokenService;
use App\Domain\Cliente\ClienteRepository;
use App\Domain\Usuario\UsuarioRepository;
use App\Http\Controllers\AuthController;
use App\Infrastructure\Cache\RedisLimiteTentativas;
use App\Infrastructure\Persistence\Conexao;
use App\Infrastructure\Persistence\PdoClienteRepository;
use App\Infrastructure\Persistence\PdoUsuarioRepository;
use App\Infrastructure\Security\BcryptPasswordHasher;
use App\Infrastructure\Security\JwtTokenService;
use Predis\Client as Redis;
use Predis\ClientInterface;
use Psr\Container\ContainerInterface as C;

use function DI\autowire;

/** Liga as interfaces às implementações reais. Os testes trocam só o que precisam. */
final class Dependencias
{
    /** @return array<string, mixed> */
    public static function definicoes(): array
    {
        return [
            \PDO::class => static fn () => Conexao::criar(
                Config::env('DB_HOST', 'db'),
                (int) Config::env('DB_PORT', '5432'),
                Config::env('DB_NAME', 'ia_zest'),
                Config::env('DB_USER', 'ia_zest'),
                Config::env('DB_PASSWORD'),
            ),
            ClientInterface::class => static fn () => new Redis([
                'host' => Config::env('REDIS_HOST', 'redis'),
                'port' => (int) Config::env('REDIS_PORT', '6379'),
            ]),
            UsuarioRepository::class => autowire(PdoUsuarioRepository::class),
            ClienteRepository::class => autowire(PdoClienteRepository::class),
            PasswordHasher::class => static fn () => new BcryptPasswordHasher((int) Config::env('BCRYPT_COST', '12')),
            TokenService::class => static fn () => new JwtTokenService(
                Config::env('JWT_SECRET'),
                (int) Config::env('JWT_TTL', '3600'),
            ),
            LimiteTentativas::class => static fn (C $c) => new RedisLimiteTentativas(
                $c->get(ClientInterface::class),
                (int) Config::env('LOGIN_MAX_TENTATIVAS', '5'),
            ),
            AuthController::class => static fn (C $c) => new AuthController(
                $c->get(\App\Application\Auth\Login::class),
                $c->get(UsuarioRepository::class),
                Config::env('CONFIAR_PROXY', '0') === '1',
            ),
            'cors.origens' => static fn () => array_map('trim', explode(',', Config::env('CORS_ORIGENS', '*'))),
            'app.debug' => static fn () => Config::env('APP_DEBUG', '0') === '1',
        ];
    }
}
