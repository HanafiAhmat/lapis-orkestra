<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class SecurityCompliance
{
    public function __construct(
        public string $standard,
        public string|null $description = null
    ) {
    }
}
