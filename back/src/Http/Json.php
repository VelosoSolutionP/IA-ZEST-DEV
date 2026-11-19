<?php

declare(strict_types=1);

namespace App\Http;

use Psr\Http\Message\ResponseInterface;

final class Json
{
    public static function responder(ResponseInterface $r, mixed $dados, int $status = 200): ResponseInterface
    {
        $r->getBody()->write(json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        return $r->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus($status);
    }
}
