<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CorsMiddleware implements MiddlewareInterface
{
    /** @param list<string> $origens */
    public function __construct(private readonly ResponseFactoryInterface $fabrica, private readonly array $origens)
    {
    }

    public function process(ServerRequestInterface $req, RequestHandlerInterface $handler): ResponseInterface
    {
        $resposta = $req->getMethod() === 'OPTIONS' ? $this->fabrica->createResponse(204) : $handler->handle($req);
        $origem = $req->getHeaderLine('Origin');
        if ($origem === '' || (!in_array('*', $this->origens, true) && !in_array($origem, $this->origens, true))) {
            return $resposta;
        }
        return $resposta
            ->withHeader('Access-Control-Allow-Origin', $origem)
            ->withHeader('Vary', 'Origin')
            ->withHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
            ->withHeader('Access-Control-Max-Age', '600');
    }
}
