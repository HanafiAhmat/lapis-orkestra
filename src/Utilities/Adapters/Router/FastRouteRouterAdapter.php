<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Adapters\Router;

use BitSynama\Lapis\Framework\DTO\ActionResponse;
use BitSynama\Lapis\Framework\DTO\RouteDefinition;
use BitSynama\Lapis\Framework\DTO\RouteMatch;
use BitSynama\Lapis\Framework\Handlers\RequestHandler;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Utilities\AdapterInfo;
use BitSynama\Lapis\Utilities\Contracts\RouterAdapterInterface;
use FastRoute\Dispatcher as FastRouteDispatcher;
use FastRoute\RouteCollector;
use Psr\Http\Message\ServerRequestInterface;
use function FastRoute\simpleDispatcher;

#[AdapterInfo(type: 'router', key: 'fastroute', description: 'FastRoute Router')]
final class FastRouteRouterAdapter implements RouterAdapterInterface
{
    public function match(ServerRequestInterface $request): RouteMatch
    {
        $dispatcher = simpleDispatcher(function (RouteCollector $r) {
            $routes = Lapis::routeRegistry()->all();
            /** @var RouteDefinition $routeDef */
            foreach ($routes->getIterator() as $routeKey => $routeDef) {
                $r->addRoute($routeDef->method, $routeDef->path, $routeKey);
            }
        });

        $uri = $request->getUri()
            ->getPath();
        $method = $request->getMethod();
        $dispatchedMatch = $dispatcher->dispatch($method, $uri);
        $isAdminSite = Lapis::requestUtility()->isAdminSite();
        $templatePrefix = $isAdminSite ? 'admin.' : 'public.';

        switch ($dispatchedMatch[0]) {
            case FastRouteDispatcher::FOUND:
                /** @var RouteDefinition $routeDef */
                $routeDef = Lapis::routeRegistry()->get($dispatchedMatch[1]);

                return new RouteMatch(
                    RouteMatch::FOUND,
                    handler: $routeDef->handler,
                    vars: $dispatchedMatch[2],
                    middlewares: $routeDef->middlewares,
                    filters: $routeDef->filters
                );

            case FastRouteDispatcher::METHOD_NOT_ALLOWED:
                $handler405 = new RequestHandler(fn (ServerRequestInterface $req): ActionResponse =>
                    new ActionResponse(
                        status: ActionResponse::ERROR,
                        message: RouteMatch::METHOD_NOT_ALLOWED,
                        statusCode: 405,
                        template: $templatePrefix . 'errors.405'
                    ));

                return new RouteMatch(
                    RouteMatch::METHOD_NOT_ALLOWED,
                    handler: $handler405,
                    vars: [],
                    allowedMethods: $dispatchedMatch[1]
                );

            case FastRouteDispatcher::NOT_FOUND:
            default:
                $handler404 = new RequestHandler(fn (ServerRequestInterface $req): ActionResponse =>
                    new ActionResponse(
                        status: ActionResponse::ERROR,
                        message: RouteMatch::NOT_FOUND,
                        statusCode: 404,
                        template: $templatePrefix . 'errors.404'
                    ));

                return new RouteMatch(RouteMatch::NOT_FOUND, handler: $handler404);
        }
    }
}
