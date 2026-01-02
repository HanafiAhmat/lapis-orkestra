<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities;

use BitSynama\Lapis\Framework\DTO\RouteMatch;
use BitSynama\Lapis\Framework\Foundation\Atlas;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Utilities\Contracts\RouterAdapterInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final class RouterUtility
{
    private readonly RouterAdapterInterface $adapter;

    public function __construct()
    {
        $this->adapter = $this->discoverAndInstantiate();
    }

    public function getRouter(): RouterAdapterInterface
    {
        return $this->adapter;
    }

    public function match(ServerRequestInterface $request): RouteMatch
    {
        return $this->adapter->match($request);
    }

    /**
     * Scans framework and child-app adapter directories for classes annotated with AdapterInfo,
     * and instantiates the one matching $adapterKey.
     */
    private function discoverAndInstantiate(): RouterAdapterInterface
    {
        /** @var string $repoDir */
        $repoDir = Lapis::varRegistry()->get('repo_dir');

        /** @var string $projectDir */
        $projectDir = Lapis::varRegistry()->get('project_dir');

        /** @var string $adapterKey */
        $adapterKey = Lapis::configRegistry()->get('utility.router') ?? 'fastroute';

        $className = Atlas::discover(
            dirPath: 'Utilities.Adapters.Router',
            interface: RouterAdapterInterface::class,
            attribute: AdapterInfo::class,
            classSuffix: 'RouterAdapter',
            type: 'router',
            key: $adapterKey,
            repoDir: $repoDir,
            projectDir: $projectDir
        );

        if (empty($className)) {
            throw new RuntimeException("Router adapter '{$adapterKey}' not found.");
        }

        /** @var RouterAdapterInterface $instance */
        $instance = new $className();

        return $instance;
    }
}
