<?php

declare(strict_types=1);

namespace App\Application\Auth;

final class CredenciaisInvalidasException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('E-mail ou senha incorretos.');
    }
}
