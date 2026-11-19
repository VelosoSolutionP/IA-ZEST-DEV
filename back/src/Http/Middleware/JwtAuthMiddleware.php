<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Application\Auth\TokenInvalidoException;
use App\Application\Auth\TokenService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/** Exige "Authorization: Bearer <jwt>" e coloca o id do usuário no atributo "usuario_id". */
final class JwtAuthMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly TokenService $tokens)
    {
    }

    public function process(ServerRequestInterface $req, RequestHandlerInterface $handler): ResponseInterface
    {
        $cabecalho = $req->getHeaderLine('Authorization');
        if (!preg_match('/^Bearer\s+(\S+)$/i', $cabecalho, $m)) {
            throw new TokenInvalidoException('Faça login para continuar.');
        }
        $claims = $this->tokens->validar($m[1]);
        return $handler->handle($req->withAttribute('usuario_id', (string) $claims['sub']));
    }
}
