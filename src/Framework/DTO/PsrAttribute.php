<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\DTO;

/**
 * Represents a single ImplementsPSR annotation on one interface,
 * along with all the places it appears.
 */
class PsrAttribute
{
    /**
     * @param string               $interface    The full PSR interface name (e.g. Psr\Log\LoggerInterface)
     * @param string               $psr          The PSR Spec number
     * @param string|null          $usage  Human‐readable usage of why/where it’s used
     * @param string|null          $link         URL to the PSR spec fragment (optional)
     * @param string|null          $class        Full Qualified Class Name (optional)
     * @param string|null          $file         Filename (optional)
     */
    public function __construct(
        public readonly string $interface,
        public readonly string $psr,
        public readonly string|null $usage,
        public readonly string|null $link,
        public readonly string|null $class,
        public readonly string|null $file,
    ) {
    }
}
