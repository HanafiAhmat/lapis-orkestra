<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Registries;

use BitSynama\Lapis\Framework\Collections\RouteCollection;
use BitSynama\Lapis\Framework\DTO\MiddlewareDefinition;
use BitSynama\Lapis\Framework\DTO\ResponseFilterDefinition;
use BitSynama\Lapis\Framework\DTO\RouteDefinition;
use BitSynama\Lapis\Framework\Handlers\RequestHandler;
use BitSynama\Lapis\Lapis;
use Closure;
use function array_merge;
use function array_pop;
use function array_reverse;
use function ltrim;
use function rtrim;
use function str_starts_with;
use function trim;

final class RouteRegistry
{
    private readonly RouteCollection $collection;

    /**
     * @var array<int, array{prefix:string, middlewares:array<int, MiddlewareDefinition>, filters:array<int, ResponseFilterDefinition>}>
     */
    private array $groupStack = [];

    public function __construct()
    {
        $this->collection = new RouteCollection();
    }

    /**
     * Define a group context.
     * All calls to ->add() inside $callback will have $prefix prepended
     * , $middlewares merged and $filters merged.
     * @param array<int, MiddlewareDefinition> $middlewares
     * @param array<int, ResponseFilterDefinition> $filters
     */
    public function addGroup(
        string $prefix,
        callable $callback,
        array $middlewares = [],
        array $filters = []
    ): void {
        // normalize: ensure leading slash, no trailing slash
        $p = '/' . trim($prefix, '/');
        $this->groupStack[] = [
            'prefix' => $p,
            'middlewares' => $middlewares,
            'filters' => $filters,
        ];
        try {
            $callback($this);
        } finally {
            array_pop($this->groupStack);
        }
    }

    /**
     * @param array<int|string, string>|Closure|string $handler
     * @param array<int, MiddlewareDefinition> $middlewares
     * @param array<int, ResponseFilterDefinition> $filters
     */
    public function add(
        string $method,
        string $path,
        array|Closure|string $handler,
        array $middlewares = [],
        array $filters = []
    ): void {
        // Compute full path = all group prefixes + this path
        $full = $path;
        $mergedMw = $middlewares;
        $mergedFilters = $filters;
        foreach (array_reverse($this->groupStack, true) as $grp) {
            // prepend prefix
            $full = rtrim($grp['prefix'], '/') . '/' . ltrim($full, '/');
            // merge group middleware (earlier groups first)
            $mergedMw = array_merge($grp['middlewares'], $mergedMw);
            $mergedFilters = array_merge($grp['filters'], $mergedFilters);
        }
        // normalize: leading slash, no trailing slash
        $full = '/' . trim($full, '/');

        // Build DTO
        $dto = new RouteDefinition($method, $full, new RequestHandler($handler), $mergedMw, $mergedFilters);

        // Add to collection
        $this->collection->add($dto);
    }

    public function get(string $compositeKey): RouteDefinition
    {
        return $this->collection->get($compositeKey);
    }

    public function has(string $compositeKey): bool
    {
        return $this->collection->has($compositeKey);
    }

    public function all(): RouteCollection
    {
        return $this->collection;
    }

    public function allPublicRoutes(): RouteCollection
    {
        /** @var string $adminPrefix */
        $adminPrefix = Lapis::configRegistry()->get('app.routes.admin_prefix');
        $newCollection = new RouteCollection();

        foreach ($this->collection->getIterator() as $dto) {
            if (! str_starts_with($dto->path, (string) $adminPrefix)) {
                $newCollection->add($dto);
            }
        }

        return $newCollection;
    }

    public function allAdminRoutes(): RouteCollection
    {
        /** @var string $adminPrefix */
        $adminPrefix = Lapis::configRegistry()->get('app.routes.admin_prefix');
        $newCollection = new RouteCollection();

        foreach ($this->collection->getIterator() as $dto) {
            if (str_starts_with($dto->path, $adminPrefix)) {
                $newCollection->add($dto);
            }
        }

        return $newCollection;
    }
}
