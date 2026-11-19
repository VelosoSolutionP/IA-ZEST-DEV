<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use PDO;

final class Conexao
{
    public static function criar(string $host, int $porta, string $banco, string $usuario, string $senha): PDO
    {
        $pdo = new PDO(
            sprintf('pgsql:host=%s;port=%d;dbname=%s', $host, $porta, $banco),
            $usuario,
            $senha,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        );
        $pdo->exec("SET TIME ZONE 'UTC'");
        return $pdo;
    }
}
