<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities;

use BitSynama\Lapis\Framework\DTO\Configs\Utilities\CookieConfig;
use BitSynama\Lapis\Framework\Foundation\Atlas;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Utilities\Contracts\CookieAdapterInterface;
use RuntimeException;

class CookieUtility
{
    private readonly CookieAdapterInterface $adapter;

    /**
     * Discover available providers via ProviderKey attribute, then instantiate.
     */
    public function __construct()
    {
        $this->adapter = $this->discoverAndInstantiate();
    }

    public function getAdapter(): CookieAdapterInterface
    {
        return $this->adapter;
    }

    public function has(string $name): bool
    {
        $value = $this->get($name);

        return ! empty($value) ? true : false;
    }

    public function set(string $name, string $value, int $expires, bool $httponly = true): void
    {
        $this->adapter->set($name, $value, $expires, $httponly);
    }

    public function get(string $name): mixed
    {
        return $this->adapter->get($name);
    }

    public function delete(string $name): void
    {
        $this->adapter->delete($name);
    }

    /**
     * Scans framework and child-app adapter directories for classes annotated with AdapterInfo,
     * and instantiates the one matching $adapterKey.
     */
    private function discoverAndInstantiate(): CookieAdapterInterface
    {
        /** @var string $repoDir */
        $repoDir = Lapis::varRegistry()->get('repo_dir');

        /** @var string $projectDir */
        $projectDir = Lapis::varRegistry()->get('project_dir');

        /** @var CookieConfig $cookieConfig */
        $cookieConfig = Lapis::configRegistry()->get('utility.cookie') ?? [];

        /** @var string $adapterKey */
        $adapterKey = $cookieConfig->adapter ?? 'lapis';

        $className = Atlas::discover(
            dirPath: 'Utilities.Adapters.Cookie',
            interface: CookieAdapterInterface::class,
            attribute: AdapterInfo::class,
            classSuffix: 'CookieAdapter',
            type: 'cookie',
            key: $adapterKey,
            repoDir: $repoDir,
            projectDir: $projectDir
        );

        if (empty($className)) {
            throw new RuntimeException("Cookie adapter '{$adapterKey}' not found.");
        }

        /** @var CookieAdapterInterface $instance */
        $instance = new $className();

        return $instance;
    }
}
