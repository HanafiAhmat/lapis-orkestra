<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Exceptions;

use BitSynama\Lapis\Framework\Exceptions\HttpException;

class InvalidCredentialsException extends HttpException
{
    public function __construct(string $message = 'Invalid login credentials', int $code = 401)
    {
        parent::__construct($message, $code);
    }
}
