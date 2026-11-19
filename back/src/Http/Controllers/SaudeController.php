<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Json;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/** Liveness/readiness para o Docker e o balanceador: confere banco e Redis. */
final class SaudeController
{
    public function __construct(private readonly ContainerInterface $c)
    {
    }

    public function __invoke(ServerRequestInterface $req, ResponseInterface $res): ResponseInterface
    {
        $estado = ['api' => 'ok', 'banco' => 'ok', 'redis' => 'ok'];
        try {
            $this->c->get(\PDO::class)->query('SELECT 1');
        } catch (\Throwable) {
            $estado['banco'] = 'falha';
        }
        try {
            $this->c->get(\Predis\ClientInterface::class)->ping();
        } catch (\Throwable) {
            $estado['redis'] = 'falha';
        }
        $ok = !in_array('falha', $estado, true);
        return Json::responder($res, $estado, $ok ? 200 : 503);
    }
}
