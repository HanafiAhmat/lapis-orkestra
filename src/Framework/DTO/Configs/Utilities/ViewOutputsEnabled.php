<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\DTO\Configs\Utilities;

class ViewOutputsEnabled
{
    public function __construct(
        public bool $html = true,
        public bool $json = true,
    ) {
    }
}
