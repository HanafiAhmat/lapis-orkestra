<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Exceptions;

use RuntimeException;
use Throwable;

class TableNotFoundException extends RuntimeException
{
    public function __construct(string $table, Throwable $previous = null)
    {
        parent::__construct("Database table '{$table}' not found or not created.", 0, $previous);
    }
}
