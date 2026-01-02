<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Middlewares;

use BitSynama\Lapis\Framework\Attributes\SecurityCompliance;
use BitSynama\Lapis\Framework\Foundation\Constants;
use BitSynama\Lapis\Lapis;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use function count;
use function in_array;
use function is_array;
use function strtoupper;

#[SecurityCompliance('OWASP-A5', 'Validates CSRF token against stored session token')]
class CsrfProtectionMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = strtoupper($request->getMethod());
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $handler->handle($request);
        }

        $clientType = Lapis::requestUtility()->getClientType();
        if (! in_array($clientType, ['web', 'postman'], true)) {
            return $handler->handle($request);
        }

        // pull the field from form data
        $parsed = $request->getParsedBody();
        if (! empty($parsed)) {
            /** @var string $providedToken */
            $providedToken = is_array($parsed) ? ($parsed['csrf_token'] ?? '') : '';
        }

        if (empty($providedToken)) {
            /** @var array<int, string> $csrfTokenArray */
            $csrfTokenArray = $request->getHeader('x-csrf-token');
            if (count($csrfTokenArray) > 0) {
                /** @var string $providedToken */
                $providedToken = $csrfTokenArray[0];
            }
        }

        if (! empty($providedToken) && ! Lapis::sessionUtility()->isCsrfTokenValid($providedToken)) {
            // token invalid or missing
            throw new RuntimeException('Invalid CSRF token', Constants::STATUS_CODE_BAD_REQUEST);
        }

        return $handler->handle($request);
    }
}
