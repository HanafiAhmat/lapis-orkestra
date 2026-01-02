<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities;

use BitSynama\Lapis\Framework\DTO\Configs\Utilities\ViewConfig;
use BitSynama\Lapis\Framework\DTO\ModuleDefinition;
use BitSynama\Lapis\Framework\Foundation\Atlas;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Utilities\Contracts\ViewAdapterInterface;
use RuntimeException;
use function array_filter;
use function array_merge;
use function array_reverse;
use function http_build_query;
use function implode;
use function is_array;
use function is_dir;
use function strtolower;
use function ucfirst;
use const DIRECTORY_SEPARATOR;

/**
 * Finds all classes implementing ViewAdapterInterface under
 * Core/Adapter/View (framework) and app/Core/Adapter/View (child),
 * then instantiates the one tagged with #[AdapterInfo($adapterKey)].
 */
final class ViewUtility
{
    private readonly ViewAdapterInterface $adapter;

    /**
     * @var array<int, string>
     */
    private array $viewPaths;

    /**
     * @var array<string, mixed>
     */
    private readonly array $extraArgs;

    private readonly string $adapterKey;

    private readonly string $repoDir;

    private readonly string $projectDir;

    public function __construct()
    {
        /** @var ViewConfig $viewConfig */
        $viewConfig = Lapis::configRegistry()->get('utility.view') ?? [];

        /** @var string $adapterKey */
        $adapterKey = $viewConfig->adapter ?? 'plates';
        $this->adapterKey = $adapterKey;

        /** @var string $repoDir */
        $repoDir = Lapis::varRegistry()->get('repo_dir');
        $this->repoDir = $repoDir;

        /** @var string $projectDir */
        $projectDir = Lapis::varRegistry()->get('project_dir');
        $this->projectDir = $projectDir;

        /** @var array<string, mixed> $extraArgs */
        $extraArgs = $viewConfig->extra ?? [];
        $this->extraArgs = $extraArgs;

        $this->discoverViewDirs();

        $this->adapter = $this->discoverAndInstantiate();
    }

    public function getAdapter(): ViewAdapterInterface
    {
        return $this->adapter;
    }

    public function templateExists(string $template): bool
    {
        return $this->adapter->templateExists($template);
    }

    /**
     * @param array<string, scalar|null|array<string, scalar|null|array<string, scalar|null>>> $currentQuery
     */
    public function paginationUrl(
        string $baseUrl,
        array $currentQuery,
        int $targetPage,
        int|null $targetLimit = null
    ): string {
        $page = $currentQuery['page'] ?? [];
        if (! is_array($page)) {
            $page = [];
        }

        $page['num'] = $targetPage;

        if ($targetLimit !== null) {
            $page['limit'] = $targetLimit;
            $page['num'] = 1;
        }

        $currentQuery['page'] = $page;

        $query = http_build_query($currentQuery);
        return $query === '' ? $baseUrl : ($baseUrl . '?' . $query);
    }

    /**
     * Recursively scan for any classes under Adapter/View that have #[AdapterInfo($adapterKey)].
     */
    public function discoverViewDirs(): void
    {
        $ds = DIRECTORY_SEPARATOR;
        $adapterDirName = ucfirst(strtolower($this->adapterKey));
        $frameworkViewDir = implode($ds, [$this->repoDir, 'src', 'Framework', 'Views', $adapterDirName]);
        $appViewDir = implode($ds, [$this->projectDir, 'app', 'Views', $adapterDirName]);
        $appOverrideFrameworkViewDir = implode($ds, [$this->projectDir, 'app', 'Framework', 'Views', $adapterDirName]);

        /** @var array<string, array<string, ModuleDefinition[]>> $modules */
        $modules = Lapis::configRegistry()->get('modules') ?? [];
        $allViewDirs = [$appViewDir, $appOverrideFrameworkViewDir];
        $reversedModules = array_reverse($modules);
        // foreach ($modules as $type => $set) {
        foreach ($reversedModules as $compositeKey => $moduleDefinition) {
            /** @var ModuleDefinition $moduleDefinition */
            if (! $moduleDefinition->enabled) {
                continue;
            }

            $allViewDirs[] = implode($ds, [$moduleDefinition->path, 'Views', $adapterDirName]);
        }
        $allViewDirs[] = $frameworkViewDir;

        // Combine in priority order (app -> module → core)
        $this->viewPaths = array_filter($allViewDirs, is_dir(...));
    }

    public function getTemplatePrefix(): string
    {
        /** @var string $adminPrefix */
        $adminPrefix = Lapis::configRegistry()->get('app.routes.admin_prefix');

        $templatePrefix = 'public.';
        if (Lapis::requestUtility()->isAdminSite()) {
            $templatePrefix = 'admin.';
        }

        return $templatePrefix;
    }

    /**
     * Recursively scan for any classes under Adapter/View that have #[AdapterInfo($adapterKey)].
     */
    private function discoverAndInstantiate(): ViewAdapterInterface
    {
        $className = Atlas::discover(
            dirPath: 'Utilities.Adapters.View',
            interface: ViewAdapterInterface::class,
            attribute: AdapterInfo::class,
            classSuffix: 'ViewAdapter',
            type: 'view',
            key: $this->adapterKey,
            repoDir: $this->repoDir,
            projectDir: $this->projectDir
        );

        if (empty($className)) {
            throw new RuntimeException("No ViewEngine found for provider '{$this->adapterKey}'.");
        }

        /** @var ViewAdapterInterface $instance */
        $instance = new $className(...array_merge([$this->viewPaths], $this->extraArgs));

        return $instance;
    }
}
