<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Foundation;

use BitSynama\Lapis\Lapis;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use function file_exists;
use function file_get_contents;
use function str_replace;
use function strip_tags;
use const DIRECTORY_SEPARATOR;

class EmailComposer
{
    /**
     * @param array<string, mixed> $data
     */
    public static function renderHtml(
        string $template,
        array $data = [],
        string|null $layout = null,
        string|null $css = null
    ): string {
        $templateEngine = Lapis::viewUtility()->getAdapter();
        $html = $templateEngine->render($template, $data);

        // Inline CSS
        $ds = DIRECTORY_SEPARATOR;
        $css = $css ? str_replace('.', $ds, $css) : 'Styles' . $ds . 'email.css';

        /** @var string $projectDir */
        $projectDir = Lapis::varRegistry()->get('project_dir');

        /** @var string $repoDir */
        $repoDir = Lapis::varRegistry()->get('repo_dir');

        $paths = [
            $projectDir . $ds . 'app' . $ds . 'Framework' . $ds . 'Views' . $ds . $css,
            $repoDir . $ds . 'src' . $ds . 'Framework' . $ds . 'Views' . $ds . $css,
        ];
        foreach ($paths as $path) {
            if (file_exists($path)) {
                $cssContent = file_get_contents($path);
                break;
            }
        }

        if (isset($cssContent) && ! empty($cssContent)) {
            $inliner = new CssToInlineStyles();
            $html = $inliner->convert($html, $cssContent);
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function renderText(string $template, array $data = [], string|null $layout = null): string
    {
        $templateEngine = Lapis::viewUtility()->getAdapter();
        $text = $templateEngine->render($template, $data);

        if (empty($text)) {
            return '';
        }

        return strip_tags($text);
    }
}
