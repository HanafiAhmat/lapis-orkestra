<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Kernel;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Framework\Exceptions\DbNotInitialisedException;
use BitSynama\Lapis\Framework\Middlewares\FinalHandlerMiddleware;
use BitSynama\Lapis\Framework\Middlewares\SessionMiddleware;
use BitSynama\Lapis\Framework\Responses\DbNotInitialisedResponse;
use BitSynama\Lapis\Framework\Responses\ErrorResponse;
use BitSynama\Lapis\Lapis;
use Closure;
use Middlewares\ClientIp as ClientIpMiddleware;
use Middlewares\JsonPayload as JsonPayloadMiddleware;
use Middlewares\UrlEncodePayload as UrlEncodePayloadMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Relay\Relay;
use Throwable;
use function array_map;
use function in_array;
use function is_array;
use function strtoupper;

#[ImplementsPSR(
    ResponseInterface::class,
    psr: 'PSR-7',
    usage: 'dispatch() function returns HTTP Response Interface',
    link: 'https://www.php-fig.org/psr/psr-7/#33-psrhttpmessageresponseinterface'
)]
final class Dispatcher
{
    public function dispatch(): ResponseInterface
    {
        $request = Lapis::requestUtility()->getRequest();
        $request = $this->methodOverride($request);

        // Match the route
        $routeMatch = Lapis::routerUtility()->match($request);

        // Build middleware queue
        $queue = [];

        // Framework's Required Middleware
        $queue[] = new SessionMiddleware();
        $queue[] = (new ClientIpMiddleware())->proxy();
        $queue[] = new JsonPayloadMiddleware();
        $queue[] = new UrlEncodePayloadMiddleware();

        if ($middleware = Lapis::middlewareRegistry()->getOrSkip('core.user.user_type_availability')) {
            $queue[] = $middleware instanceof Closure ? $middleware() : new $middleware();
        }

        if ($middleware = Lapis::middlewareRegistry()->getOrSkip('core.security.csrf_protection')) {
            $queue[] = $middleware instanceof Closure ? $middleware() : new $middleware();
        }

        if ($middleware = Lapis::middlewareRegistry()->getOrSkip('core.security.jwt_authentication')) {
            $queue[] = $middleware instanceof Closure ? $middleware() : new $middleware();
        }

        // fetch pre action middleware
        foreach ($routeMatch->middlewares as $mwDef) {
            if (Lapis::middlewareRegistry()->has($mwDef->id)) {
                $mwFactory = Lapis::middlewareRegistry()->get($mwDef->id);
                $queue[] = $mwFactory instanceof Closure ? $mwFactory(...$mwDef->vars) : new $mwFactory(
                    ...$mwDef->vars
                );
            }
        }

        $queue[] = new FinalHandlerMiddleware($routeMatch);

        // Relay through middleware
        $relay = new Relay($queue);
        try {
            $response = $relay->handle($request);

            // Relay through response filter
            $filters = array_map(function ($rfDef) {
                $filter = Lapis::responseFilterRegistry()->get($rfDef->id);
                return $filter instanceof Closure ? $filter(...$rfDef->vars) : new $filter(...$rfDef->vars);
            }, $routeMatch->filters);
            $response = (new ResponseFilterRelay($filters))->run($response, $request);
        } catch (DbNotInitialisedException $e) {
            $response = (new DbNotInitialisedResponse())($e);
        } catch (Throwable $e) {
            $response = (new ErrorResponse())($e);
        }

        return $response;
    }

    private function methodOverride(ServerRequestInterface $request): ServerRequestInterface
    {
        if (strtoupper($request->getMethod()) !== 'POST') {
            return $request;
        }

        $override = $request->getHeaderLine('X-HTTP-Method-Override');
        if (! $override) {
            $parsed = $request->getParsedBody();
            if (is_array($parsed) && ! empty($parsed['_method'])) {
                /** @var string $method */
                $method = $parsed['_method'];
                $override = $method;
            }
        }

        if ($override) {
            $override = strtoupper($override);
            // if (in_array($override, ['PUT','PATCH','DELETE'], true)) {
            if (in_array($override, ['PUT', 'PATCH'], true)) {
                // change the method but keep parsed body & uploaded files as-is
                $request = $request->withMethod($override);
            }
        }

        return $request;
    }
}
