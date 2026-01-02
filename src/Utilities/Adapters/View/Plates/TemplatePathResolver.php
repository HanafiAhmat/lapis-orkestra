<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Adapters\View\Plates;

use League\Plates\Exception\TemplateNotFound;
use League\Plates\Template\Name;
use League\Plates\Template\ResolveTemplatePath;
use function array_map;
use function implode;
use function is_file;
use function rtrim;
use function sprintf;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;
use const DIRECTORY_SEPARATOR;

/**
 * Implements Plates\ResolveTemplatePath to allow searching multiple base
 * view directories, each with subfolders like “Templates/”, “Layouts/”,
 * “Partials/”, etc. We will interpret a name like “layouts:main” or
 * “partials:widget.recent” to map to those subfolders.
 *
 * $viewRoots is an ordered list of absolute directories. In each, we expect:
 *   {viewRoot}/Templates/
 *   {viewRoot}/Layouts/
 *   {viewRoot}/Partials/
 *
 * When Plates asks to resolve a Name $name, $name->getName() may be:
 *   - “foo.bar.baz”           → look in {any}/Templates/foo/bar/baz.php
 *   - “layouts:foo”           → look in {any}/Layouts/foo.php
 *   - “partials:widget.recent”→ look in {any}/Partials/widget/recent.php
 *
 * We return the first filepath that exists, or throw TemplateNotFound.
 */
class TemplatePathResolver implements ResolveTemplatePath
{
    /**
     * @var string[]
     */
    private readonly array $viewRoots;

    /**
     * @param string[] $viewRoots
     *    Ordered list of absolute directories where you keep your view subfolders.
     *    e.g. [ '/path/to/Core/View', '/path/to/ModuleA/View', '/path/to/App/View' ]
     */
    public function __construct(array $viewRoots)
    {
        $this->viewRoots = array_map(fn ($p) => rtrim($p, DIRECTORY_SEPARATOR), $viewRoots);
    }

    public function __invoke(Name $name): string
    {
        $rawName = $name->getName();     // e.g. “home.index” or “layouts:main”
        $template = '';
        $subFolder = 'Templates';          // default subfolder if no prefix

        // 1) Detect prefixes “layouts:” or “partials:”
        if (str_starts_with($rawName, 'layouts:')) {
            $subFolder = 'Layouts';
            $template = substr($rawName, strlen('layouts:'));
        } elseif (str_starts_with($rawName, 'partials:')) {
            $subFolder = 'Partials';
            $template = substr($rawName, strlen('partials:'));
        } elseif (str_starts_with($rawName, 'widgets:')) {
            $subFolder = 'Widgets';
            $template = substr($rawName, strlen('widgets:'));
        } else {
            $subFolder = 'Templates';
            $template = $rawName;
        }

        // 2) Convert “dot.notation” → slashed path, and add “.php”
        //    “foo.bar.baz” → “foo/bar/baz.php”
        $relativePath = str_replace('.', DIRECTORY_SEPARATOR, $template) . '.php';

        // 3) For each viewRoot, look under {viewRoot}/{subFolder}/{relativePath}
        foreach ($this->viewRoots as $root) {
            $candidate = $root
                       . DIRECTORY_SEPARATOR
                       . $subFolder
                       . DIRECTORY_SEPARATOR
                       . $relativePath;

            if (is_file($candidate)) {
                return $candidate;
            }
        }

        // 4) If we reach here, nothing was found. Throw TemplateNotFound
        throw new TemplateNotFound(
            $rawName,
            [$name->getPath()],
            sprintf(
                'The template "%s" (subfolder "%s", path "%s") could not be found in any of: %s',
                $rawName,
                $subFolder,
                $relativePath,
                implode(', ', $this->viewRoots)
            )
        );
    }
}
