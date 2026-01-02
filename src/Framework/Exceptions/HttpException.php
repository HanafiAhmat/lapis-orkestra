<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Exceptions;

use RuntimeException;

abstract class HttpException extends RuntimeException
{
    protected int $statusCode = 500;

    protected string $defaultMessage = 'An unexpected error occurred.';

    public function __construct(string|null $message = null, int|null $code = null)
    {
        parent::__construct($message ?? $this->defaultMessage, $code ?? $this->statusCode);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
