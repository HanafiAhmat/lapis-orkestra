<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\DTO;

class MiddlewareDefinition
{
    /**
     * @param array<string, mixed> $vars
     */
    public function __construct(
        public readonly string $id,
        public readonly array $vars = []
    ) {
    }
}
