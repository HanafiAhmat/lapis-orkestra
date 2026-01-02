<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities;

use BitSynama\Lapis\Framework\DTO\Configs\Utilities\CookieConfig;
use BitSynama\Lapis\Framework\DTO\Configs\Utilities\CookieParams;
use BitSynama\Lapis\Framework\DTO\Configs\Utilities\SessionConfig;
use BitSynama\Lapis\Framework\DTO\Configs\Utilities\SessionVariant;
use BitSynama\Lapis\Framework\Foundation\Atlas;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Utilities\Contracts\SessionAdapterInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use function array_merge;
use function str_starts_with;

/**
 * Thin wrapper around Aura\Session to give you a Session plus named segments.
 */
final class SessionUtility
{
    /**
     * @var array<string, SessionVariant>
     */
    private array $variants;

    private readonly SessionAdapterInterface $adapter;

    public function __construct()
    {
        $this->adapter = $this->discoverAndInstantiate();
    }

    /**
     * Given the incoming request, picks the correct variant
     * (by path prefix or host match), then sets name + cookie params.
     */
    public function initialize(ServerRequestInterface $request): void
    {
        $host = $request->getUri()
            ->getHost();
        $path = $request->getUri()
            ->getPath();

        /** @var SessionVariant $cfg */
        $cfg = $this->variants['default'];

        // find a matching override
        foreach ($this->variants as $key => $variant) {
            if ($key === 'default') {
                continue;
            }

            if (
                isset($variant->path)
                && (
                    str_starts_with($path, $variant->path)
                    || $host === $variant->path
                )
            ) {
                $cfg = $variant;
                break;
            }
        }

        $this->adapter->setName($cfg->name);

        $params = array_merge($this->adapter->getCookieParams(), $cfg->cookieParams->toArray());
        $this->adapter->setCookieParams($params);
    }

    public function start(): void
    {
        $this->adapter->start();
    }

    public function commit(): void
    {
        $this->adapter->commit();
    }

    public function setFlash(string $key, mixed $val): void
    {
        $this->adapter->setFlash($key, $val);
    }

    public function getFlash(string $key, mixed $alt = null): mixed
    {
        return $this->adapter->getFlash($key, $alt);
    }

    public function clearFlash(): void
    {
        $this->adapter->clearFlash();
    }

    public function setAlert(string $key, mixed $val): void
    {
        $this->adapter->setAlert($key, $val);
    }

    public function getAlert(string $key, mixed $alt = null): mixed
    {
        return $this->adapter->getAlert($key, $alt);
    }

    public function getCsrfToken(): string
    {
        return $this->adapter->getCsrfToken();
    }

    public function isCsrfTokenValid(string $token): bool
    {
        return $this->adapter->isCsrfTokenValid($token);
    }

    public function has(string $var): bool
    {
        return $this->adapter->has($var);
    }

    public function get(string $var, mixed $alt = null): mixed
    {
        return $this->adapter->get($var, $alt);
    }

    public function set(string $var, string $value): void
    {
        $this->adapter->set($var, $value);
    }

    public function remove(string $var): void
    {
        $this->adapter->remove($var);
    }

    /**
     * Scans framework and child-app adapter directories for classes annotated with AdapterInfo,
     * and instantiates the one matching $adapterKey.
     */
    private function discoverAndInstantiate(): SessionAdapterInterface
    {
        /** @var string $repoDir */
        $repoDir = Lapis::varRegistry()->get('repo_dir');

        /** @var string $projectDir */
        $projectDir = Lapis::varRegistry()->get('project_dir');

        /** @var SessionConfig $sessionConfig */
        $sessionConfig = Lapis::configRegistry()->get('utility.session');

        /** @var CookieConfig $cookieConfig */
        $cookieConfig = Lapis::configRegistry()->get('utility.cookie');

        /** @var CookieParams $globalCookieParams */
        $globalCookieParams = $cookieConfig->params;

        /** @var string $adapterKey */
        $adapterKey = $sessionConfig->adapter ?? 'aura';

        /** @var string $sessionSegment */
        $sessionSegment = $sessionConfig->segment ?: 'LapisOrkestra';

        /** @var array<string, SessionVariant> $variants */
        $variants = $sessionConfig->variants;
        $this->variants = $variants;

        $className = Atlas::discover(
            dirPath: 'Utilities.Adapters.Session',
            interface: SessionAdapterInterface::class,
            attribute: AdapterInfo::class,
            classSuffix: 'SessionAdapter',
            type: 'session',
            key: $adapterKey,
            repoDir: $repoDir,
            projectDir: $projectDir
        );

        if (empty($className)) {
            throw new RuntimeException("Session adapter '{$adapterKey}' not found.");
        }

        /** @var SessionAdapterInterface $instance */
        $instance = new $className($sessionSegment, $globalCookieParams);

        return $instance;
    }
}
