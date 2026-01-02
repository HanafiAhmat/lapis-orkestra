<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Framework\Foundation\Atlas;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Utilities\Contracts\RequestAdapterInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;
use function count;
use function filter_var;
use function is_array;
use function str_contains;
use function str_starts_with;
use function strtolower;
use function strtoupper;
use function trim;
use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_VALIDATE_IP;

#[ImplementsPSR(
    ServerRequestInterface::class,
    psr: 'PSR-7',
    usage: 'Wrapper for HTTP Server Request Interface',
    link: 'https://www.php-fig.org/psr/psr-7/#321-psrhttpmessageserverrequestinterface'
)]
final class RequestUtility
{
    private readonly ServerRequestInterface $serverRequest;

    private readonly mixed $httpFactory;

    public function __construct()
    {
        $requestAdapter = $this->discoverAndInstantiate();

        $this->serverRequest = $requestAdapter->fromGlobals();
        $this->httpFactory = $requestAdapter->getHttpFactory();
    }

    /**
     * Access raw PSR-7 request
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->serverRequest;
    }

    public function getHttpFactory(): mixed
    {
        return $this->httpFactory;
    }

    public function getRequestFactory(): RequestFactoryInterface
    {
        /** @var RequestFactoryInterface $requestFactory */
        $requestFactory = $this->httpFactory;

        return $requestFactory;
    }

    public function getResponseFactory(): ResponseFactoryInterface
    {
        /** @var ResponseFactoryInterface $responseFactory */
        $responseFactory = $this->httpFactory;

        return $responseFactory;
    }

    public function getStreamFactory(): StreamFactoryInterface
    {
        /** @var StreamFactoryInterface $streamFactory */
        $streamFactory = $this->httpFactory;

        return $streamFactory;
    }

    /**
     * Convenience: get parsed JSON or form body as array
     *
     * @return array<string|int, mixed>
     */
    public function getParsedBody(): array
    {
        $body = $this->serverRequest->getParsedBody();
        return is_array($body) ? $body : [];
    }

    /**
     * Convenience: retrieve a query param with default
     */
    public function getQueryParam(string $name, mixed $default = null): mixed
    {
        $params = $this->serverRequest->getQueryParams();
        return $params[$name] ?? $default;
    }

    /**
     * Detect client type with multiple fallback methods.
     * Priority: X-Client-Type Header -> Hostname -> User-Agent Pattern -> Default 'web'
     */
    public function getClientType(): string
    {
        // Priority 1: X-Client-Type Header
        $headerClientType = $this->serverRequest->getHeader('x-client-type');
        if (count($headerClientType) > 0) {
            return strtolower($headerClientType[0]);
        }

        // Priority 2: Hostname pattern
        $host = $this->serverRequest->getHeader('host') ? $this->serverRequest->getHeader('host')[0] : '';
        if (str_contains($host, 'mobile-api.')) {
            return 'mobile';
        }
        if (str_contains($host, 'test-api.')) {
            return 'postman';
        }

        // Priority 3: User-Agent pattern
        $userAgent = $this->serverRequest->getHeader('user-agent') ? $this->serverRequest->getHeader(
            'user-agent'
        )[0] : '';
        if (str_contains($userAgent, 'PostmanRuntime')) {
            return 'postman';
        }

        if (
            str_contains($userAgent, 'BitSynamaMobileApp') ||
            str_contains($userAgent, 'okhttp') ||
            str_contains($userAgent, 'Dart') ||
            str_contains($userAgent, 'Capacitor')
        ) {
            return 'mobile';
        }

        // Fallback
        return 'web';
    }

    public function getUserAgent(): string
    {
        return $this->serverRequest->getHeader('user-agent') ? $this->serverRequest->getHeader('user-agent')[0] : '';
    }

    public function getDeviceInfo(): string
    {
        return $this->serverRequest->getHeader('user-agent') ? $this->serverRequest->getHeader('user-agent')[0] : '';
    }

    public function getIpAddress(): string
    {
        $server = $this->serverRequest->getServerParams();
        $ip = trim($server['REMOTE_ADDR'] ?? '', '[]');

        return self::isValid($ip) ? $ip : '';

        // // $forwardedIp = CoreKit::request()->proxy_ip;
        // $forwardedIp = '';
        // $headers = [
        //     'Forwarded',
        //     'Forwarded-For',
        //     'X-Forwarded',
        //     'X-Forwarded-For',
        //     'X-Cluster-Client-Ip',
        //     'Client-Ip',
        // ];
        // foreach ($headers as $header) {
        //     // code...
        // }

        // if (empty($forwardedIp)) {
        //     return $ip;
        // }

        // $ipAddresses = explode(',', str_replace(' ', '', $forwardedIp));
        // $ipAddresses = array_filter($ipAddresses, function ($ipAddress) use ($ip) {
        //     if (
        //         $ipAddress !== '127.0.0.1'
        //         && substr($ipAddress, 0, 7) !== substr($ip, 0, 7)
        //     ) {
        //         return true;
        //     }
        // });

        // $ipAddress = $ip;
        // if (! empty($ipAddresses) && ! empty($ipAddresses[0])) {
        //     $ipAddress = $ipAddresses[0];
        // }

        // return $ipAddress;
    }

    public function getHeader(string $param): string
    {
        return $this->serverRequest->getHeader($param) ? $this->serverRequest->getHeader($param)[0] : '';
    }

    public function getReferer(): string
    {
        return $this->serverRequest->getHeader('referer') ? $this->serverRequest->getHeader('referer')[0] : '';
    }

    public function getAllowedReferer(): string
    {
        $serverUrl = $this->serverRequest->getUri()
            ->getScheme() . '://' . $this->serverRequest->getUri()->getHost();
        $referer = $this->getReferer();

        if (str_starts_with($referer, $serverUrl)) {
            return $referer;
        }

        return '';
    }

    public function getServerUrl(): string
    {
        $serverUrl = $this->serverRequest->getUri()
            ->getScheme() . '://' . $this->serverRequest->getUri()->getHost();

        if ($this->serverRequest->getUri()->getPort() !== 80) {
            $serverUrl .= ':' . ((string) $this->serverRequest->getUri()->getPort());
        }

        return $serverUrl;
    }

    public function getCurrentUrl(): string
    {
        return $this->serverRequest->getUri()
            ->__toString();
    }

    public function jsonOutputRequested(): bool
    {
        $accept = $this->serverRequest->getHeaderLine('Accept');
        return str_contains($accept, 'application/json');

        //           || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')

    }

    public function getCurrentAppBaseUrl(): string
    {
        /** @var string $url */
        $url = Lapis::configRegistry()->get('app.frontend_url');

        /** @var string $adminPrefix */
        $adminPrefix = Lapis::configRegistry()->get('app.routes.admin_prefix');

        if ($this->isAdminSite()) {
            if (str_starts_with($adminPrefix, '/')) {
                $url .= $adminPrefix;
            } else {
                $url = $adminPrefix;
            }
        }

        return $url;
    }

    public function isAdminSite(): bool
    {
        /** @var string $adminPrefix */
        $adminPrefix = Lapis::configRegistry()->get('app.routes.admin_prefix');
        $currentUrl = $this->getCurrentUrl();

        return str_contains($currentUrl, $adminPrefix);
    }

    public function getServerParam(string $param): string
    {
        $serverParams = $this->serverRequest->getServerParams();

        return $serverParams[strtoupper($param)] ?? '';
    }

    /**
     * Check that a given string is a valid IP address.
     */
    private function isValid(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Scans framework and child-app adapter directories for classes annotated with AdapterInfo,
     * and instantiates the one matching $adapterKey.
     */
    private function discoverAndInstantiate(): RequestAdapterInterface
    {
        /** @var string $repoDir */
        $repoDir = Lapis::varRegistry()->get('repo_dir');

        /** @var string $projectDir */
        $projectDir = Lapis::varRegistry()->get('project_dir');

        /** @var string $adapterKey */
        $adapterKey = Lapis::configRegistry()->get('utility.request') ?? 'nyholm';

        $className = Atlas::discover(
            dirPath: 'Utilities.Adapters.Request',
            interface: RequestAdapterInterface::class,
            attribute: AdapterInfo::class,
            classSuffix: 'RequestAdapter',
            type: 'request',
            key: $adapterKey,
            repoDir: $repoDir,
            projectDir: $projectDir
        );

        if (empty($className)) {
            throw new RuntimeException("Request adapter '{$adapterKey}' not found.");
        }

        /** @var RequestAdapterInterface $instance */
        $instance = new $className();

        return $instance;
    }
}
