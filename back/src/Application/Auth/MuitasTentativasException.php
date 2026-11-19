<?php

declare(strict_types=1);

namespace App\Application\Auth;

final class MuitasTentativasException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Muitas tentativas. Aguarde alguns minutos e tente de novo.');
    }
}
