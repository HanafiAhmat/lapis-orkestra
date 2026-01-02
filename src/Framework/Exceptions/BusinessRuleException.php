<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Exceptions;

class BusinessRuleException extends HttpException
{
    public function __construct(string $message = 'Business rule violated', int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
