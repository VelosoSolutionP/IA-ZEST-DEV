<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Auth\Login;
use App\Domain\Shared\NaoEncontradoException;
use App\Domain\Usuario\UsuarioRepository;
use App\Http\Json;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AuthController
{
    public function __construct(
        private readonly Login $login,
        private readonly UsuarioRepository $usuarios,
        private readonly bool $confiarProxy = false,
    ) {
    }

    public function login(ServerRequestInterface $req, ResponseInterface $res): ResponseInterface
    {
        $corpo = (array) ($req->getParsedBody() ?? []);
        $resultado = $this->login->executar(
            (string) ($corpo['email'] ?? ''),
            (string) ($corpo['senha'] ?? ''),
            $this->ip($req),
        );
        return Json::responder($res, $resultado);
    }

    public function eu(ServerRequestInterface $req, ResponseInterface $res): ResponseInterface
    {
        $usuario = $this->usuarios->porId((string) $req->getAttribute('usuario_id'));
        if ($usuario === null) {
            throw new NaoEncontradoException('Usuário não encontrado.');
        }
        return Json::responder($res, $usuario->publico());
    }

    private function ip(ServerRequestInterface $req): string
    {
        if ($this->confiarProxy && $req->getHeaderLine('X-Real-IP') !== '') {
            return $req->getHeaderLine('X-Real-IP');
        }
        return (string) ($req->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0');
    }
}
