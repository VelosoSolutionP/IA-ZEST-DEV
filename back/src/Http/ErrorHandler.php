<?php

declare(strict_types=1);

namespace App\Http;

use App\Application\Auth\CredenciaisInvalidasException;
use App\Application\Auth\MuitasTentativasException;
use App\Application\Auth\TokenInvalidoException;
use App\Domain\Shared\ConflitoException;
use App\Domain\Shared\NaoEncontradoException;
use App\Domain\Shared\ValidacaoException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpException;

/** Converte qualquer exceção em JSON com o status certo. Nunca vaza stack trace para o cliente. */
final class ErrorHandler
{
    public function __construct(
        private readonly ResponseFactoryInterface $fabrica,
        private readonly ?LoggerInterface $log = null,
    ) {
    }

    public function __invoke(ServerRequestInterface $req, \Throwable $e, bool $detalhes): ResponseInterface
    {
        [$status, $corpo] = match (true) {
            $e instanceof ValidacaoException => [422, ['erro' => $e->getMessage(), 'campos' => $e->erros()]],
            $e instanceof CredenciaisInvalidasException,
            $e instanceof TokenInvalidoException => [401, ['erro' => $e->getMessage()]],
            $e instanceof ConflitoException => [409, ['erro' => $e->getMessage()]],
            $e instanceof NaoEncontradoException => [404, ['erro' => $e->getMessage()]],
            $e instanceof MuitasTentativasException => [429, ['erro' => $e->getMessage()]],
            $e instanceof HttpException => [$e->getCode(), ['erro' => $e->getTitle()]],
            default => [500, ['erro' => 'Erro interno. Tente novamente.']],
        };
        if ($status === 500) {
            $this->log?->error($e->getMessage(), ['exception' => $e]);
            error_log((string) $e);
            if ($detalhes) {
                $corpo['detalhe'] = $e->getMessage();
            }
        }
        $resposta = Json::responder($this->fabrica->createResponse(), $corpo, $status);
        return $status === 429 ? $resposta->withHeader('Retry-After', '900') : $resposta;
    }
}
