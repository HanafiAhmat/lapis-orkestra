<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Foundation;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Framework\Collections\PsrAttributeCollection;
use BitSynama\Lapis\Framework\DTO\PsrAttribute;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;
use function class_exists;
use function count;
use function file_get_contents;
use function is_array;
use function is_dir;
use function ltrim;
use function token_get_all;
use const T_CLASS;
use const T_NAMESPACE;
use const T_NEW;
use const T_STRING;
use const T_WHITESPACE;

/**
 * Scans PHP source files for ImplementsPSR attributes via reflection.
 */
final class PsrAttributeParser
{
    public function __construct(
        private readonly string $scanDir
    ) {
    }

    /**
     * Parse all PHP classes under configured directories, reflect the ImplementsPSR attributes, and
     * return a structured summary keyed by the PSR interface name.
     */
    public function parse(): PsrAttributeCollection
    {
        $collection = new PsrAttributeCollection();

        $baseDir = $this->scanDir;
        if (! is_dir($baseDir)) {
            return $collection;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
        /** @var SplFileInfo $fileInfo */
        foreach ($iterator as $fileInfo) {
            if (! $fileInfo->isFile() || $fileInfo->getExtension() !== 'php') {
                continue;
            }

            $filePath = $fileInfo->getPathname();
            $relPath = $fileInfo->getFilename();

            // Attempt to discover any classes in this file via token scanning
            $classesInFile = $this->getClassesFromFile($filePath);
            foreach ($classesInFile as $fqcn) {
                if (! class_exists($fqcn)) {
                    continue;
                }

                $ref = new ReflectionClass($fqcn);
                $attributes = $ref->getAttributes(ImplementsPSR::class);
                foreach ($attributes as $attr) {
                    /** @var ImplementsPSR $instance */
                    $instance = $attr->newInstance();
                    $collection->add(new PsrAttribute(
                        interface: $instance->interface,
                        psr: $instance->psr,
                        usage: $instance->usage,
                        link: $instance->link,
                        class: $fqcn,
                        file: $relPath,
                    ));
                }
            }
        }

        // we don’t need to sort keys manually; the collection is keyed by PSR interface name
        return $collection;
    }

    /**
     * Returns an array of fully-qualified class names declared in the given PHP file.
     *
     * @return array<int, string>
     */
    private function getClassesFromFile(string $filePath): array
    {
        $phpCode = file_get_contents($filePath);
        if (! $phpCode) {
            return [];
        }

        $tokens = token_get_all($phpCode);
        $classes = [];
        $namespace = '';
        $i = 0;
        $count = count($tokens);

        while ($i < $count) {
            $token = $tokens[$i];
            if (is_array($token)) {
                if ($token[0] === T_NAMESPACE) {
                    // parse namespace
                    // $namespace = '';
                    // $i++;
                    // while ($i < $count && is_array($tokens[$i]) && in_array($tokens[$i][0], [T_STRING, T_NS_SEPARATOR], true)) {
                    //     $namespace .= $tokens[$i][1];
                    //     $i++;
                    // }

                    $i += 2;
                    $namespace = $tokens[$i][1];
                }
                if ($token[0] === T_CLASS) {
                    // skip anonymous classes
                    $isAnonymous = false;
                    // look back few tokens to check for T_NEW
                    $j = $i - 1;
                    while ($j >= 0 && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
                        $j--;
                    }
                    if ($j >= 0 && is_array($tokens[$j]) && $tokens[$j][0] === T_NEW) {
                        $isAnonymous = true;
                    }
                    if (! $isAnonymous) {
                        // next non-whitespace token is class name
                        $i++;
                        while ($i < $count && is_array($tokens[$i]) && $tokens[$i][0] !== T_STRING) {
                            $i++;
                        }
                        if ($i < $count && is_array($tokens[$i]) && $tokens[$i][0] === T_STRING) {
                            $className = $tokens[$i][1];
                            $fqcn = ltrim($namespace . '\\' . $className, '\\');
                            $classes[] = $fqcn;
                        }
                    }
                }
            }
            $i++;
        }

        return $classes;
    }
}
