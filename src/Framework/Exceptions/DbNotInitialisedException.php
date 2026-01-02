<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Exceptions;

use RuntimeException;
use Throwable;

class DbNotInitialisedException extends RuntimeException
{
    public function __construct(Throwable $previous = null)
    {
        parent::__construct('Required database tables have not been created.', 0, $previous);
    }
}
