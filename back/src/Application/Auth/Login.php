<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\Shared\Email;
use App\Domain\Shared\ValidacaoException;
use App\Domain\Usuario\UsuarioRepository;

/** Caso de uso: autenticar por e-mail e senha e emitir o JWT. */
final class Login
{
    /** Hash bcrypt de referência: compara mesmo quando o e-mail não existe (tempo constante). */
    private const HASH_FALSO = '$2y$12$Ph4CNAySoJqjAA547ssrweU3215I1RXwD4znksEvZEbHq/Ygpdxi6';

    public function __construct(
        private readonly UsuarioRepository $usuarios,
        private readonly PasswordHasher $hasher,
        private readonly TokenService $tokens,
        private readonly LimiteTentativas $limite,
    ) {
    }

    /** @return array{token: string, expira_em: int, usuario: array{id: string, nome: string, email: string}} */
    public function executar(string $email, string $senha, string $ip): array
    {
        if (trim($senha) === '') {
            throw new ValidacaoException(['senha' => 'Informe a senha.']);
        }
        $emailValido = Email::de($email);
        $chave = hash('sha256', $ip . '|' . $emailValido->valor());

        if ($this->limite->bloqueado($chave)) {
            throw new MuitasTentativasException();
        }

        $usuario = $this->usuarios->porEmail($emailValido->valor());
        $confere = $this->hasher->confere($senha, $usuario?->senhaHash ?? self::HASH_FALSO);

        if ($usuario === null || !$usuario->ativo || !$confere) {
            $this->limite->registrarFalha($chave);
            throw new CredenciaisInvalidasException();
        }

        $this->limite->limpar($chave);
        if ($this->hasher->precisaRehash($usuario->senhaHash)) {
            $this->usuarios->atualizarSenhaHash($usuario->id, $this->hasher->hash($senha));
        }

        return $this->tokens->emitir($usuario) + ['usuario' => $usuario->publico()];
    }
}
