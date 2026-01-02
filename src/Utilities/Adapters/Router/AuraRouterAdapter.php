<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Adapters\Router;

use Aura\Router\RouterContainer;
use BitSynama\Lapis\Framework\DTO\ActionResponse;
use BitSynama\Lapis\Framework\DTO\RouteDefinition;
use BitSynama\Lapis\Framework\DTO\RouteMatch;
use BitSynama\Lapis\Framework\Handlers\RequestHandler;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Utilities\AdapterInfo;
use BitSynama\Lapis\Utilities\Contracts\RouterAdapterInterface;
use Psr\Http\Message\ServerRequestInterface;
use function strtoupper;

#[AdapterInfo(type: 'router', key: 'aura', description: 'Aura.Router Adapter')]
final class AuraRouterAdapter implements RouterAdapterInterface
{
    private readonly RouterContainer $routerContainer;

    public function __construct()
    {
        $this->routerContainer = new RouterContainer();

        $map = $this->routerContainer->getMap();

        // Register every Lapis route into Aura.Router’s map
        /** @var RouteDefinition $routeDef */
        foreach (Lapis::routeRegistry()->all() as $routeKey => $routeDef) {
            // Use the generic route() + allows() so ANY HTTP verb can be supported
            $route = $map
                ->route($routeKey, $routeDef->path, $routeKey)
                ->allows([strtoupper($routeDef->method)]);
        }
    }

    public function match(ServerRequestInterface $request): RouteMatch
    {
        $matcher = $this->routerContainer->getMatcher();
        $route = $matcher->match($request);
        $isAdminSite = Lapis::requestUtility()->isAdminSite();
        $templatePrefix = $isAdminSite ? 'admin.' : 'public.';

        if (! $route) {
            // No route matched → 404
            $handler404 = new RequestHandler(
                fn ($req) => new ActionResponse(
                    status: ActionResponse::ERROR,
                    message: '404 Not Found',
                    statusCode: 404,
                    template: $templatePrefix . 'errors.404'
                )
            );
            return new RouteMatch(RouteMatch::NOT_FOUND, handler: $handler404);
        }

        // Found a matching routeKey
        $routeKey = $route->name;
        /** @var RouteDefinition $routeDef */
        $routeDef = Lapis::routeRegistry()->get($routeKey);

        return new RouteMatch(
            RouteMatch::FOUND,
            handler: $routeDef->handler,
            vars: $route->attributes,
            middlewares: $routeDef->middlewares,
            filters: $routeDef->filters
        );
    }
}
