<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Middlewares;

use BitSynama\Lapis\Framework\Exceptions\BusinessRuleException;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\User\Entities\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JwtAuthenticationMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $jwtTokenInteractor = Lapis::interactorRegistry()->get('core.security.jwt_token');
        $token = $jwtTokenInteractor::getAccessToken($request);
        if ($token) {
            $payload = $jwtTokenInteractor::verifyAccessToken($token);
            if (! empty($payload)) {
                $request = $request->withAttribute('jwt_payload', $payload);
                Lapis::varRegistry()->set('jwt_payload', $payload);
                if (Lapis::userTypeRegistry()->has($payload->type)) {
                    /** @var User|null $user */
                    $user = Lapis::userTypeRegistry()->getUserById(alias: $payload->type, id: $payload->sub);
                    if ($user !== null) {
                        $request = $request->withAttribute('user', $user);
                        Lapis::varRegistry()->set('user', $user);
                    }
                    // throw new ValidationException([], 'Invalid credentials', Constants::STATUS_CODE_UNAUTHORIZED);

                }
                // throw new BusinessRuleException("Unsupported user type");

            }
            // throw new BusinessRuleException("Corrupted Access Token");

        }

        return $handler->handle($request);
    }
}
