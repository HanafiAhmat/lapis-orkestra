<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Middlewares;

use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\Interactors\NonceTokenInteractor;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SecurityHeadersMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // $response = Lapis::response();

        // $response->header('X-Frame-Options', 'SAMEORIGIN');
        // $response->header('X-Content-Type-Options', 'nosniff');
        // $response->header('X-XSS-Protection', '1; mode=block');
        // $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        // $response->header('Permissions-Policy', 'geolocation=(), camera=()');

        // $config = Lapis::configRegistry();

        // $cdnUrl = $config->get('app.cdn_url');

        // // For tracy debugger
        // $nonce = NonceTokenInteractor::generateToken();
        // $unsafeInline = $config->get('app.env') !== 'production' ? "'unsafe-inline'" : '';
        // // End for tracy debugger

        // $response->header('Content-Security-Policy',
        //     "default-src 'self'; " .
        //     "style-src 'self' {$unsafeInline} {$cdnUrl} https://fonts.googleapis.com; " .
        //     "img-src 'self' {$cdnUrl} data:; " .
        //     "font-src 'self' {$cdnUrl} https://fonts.gstatic.com; " .
        //     "script-src 'self' 'nonce-{$nonce}' 'strict-dynamic';"
        // );

        return $handler->handle($request);
    }

    // public function error()
    // {
    //     $response = Lapis::response();

    //     // Remove sensitive headers to prevent leakage
    //     $headers = ['Authorization', 'X-Csrf-Token', 'Set-Cookie', 'X-Test'];
    //     foreach ($headers as $header) {
    //         if ($response->getHeader($header)) {
    //             $response->header($header, null);
    //         }
    //     }
    // }
}
