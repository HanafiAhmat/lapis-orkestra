<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Middlewares;

use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\Interactors\SecurityContextInteractor;
use BitSynama\Lapis\Pipeline\Utilities\Constants;
use BitSynama\Lapis\Pipeline\Utilities\RequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AudClaimMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // if (!SecurityContextInteractor::isSecurityModuleEnabled()) {
        //     return true;
        // }

        // $user = SecurityContextInteractor::getUser();
        // if (!$user || !isset($user->aud)) {
        //     MultiResponse::fail('Missing or invalid token audience (aud)', [], Constants::STATUS_CODE_UNAUTHORIZED);
        //     return false;
        // }

        // $clientType = RequestHelper::getClientType();
        // if ($user->aud !== $clientType) {
        //     MultiResponse::fail('Invalid token for client type', [
        //         'token_aud' => $user->aud,
        //         'client' => $clientType,
        //     ], Constants::STATUS_CODE_UNAUTHORIZED);
        //     return false;
        // }

        return $handler->handle($request);
    }

    /*
    public function oldProcess(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // $config = Lapis::configRegistry();
        // $allowedAudiences = $config->get('app.allowed_audiences') ? explode(
        //     ',',
        //     $config->get('app.allowed_audiences')
        // ) : [Constants::AUDIENCE_WEB];
        // $token = AuthService::getAccessToken();
        // if (empty($token)) {
        //     MultiResponse::fail('Unauthorized - No token provided', [], Constants::STATUS_CODE_UNAUTHORIZED);
        //     return false;
        // }
        // try {
        //     if (is_string($token)) {
        //         $jwtTokenService = InteractorRegistry::get('security', 'jwt_token');
        //         $decoded = $jwtTokenService::verifyAccessToken($token);
        //         if (property_exists($decoded, 'jti')) {
        //             if (AuthService::isTokenRevoked($decoded->jti)) {
        //                 MultiResponse::fail(
        //                     'Unauthorized - Access token revoked',
        //                     [],
        //                     Constants::STATUS_CODE_UNAUTHORIZED
        //                 );
        //                 return false;
        //             }
        //         } else {
        //             MultiResponse::fail('Unauthorized - Invalid token', [], Constants::STATUS_CODE_UNAUTHORIZED);
        //             return false;
        //         }
        //         if (property_exists($decoded, 'aud')) {
        //             if (! in_array($decoded->aud, $allowedAudiences, true)) {
        //                 MultiResponse::fail('Forbidden - Invalid token audience', [], Constants::STATUS_CODE_FORBIDDEN);
        //                 return false;
        //             }
        //             if (! AuthService::verifySSLDomain($decoded->aud)) {
        //                 MultiResponse::fail(
        //                     'Forbidden - Invalid SSL domain or issuer',
        //                     [],
        //                     Constants::STATUS_CODE_FORBIDDEN
        //                 );
        //                 return false;
        //             }
        //         } else {
        //             MultiResponse::fail('Unauthorized - Invalid token missing audience', [], Constants::STATUS_CODE_UNAUTHORIZED);
        //             return false;
        //         }
        //         Flight::app()->set('user', $decoded);
        //     } else {
        //         MultiResponse::fail('Unauthorized - Invalid token not a string', [], Constants::STATUS_CODE_UNAUTHORIZED);
        //         return false;
        //     }
        // } catch (Exception $e) {
        //     // MultiResponse::fail('Unauthorized - Invalid token exception', [], Constants::STATUS_CODE_UNAUTHORIZED);
        //     MultiResponse::fail('Unauthorized - ' . $e->getMessage(), [], Constants::STATUS_CODE_UNAUTHORIZED);
        //     return false;
        // }
        return true;
    }
    */
}
