<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Adapters\View;

use BitSynama\Lapis\Utilities\AdapterInfo;
use BitSynama\Lapis\Utilities\Contracts\ViewAdapterInterface;
use RuntimeException;
use function extract;
use function file_exists;
use function is_file;
use function is_string;
use function ob_get_clean;
use function ob_start;
use function rtrim;
use function str_replace;
use const DIRECTORY_SEPARATOR;
use const EXTR_SKIP;

/**
 * A very simple PHP‐based, dot‐notation engine.
 *
 * It looks in one or more view‐directories for “dot.notation.php” files.
 */
#[AdapterInfo(type: 'view', key: 'lapis')]
final class LapisViewAdapter implements ViewAdapterInterface
{
    /**
     * @param string[] $viewPaths   List of directories to search for templates.
     *                             (each dir ends with no “.php”)
     */
    public function __construct(
        private readonly array $viewPaths
    ) {
    }

    public function render(string $template, array $data = []): string
    {
        // Convert “dot.notation” → folder separators
        $templateFile = str_replace('.', DIRECTORY_SEPARATOR, $template) . '.php';

        // 1) Locate and include the template, capture output:
        extract($data, EXTR_SKIP);
        ob_start();
        $found = false;
        foreach ($this->viewPaths as $base) {
            $path = rtrim($base, DIRECTORY_SEPARATOR)
                  . DIRECTORY_SEPARATOR
                  . $templateFile;
            if (is_file($path)) {
                include $path;
                $found = true;
                break;
            }
        }
        if (! $found) {
            throw new RuntimeException("PHP view not found: {$templateFile}");
        }
        $contents = ob_get_clean();

        return is_string($contents) ? $contents : '';
    }

    public function templateExists(string $template): bool
    {
        $template = str_replace('.', DIRECTORY_SEPARATOR, $template) . '.php';

        foreach ($this->viewPaths as $path) {
            $filepath = $path . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . $template;
            if (file_exists($filepath)) {
                return true;
            }
        }

        return false;
    }
}
