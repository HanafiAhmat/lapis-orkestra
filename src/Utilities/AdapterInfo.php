<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AdapterInfo
{
    public function __construct(
        public readonly string $type,
        public readonly string $key,
        public string|null $description = null
    ) {
    }
}
