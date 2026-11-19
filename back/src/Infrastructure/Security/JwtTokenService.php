<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Auth\TokenInvalidoException;
use App\Application\Auth\TokenService;
use App\Domain\Usuario\Usuario;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/** JWT HS256 sem estado no servidor: qualquer réplica da API valida o token. */
final class JwtTokenService implements TokenService
{
    private const ALGORITMO = 'HS256';

    public function __construct(
        private readonly string $segredo,
        private readonly int $validadeSegundos = 3600,
        private readonly string $emissor = 'ia-zest-api',
    ) {
        if (strlen($segredo) < 32) {
            throw new \InvalidArgumentException('JWT_SECRET precisa de pelo menos 32 caracteres.');
        }
    }

    public function emitir(Usuario $usuario): array
    {
        $agora = time();
        $expira = $agora + $this->validadeSegundos;
        $token = JWT::encode([
            'iss' => $this->emissor,
            'sub' => $usuario->id,
            'nome' => $usuario->nome,
            'iat' => $agora,
            'nbf' => $agora,
            'exp' => $expira,
        ], $this->segredo, self::ALGORITMO);

        return ['token' => $token, 'expira_em' => $expira];
    }

    public function validar(string $token): array
    {
        try {
            $claims = (array) JWT::decode($token, new Key($this->segredo, self::ALGORITMO));
        } catch (\Throwable) {
            throw new TokenInvalidoException('Token inválido ou expirado.');
        }
        if (($claims['iss'] ?? null) !== $this->emissor || !isset($claims['sub'])) {
            throw new TokenInvalidoException('Token inválido.');
        }
        return $claims;
    }
}
