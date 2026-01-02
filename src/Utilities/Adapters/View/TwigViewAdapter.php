<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Adapters\View;

use BitSynama\Lapis\Utilities\AdapterInfo;
use BitSynama\Lapis\Utilities\Contracts\ViewAdapterInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use function file_exists;
use function str_replace;
use const DIRECTORY_SEPARATOR;

/**
 * A Twig‐based adapter.
 * Assumes “dot.notation” maps to “dot/notation.twig” files.
 */
#[AdapterInfo(type: 'view', key: 'twig')]
final class TwigViewAdapter implements ViewAdapterInterface
{
    private readonly Environment $twig;

    /**
     * @param string[] $templatePaths Directories containing Twig templates
     * @param array<string,mixed> $twigOptions  (optional) any Twig\Environment options
     * @param LoggerInterface|null $logger      (optional) to attach as a Twig function/extension
     */
    public function __construct(
        protected array $templatePaths,
        array $twigOptions = [],
        LoggerInterface|null $logger = null
    ) {
        // Treat “dot.notation.twig” inside each path
        $loader = new FilesystemLoader($templatePaths);
        $this->twig = new Environment($loader, $twigOptions);

        // Optionally expose a “log” function inside Twig: {{ log('message') }}
        if ($logger !== null) {
            $this->twig->addFunction(new TwigFunction('log', function (string $msg, string $level = 'info') use (
                $logger
            ) {
                $logger->{$level}($msg);
            }));
        }
    }

    public function render(string $template, array $params = []): string
    {
        // Convert dot.notation → /, append “.twig”
        $tpl = str_replace('.', '/', $template) . '.twig';

        try {
            return $this->twig->render($tpl, $params);
        } catch (Throwable $e) {
            throw new RuntimeException("Twig render error [{$tpl}]: " . $e->getMessage(), 0, $e);
        }
    }

    public function templateExists(string $template): bool
    {
        $template = str_replace('.', DIRECTORY_SEPARATOR, $template) . '.twig';

        foreach ($this->templatePaths as $path) {
            $filepath = $path . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . $template;
            if (file_exists($filepath)) {
                return true;
            }
        }

        return false;
    }
}
