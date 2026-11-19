<?php

declare(strict_types=1);

namespace App\Domain\Shared;

/** Regra de unicidade violada (ex.: documento já cadastrado). */
final class ConflitoException extends \DomainException
{
}
