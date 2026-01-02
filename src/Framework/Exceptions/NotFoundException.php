<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Exceptions;

class NotFoundException extends HttpException
{
    public function __construct(string $message = 'Resource not found', int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
