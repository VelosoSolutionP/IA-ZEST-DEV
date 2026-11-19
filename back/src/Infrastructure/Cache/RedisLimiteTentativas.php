<?php

declare(strict_types=1);

namespace App\Infrastructure\Cache;

use App\Application\Auth\LimiteTentativas;
use Predis\ClientInterface;

/** Contador de falhas por IP+e-mail com expiração no Redis (compartilhado entre réplicas). */
final class RedisLimiteTentativas implements LimiteTentativas
{
    public function __construct(
        private readonly ClientInterface $redis,
        private readonly int $maximo = 5,
        private readonly int $janelaSegundos = 900,
    ) {
    }

    public function bloqueado(string $chave): bool
    {
        return (int) $this->redis->get($this->k($chave)) >= $this->maximo;
    }

    public function registrarFalha(string $chave): void
    {
        $k = $this->k($chave);
        $total = $this->redis->incr($k);
        if ($total === 1) {
            $this->redis->expire($k, $this->janelaSegundos);
        }
    }

    public function limpar(string $chave): void
    {
        $this->redis->del([$this->k($chave)]);
    }

    private function k(string $chave): string
    {
        return 'login:falhas:' . $chave;
    }
}
