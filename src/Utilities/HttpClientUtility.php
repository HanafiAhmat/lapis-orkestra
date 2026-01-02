<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities;

use BitSynama\Lapis\Framework\Foundation\Atlas;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Utilities\Contracts\HttpClientAdapterInterface;
use GuzzleHttp\Psr7\MultipartStream;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use function array_map;
use function explode;
use function http_build_query;
use function implode;
use function is_array;
use function is_string;
use function json_encode;
use function ltrim;
use function strtolower;
use const JSON_THROW_ON_ERROR;

/**
 * Auto-selects and instantiates a PSR-6 cache adapter based on configuration.
 */
class HttpClientUtility
{
    private readonly HttpClientAdapterInterface $adapter;

    private readonly RequestFactoryInterface $requestFactory;

    private readonly StreamFactoryInterface $streamFactory;

    /**
     * Discover available providers via ProviderKey attribute, then instantiate.
     */
    public function __construct()
    {
        /** @var RequestFactoryInterface $requestFactory */
        $requestFactory = Lapis::requestUtility()->getRequestFactory();
        $this->requestFactory = $requestFactory;

        /** @var StreamFactoryInterface $streamFactory */
        $streamFactory = Lapis::requestUtility()->getStreamFactory();
        $this->streamFactory = $streamFactory;

        $this->adapter = $this->discoverAndInstantiate();
    }

    public function getAdapter(): HttpClientAdapterInterface
    {
        return $this->adapter;
    }

    /**
     * Send a pre-built PSR-7 request through the adapter.
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->adapter->sendRequest($request);
    }

    /**
     * General-purpose request with raw options.
     * Options may include 'query', 'headers', 'body', 'multipart', 'boundary', 'json' and 'form_params'.
     *
     * @param array<string, mixed> $options
     */
    public function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        // 1) Build full URI with query string
        /** @var array<string, string|int|float> $queryParams */
        $queryParams = $options['query'] ?? [];
        $url = $this->buildUri($uri, $queryParams);
        $req = $this->requestFactory->createRequest($method, $url);

        // 2) Body coercion
        if (! empty($options['multipart']) && is_array($options['multipart'])) {
            // multipart/form-data
            $parts = $options['multipart'];
            /** @var string|null $boundary */
            $boundary = $options['boundary'] ?? null;
            $stream = new MultipartStream($parts, $boundary);
            $req = $req
                ->withBody($stream)
                ->withHeader('Content-Type', 'multipart/form-data; boundary=' . $stream->getBoundary());
        } elseif (isset($options['json'])) {
            // application/json
            $payload = json_encode($options['json'], JSON_THROW_ON_ERROR);
            $req = $req
                ->withBody($this->streamFactory->createStream($payload))
                ->withHeader('Content-Type', 'application/json');
        } elseif (! empty($options['form_params']) && is_array($options['form_params'])) {
            // application/x-www-form-urlencoded
            $payload = http_build_query($options['form_params']);
            $req = $req
                ->withBody($this->streamFactory->createStream($payload))
                ->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        } elseif (isset($options['body'])) {
            // raw body
            if ($options['body'] instanceof StreamInterface) {
                $bodyStream = $options['body'];
            } elseif (is_string($options['body'])) {
                $bodyStream = $this->streamFactory->createStream($options['body']);
            } else {
                $bodyStream = $this->streamFactory->createStream();
            }
            $req = $req->withBody($bodyStream);
        }

        // 3) Attach any additional headers (won’t override Content-Type)
        /** @var array<string, array<string>|string> $headers */
        $headers = $options['headers'] ?? [];
        foreach ($headers as $name => $value) {
            $req = $req->withHeader($name, $value);
        }

        // 4) Dispatch
        return $this->adapter->sendRequest($req);
    }

    /**
     * Convenience GET
     *
     * @param array<string, string|int|float> $queryParams
     * @param array<string, array<string>|string> $headers
     */
    public function get(string $uri, array $queryParams = [], array $headers = []): ResponseInterface
    {
        return $this->request('GET', $uri, [
            'query' => $queryParams,
            'headers' => $headers,
        ]);
    }

    /**
     * Convenience POST with data-format automatically handled
     *
     * @param array<string|int, mixed>|string $data
     * @param array<string, string|int|float> $queryParams
     * @param array<string, array<string>|string> $headers
     */
    public function post(
        string $uri,
        array|string $data = [],
        string $format = 'json',
        array $queryParams = [],
        array $headers = []
    ): ResponseInterface {
        return $this->prepareAndSend('POST', $uri, $data, $format, $queryParams, $headers);
    }

    /**
     * Convenience PUT
     *
     * @param array<string|int, mixed>|string $data
     * @param array<string, string|int|float> $queryParams
     * @param array<string, array<string>|string> $headers
     */
    public function put(
        string $uri,
        array|string $data = [],
        string $format = 'json',
        array $queryParams = [],
        array $headers = []
    ): ResponseInterface {
        return $this->prepareAndSend('PUT', $uri, $data, $format, $queryParams, $headers);
    }

    /**
     * Convenience PATCH
     *
     * @param array<string|int, mixed>|string $data
     * @param array<string, string|int|float> $queryParams
     * @param array<string, array<string>|string> $headers
     */
    public function patch(
        string $uri,
        array|string $data = [],
        string $format = 'json',
        array $queryParams = [],
        array $headers = []
    ): ResponseInterface {
        return $this->prepareAndSend('PATCH', $uri, $data, $format, $queryParams, $headers);
    }

    /**
     * Convenience DELETE
     *
     * @param array<string, string|int|float> $queryParams
     * @param array<string, array<string>|string> $headers
     */
    public function delete(string $uri, array $queryParams = [], array $headers = []): ResponseInterface
    {
        return $this->request('DELETE', $uri, [
            'query' => $queryParams,
            'headers' => $headers,
        ]);
    }

    /**
     * Convenience PURGE
     *
     * @param array<string, string|int|float> $queryParams
     * @param array<string, array<string>|string> $headers
     */
    public function purge(string $uri, array $queryParams = [], array $headers = []): ResponseInterface
    {
        return $this->request('PURGE', $uri, [
            'query' => $queryParams,
            'headers' => $headers,
        ]);
    }

    /**
     * Scans framework and child-app adapter directories for classes annotated with AdapterInfo,
     * and instantiates the one matching $adapterKey.
     */
    private function discoverAndInstantiate(): HttpClientAdapterInterface
    {
        /** @var string $repoDir */
        $repoDir = Lapis::varRegistry()->get('repo_dir');

        /** @var string $projectDir */
        $projectDir = Lapis::varRegistry()->get('project_dir');

        /** @var string $adapterKey */
        $adapterKey = Lapis::configRegistry()->get('utility.http_client') ?? 'guzzle';

        $className = Atlas::discover(
            dirPath: 'Utilities.Adapters.HttpClient',
            interface: HttpClientAdapterInterface::class,
            attribute: AdapterInfo::class,
            classSuffix: 'HttpClientAdapter',
            type: 'http_client',
            key: $adapterKey,
            repoDir: $repoDir,
            projectDir: $projectDir
        );

        if (empty($className)) {
            throw new RuntimeException("Http Client adapter '{$adapterKey}' not found.");
        }

        /** @var HttpClientAdapterInterface $instance */
        $instance = new $className();

        return $instance;
    }

    /**
     * Internal: prepare data/formats then dispatch
     *
     * @param array<string|int, mixed>|string $data
     * @param array<string, string|int|float> $queryParams
     * @param array<string, array<string>|string> $headers
     */
    private function prepareAndSend(
        string $method,
        string $uri,
        array|string $data,
        string $format,
        array $queryParams,
        array $headers
    ): ResponseInterface {
        $url = $this->buildUri($uri, $queryParams);
        $req = $this->requestFactory->createRequest($method, $url);

        // Decide on body + Content-Type header
        if (is_string($data)) {
            $stream = $this->streamFactory->createStream($data);
            if (strtolower($format) === 'xml') {
                $headers['Content-Type'] = 'application/xml';
            } else {
                $headers['Content-Type'] = 'text/plain';
            }
        } else {
            switch (strtolower($format)) {
                case 'form':
                    $stream = $this->streamFactory->createStream((string) http_build_query($data));
                    $headers['Content-Type'] = 'application/x-www-form-urlencoded';
                    break;
                case 'multipart':
                    $stream = new MultipartStream($data, null);
                    $headers['Content-Type'] = 'multipart/form-data; boundary=' . $stream->getBoundary();
                    break;
                case 'json':
                default:
                    $stream = $this->streamFactory->createStream((string) json_encode($data, JSON_THROW_ON_ERROR));
                    $headers['Content-Type'] = 'application/json';
                    break;
            }
        }

        $req = $req->withBody($stream);

        // Attach headers
        foreach ($headers as $name => $value) {
            $req = $req->withHeader($name, $value);
        }

        return $this->adapter->sendRequest($req);
    }

    /**
     * Build URI + query string
     *
     * @param array<string, string|int|float> $queryParams
     */
    private function buildUri(string $uri, array $queryParams): string
    {
        if (empty($queryParams)) {
            return $uri;
        }
        $uriParts = array_map(fn ($p) => ltrim($p, '?'), explode('&', $uri));

        return $uri . '?' . implode('&', $uriParts);
    }
}
