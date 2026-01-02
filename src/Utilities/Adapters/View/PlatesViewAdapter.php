<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Adapters\View;

use BitSynama\Lapis\Utilities\AdapterInfo;
use BitSynama\Lapis\Utilities\Adapters\View\Plates\TemplatePathResolver;
use BitSynama\Lapis\Utilities\Contracts\ViewAdapterInterface;
use InvalidArgumentException;
use League\Plates\Engine;
use RuntimeException;
use Throwable;
use function file_exists;
use function sprintf;
use function str_replace;
use const DIRECTORY_SEPARATOR;

/**
 * A Plates adapter that configures a custom ResolveTemplatePath.
 *
 * Usage:
 *   $viewRoots = [
 *     '/full/path/to/Framework/Views',
 *     '/full/path/to/ModuleFoo/View',
 *     '/full/path/to/App/View',
 *   ];
 *
 *   $adapter = new PlatesViewAdapter($viewRoots);
 *   echo $adapter->render('home.index',   $data);       // looks under /…/Templates/home/index.php
 *   echo $adapter->render('layouts:main', $data);       // looks under /…/Layouts/main.php
 *   echo $adapter->render('partials:nav', $data);       // looks under /…/Partials/nav.php
 */
#[AdapterInfo(type: 'view', key: 'plates')]
final class PlatesViewAdapter implements ViewAdapterInterface
{
    private readonly Engine $engine;

    private string $extension = 'php';

    /**
     * @param string[] $viewRoots  Absolute paths to base “View” folders (each contains subfolders Templates/, Layouts/, Partials/, etc.)
     */
    public function __construct(
        protected array $viewRoots
    ) {
        if (empty($viewRoots)) {
            throw new InvalidArgumentException('Pass at least one view‐root directory to PlatesViewAdapter.');
        }

        // 1) Instantiate a bare Plates Engine
        $this->engine = new Engine(null, $this->extension);

        // 2) Register our custom ResolveTemplatePath
        $resolver = new TemplatePathResolver($viewRoots);
        $this->engine->setResolveTemplatePath($resolver);
    }

    /**
     * @param string $template  Either “dot.notation” (defaults to subfolder “Templates”),
     *                          or prefixed “layouts:foo” or “partials:bar.baz”
     * @param array<string,mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        try {
            return $this->engine->render($template, $data);
        } catch (Throwable $e) {
            throw new RuntimeException(
                sprintf('PlatesViewAdapter error rendering "%s": %s', $template, $e->getMessage()),
                0,
                $e
            );
        }
    }

    public function templateExists(string $template): bool
    {
        $template = str_replace('.', DIRECTORY_SEPARATOR, $template) . '.' . $this->extension;

        foreach ($this->viewRoots as $path) {
            $filepath = $path . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . $template;
            if (file_exists($filepath)) {
                return true;
            }
        }

        return false;
    }
}
