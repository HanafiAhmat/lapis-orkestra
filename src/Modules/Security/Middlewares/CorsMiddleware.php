<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Middlewares;

use BitSynama\Lapis\Lapis;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function explode;
use function in_array;
use function is_string;

class CorsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // $request = Lapis::requestUtility();
        // $response = Lapis::response();

        // if ($request->getVar('HTTP_ORIGIN') !== '') {
        //     $this->allowOrigins();
        //     $response->header('Access-Control-Allow-Credentials', 'true');
        //     $response->header('Access-Control-Max-Age', '86400');
        // }

        // if ($request->method === 'OPTIONS') {
        //     if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_METHOD')) {
        //         $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD');
        //     }

        //     if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS')) {
        //         $response->header('Access-Control-Allow-Headers', $request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS'));
        //     }

        //     $response->status(200);
        //     $response->send();
        //     return false;
        // }

        return $handler->handle($request);
    }

    // private function allowOrigins(): void
    // {
    //     $configAllowedOrigins = Lapis::configRegistry()->get('app.cors.allowed_origins');
    //     $allowed = is_string($configAllowedOrigins)
    //         ? explode(',', $configAllowedOrigins)
    //         : [
    //             'capacitor://localhost',
    //             'ionic://localhost',
    //             'http://localhost',
    //             'http://localhost:4200',
    //             'http://localhost:8080',
    //             'http://localhost:8100',
    //         ];

    //     $origin = Lapis::requestUtility()->getServerParam('HTTP_ORIGIN');
    //     if (in_array($origin, $allowed, true)) {
    //         Lapis::requestUtility()->setHeader('Access-Control-Allow-Origin', $origin); // @todo this should be on response filter
    //     }
    // }
}
