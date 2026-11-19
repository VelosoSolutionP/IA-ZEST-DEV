<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Clientes\CadastrarCliente;
use App\Application\Clientes\ListarClientes;
use App\Domain\Cliente\ClienteRepository;
use App\Domain\Shared\NaoEncontradoException;
use App\Http\Json;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ClienteController
{
    public function __construct(
        private readonly CadastrarCliente $cadastrar,
        private readonly ListarClientes $listar,
        private readonly ClienteRepository $clientes,
    ) {
    }

    public function criar(ServerRequestInterface $req, ResponseInterface $res): ResponseInterface
    {
        $cliente = $this->cadastrar->executar((array) ($req->getParsedBody() ?? []), (string) $req->getAttribute('usuario_id'));
        return Json::responder($res, $cliente->paraArray(), 201)
            ->withHeader('Location', '/api/v1/clientes/' . $cliente->id);
    }

    public function listar(ServerRequestInterface $req, ResponseInterface $res): ResponseInterface
    {
        $q = $req->getQueryParams();
        $limite = isset($q['limite']) && is_numeric($q['limite']) ? (int) $q['limite'] : null;
        return Json::responder($res, $this->listar->executar($q['busca'] ?? null, $limite, $q['apos'] ?? null));
    }

    /** @param array{id: string} $args */
    public function ver(ServerRequestInterface $req, ResponseInterface $res, array $args): ResponseInterface
    {
        $cliente = $this->clientes->porId($args['id']);
        if ($cliente === null) {
            throw new NaoEncontradoException('Cliente não encontrado.');
        }
        return Json::responder($res, $cliente->paraArray());
    }
}
