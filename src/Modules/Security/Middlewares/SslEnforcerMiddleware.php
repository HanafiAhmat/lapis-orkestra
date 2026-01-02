<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Middlewares;

use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Pipeline\Utilities\Constants;
use BitSynama\Lapis\Pipeline\Utilities\MultiResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SslEnforcerMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // // Allow insecure on development or Postman
        // if (Lapis::configRegistry()->get('app.env') !== 'production') {
        //     return true;
        // }

        // $request = Lapis::requestUtility();
        // $isSecure = !empty($request->getVar('HTTPS'))
        //             && $request->getVar('HTTPS') !== 'off'
        //             || $request->getVar('SERVER_PORT') === '443'
        //             || (
        //                 null !== $request->getVar('HTTP_X_FORWARDED_PROTO')
        //                 && $request->getVar('HTTP_X_FORWARDED_PROTO') === 'https'
        //             );

        // if (! $isSecure) {
        //     MultiResponse::fail(
        //         'HTTPS is required to access this resource.',
        //         [ 'url' => $request->url ],
        //         Constants::STATUS_CODE_UPGRADE_REQUIRED
        //     );
        //     return false;
        // }

        return $handler->handle($request);
    }

    // /**
    //  * Verify incoming request SSL Certificate.
    //  */
    // private function verifySSLDomain(string $audience): bool
    // {
    //     $configRegistry = Lapis::configRegistry();
    //     if ($audience === 'postman' || $configRegistry->get('app.env') === Constants::ENV_DEVELOPMENT) {
    //         return true; // Skip SSL checks in dev/postman
    //     }

    //     $requestUtility = Lapis::requestUtility();
    //     $subjectDn = $requestUtility->getServerParam('SSL_CLIENT_S_DN');
    //     if (empty($subjectDn)) {
    //         return false;
    //     }

    //     $issuerDn = $requestUtility->getServerParam('SSL_CLIENT_I_DN');
    //     if (empty($issuerDn)) {
    //         return false;
    //     }

    //     $allowedDomains = $configRegistry->get('app.ssl.allowed_domains') ? explode(
    //         ',',
    //         (string) $configRegistry->get('app.ssl.allowed_domains')
    //     ) : [];
    //     $domainValid = false;
    //     foreach ($allowedDomains as $allowed) {
    //         if (stripos((string) $subjectDn, $allowed) !== false) {
    //             $domainValid = true;
    //         }
    //     }

    //     $allowedIssuers = [];
    //     if ($configRegistry->has('app.ssl.allowed_issuers') ? explode(
    //         ',',
    //         (string) $configRegistry->get('app.ssl.allowed_issuers')
    //     ) : [];
    //     $issuerValid = false;
    //     foreach ($allowedIssuers as $allowed) {
    //         if (stripos((string) $issuerDn, $allowed) !== false) {
    //             $issuerValid = true;
    //         }
    //     }

    //     return $domainValid && $issuerValid;
    // }
}
