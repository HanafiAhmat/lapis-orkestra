<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Foundation;

use RuntimeException;
use function extract;
use function is_file;
use function ob_get_clean;
use function ob_start;
use function rtrim;
use function str_replace;
use const DIRECTORY_SEPARATOR;
use const EXTR_SKIP;

/**
 * Very minimal template engine that supports dot notation and multiple search paths.
 */
final class TemplateEngine
{
    /**
     * @param string[] $templatePaths   Directories to search for view templates.
     * @param string[] $layoutPaths Directories to search for layout templates.
     */
    public function __construct(
        private readonly array $templatePaths,
        private readonly array $layoutPaths
    ) {
    }

    /**
     * Render a view with variables, wrapped in a layout.
     *
     * @param string $template Dot-notation view name (e.g. "Module.ViewName").
     * @param array<string, mixed> $vars Variables to extract into the view.
     * @param string $layout Dot-notation layout name (e.g. "Main.default").
     * @return string The final rendered HTML.
     */
    public function render(string $template, array $vars = [], string $layout = 'default'): string
    {
        // Convert dot notation to directory separators
        $templatePathFragment = str_replace('.', DIRECTORY_SEPARATOR, $template);
        $layoutPathFragment = str_replace('.', DIRECTORY_SEPARATOR, $layout);

        // $data['user'] = self::getLoggedInUser();
        // $data['csrf_token'] = self::getCsrfToken();
        // $data['nonce_token'] = self::getNonceToken();
        // $data['cdn_url'] = Lapis::configRegistry()->get('app.cdn_url');
        // $data['brand_name'] = Lapis::configRegistry()->get('app.name');

        // 1) Render the view into $content
        extract($vars, EXTR_SKIP);
        ob_start();
        $templateFound = false;
        foreach ($this->templatePaths as $base) {
            $file = rtrim($base, DIRECTORY_SEPARATOR)
                  . DIRECTORY_SEPARATOR
                  . $templatePathFragment
                  . '.php';
            if (is_file($file)) {
                include $file;
                $templateFound = true;
                break;
            }
        }
        if (! $templateFound) {
            throw new RuntimeException("View template '{$template}.php' not found in any configured view path.");
        }
        $content = ob_get_clean();

        // 2) Render within the layout
        ob_start();
        $layoutFound = false;
        foreach ($this->layoutPaths as $base) {
            $file = rtrim($base, DIRECTORY_SEPARATOR)
                  . DIRECTORY_SEPARATOR
                  . $layoutPathFragment
                  . '.php';
            if (is_file($file)) {
                // The layout can use the $content variable
                include $file;
                $layoutFound = true;
                break;
            }
        }
        if (! $layoutFound) {
            throw new RuntimeException("Layout template '{$layout}.php' not found in any configured layout path.");
        }
        $html = ob_get_clean();

        /** @var string $html */
        return $html;
    }
}
