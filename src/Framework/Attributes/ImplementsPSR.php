<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ImplementsPSR
{
    public function __construct(
        public readonly string $interface,
        public readonly string $psr,
        public readonly string|null $usage = null,
        public readonly string|null $link = null
    ) {
    }
}
