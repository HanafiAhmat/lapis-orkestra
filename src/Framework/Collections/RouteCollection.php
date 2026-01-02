<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Collections;

use ArrayIterator;
use ArrayObject;
use BitSynama\Lapis\Framework\DTO\RouteDefinition;
use BitSynama\Lapis\Framework\Exceptions\RegistryException;
use IteratorAggregate;
use function explode;
use function rtrim;
use function strtoupper;

/**
 * A typed collection of RouteDefinition objects, keyed by route name and path.
 *
 * @implements IteratorAggregate<string, RouteDefinition>
 */
final class RouteCollection implements IteratorAggregate
{
    /**
     * @var ArrayObject<string, RouteDefinition>
     */
    private ArrayObject $routes;

    public function __construct()
    {
        // Initialize an ArrayObject to hold RouteDefinition instances keyed by composite key
        $this->routes = new ArrayObject([]);
    }

    /**
     * Add a RouteDefinition to the collection.
     */
    public function add(RouteDefinition $route): void
    {
        $key = $this->generateKey($route->method, $route->path);
        $this->routes[$key] = $route;
    }

    /**
     * Retrieve a RouteDefinition by HTTP method and path pattern.
     */
    public function get(string $compositeKey): RouteDefinition
    {
        [$method, $pattern] = explode(':', $compositeKey, 2);

        return $this->routes[$compositeKey]
            ?? throw new RegistryException("Route not found: {$method} {$pattern}");
    }

    /**
     * Check if a route exists for given method and pattern.
     */
    public function has(string $compositeKey): bool
    {
        return isset($this->routes[$compositeKey]);
    }

    /**
     * Return the internal ArrayObject of routes.
     *
     * @return ArrayObject<string, RouteDefinition>
     */
    public function all(): ArrayObject
    {
        return $this->routes;
    }

    /**
     * @return ArrayIterator<string, RouteDefinition>
     */
    public function getIterator(): ArrayIterator
    {
        return $this->routes->getIterator();
    }

    /**
     * Build the composite key for storage and lookup.
     */
    private function generateKey(string $method, string $path): string
    {
        return strtoupper($method) . ':' . rtrim($path, '/');
    }
}
