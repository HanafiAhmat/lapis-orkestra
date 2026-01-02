<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Exceptions;

class ValidationException extends HttpException
{
    /**
     * @param array<string, string> $errors
     */
    public function __construct(
        protected array $errors,
        string|null $message = 'Validation failed',
        int|null $code = 422
    ) {
        parent::__construct($message, $code);
    }

    /**
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
