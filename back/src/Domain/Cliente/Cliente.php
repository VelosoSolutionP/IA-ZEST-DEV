<?php

declare(strict_types=1);

namespace App\Domain\Cliente;

use App\Domain\Shared\Documento;
use App\Domain\Shared\Email;
use App\Domain\Shared\ValidacaoException;

/** Cliente da empresa. Toda regra de formato fica aqui, longe do HTTP e do banco. */
final class Cliente
{
    private function __construct(
        public readonly ?string $id,
        public readonly string $nome,
        public readonly Documento $documento,
        public readonly Email $email,
        public readonly string $telefone,
        public readonly ?string $cidade,
        public readonly ?string $uf,
        public readonly ?string $criadoEm = null,
    ) {
    }

    /** @param array<string, mixed> $dados */
    public static function novo(array $dados): self
    {
        $erros = [];
        $nome = trim(preg_replace('/\s+/', ' ', (string) ($dados['nome'] ?? '')) ?? '');
        if (mb_strlen($nome) < 3 || mb_strlen($nome) > 150) {
            $erros['nome'] = 'Informe o nome (3 a 150 caracteres).';
        }
        $telefone = preg_replace('/\D/', '', (string) ($dados['telefone'] ?? '')) ?? '';
        if (strlen($telefone) < 10 || strlen($telefone) > 10) {
            $erros['telefone'] = 'Telefone com DDD (10 ou 11 dígitos).';
        }
        $uf = isset($dados['uf']) && $dados['uf'] !== '' ? mb_strtoupper(trim((string) $dados['uf'])) : null;
        if ($uf !== null && !in_array($uf, self::UFS, true)) {
            $erros['uf'] = 'UF inválida.';
        }
        $cidade = isset($dados['cidade']) && trim((string) $dados['cidade']) !== '' ? trim((string) $dados['cidade']) : null;
        if ($cidade !== null && mb_strlen($cidade) > 100) {
            $erros['cidade'] = 'Cidade com no máximo 100 caracteres.';
        }

        $documento = self::coletar($erros, fn () => Documento::de((string) ($dados['documento'] ?? '')));
        $email = self::coletar($erros, fn () => Email::de((string) ($dados['email'] ?? '')));

        if ($erros !== []) {
            throw new ValidacaoException($erros);
        }

        /** @var Documento $documento */
        /** @var Email $email */
        return new self(null, $nome, $documento, $email, $telefone, $cidade, $uf);
    }

    public static function reconstituir(
        string $id,
        string $nome,
        string $documento,
        string $email,
        string $telefone,
        ?string $cidade,
        ?string $uf,
        string $criadoEm,
    ): self {
        return new self($id, $nome, Documento::de($documento), Email::de($email), $telefone, $cidade, $uf, $criadoEm);
    }

    /** @return array<string, mixed> */
    public function paraArray(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'tipo_pessoa' => $this->documento->tipoPessoa(),
            'documento' => $this->documento->formatado(),
            'email' => $this->email->valor(),
            'telefone' => $this->telefone,
            'cidade' => $this->cidade,
            'uf' => $this->uf,
            'criado_em' => $this->criadoEm,
        ];
    }

    /** @param array<string, string> $erros */
    private static function coletar(array &$erros, callable $fn): mixed
    {
        try {
            return $fn();
        } catch (ValidacaoException $e) {
            $erros += $e->erros();
            return null;
        }
    }

    private const UFS = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR',
        'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
}
