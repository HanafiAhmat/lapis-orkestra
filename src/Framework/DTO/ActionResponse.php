<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\DTO;

use InvalidArgumentException;
use function in_array;

class ActionResponse
{
    public const SUCCESS = 'success';

    public const FAIL = 'fail';

    public const ERROR = 'error';

    /**
     * @param array<string,mixed> $data
     */
    public function __construct(
        public readonly string $status,       // success|fail|error
        public readonly array $data = [],
        public readonly string $message = '',
        public readonly int $statusCode = 200,
        public readonly string|null $template = null,   // e.g. 'admin.default' or 'public.default'
        public readonly string $htmlRedirect = '',  // empty = no redirect
    ) {
        if (! in_array($status, [self::SUCCESS, self::FAIL, self::ERROR], true)) {
            throw new InvalidArgumentException('Invalid ActionResponse status: ' . $status);
        }
    }

    // ---------- Light factories (ergonomic, still DTO-only) ----------

    /**
     * @param array<string,mixed> $data
     */
    public static function success(
        array $data = [],
        string $message = '',
        int $statusCode = 200,
        string|null $template = null,
        string $htmlRedirect = '',
    ): self {
        return new self(self::SUCCESS, $data, $message, $statusCode, $template, $htmlRedirect);
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fail(
        array $data = [],
        string $message = '',
        int $statusCode = 422,
        string|null $template = null,
        string $htmlRedirect = '',
    ): self {
        return new self(self::FAIL, $data, $message, $statusCode, $template, $htmlRedirect);
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function error(
        array $data = [],
        string $message = '',
        int $statusCode = 500,
        string|null $template = null,
        string $htmlRedirect = '',
    ): self {
        return new self(self::ERROR, $data, $message, $statusCode, $template, $htmlRedirect);
    }

    // ---------- Immutability helpers (non-mutating clones) ----------

    public function withTemplate(string|null $template): self
    {
        return new self($this->status, $this->data, $this->message, $this->statusCode, $template, $this->htmlRedirect);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function withData(array $data): self
    {
        return new self($this->status, $data, $this->message, $this->statusCode, $this->template, $this->htmlRedirect);
    }

    /**
     * @param array<string,mixed> $extra
     */
    public function withMergedData(array $extra): self
    {
        return $this->withData($extra + $this->data);
    }

    public function withMessage(string $message): self
    {
        return new self($this->status, $this->data, $message, $this->statusCode, $this->template, $this->htmlRedirect);
    }

    public function withStatusCode(int $statusCode): self
    {
        return new self($this->status, $this->data, $this->message, $statusCode, $this->template, $this->htmlRedirect);
    }

    public function withRedirect(string $url): self
    {
        return new self($this->status, $this->data, $this->message, $this->statusCode, $this->template, $url);
    }

    // ---------- Tiny helpers ----------

    public function isSuccess(): bool
    {
        return $this->status === self::SUCCESS;
    }

    public function isFail(): bool
    {
        return $this->status === self::FAIL;
    }

    public function isError(): bool
    {
        return $this->status === self::ERROR;
    }
}
