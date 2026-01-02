<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Middlewares;

use Aura\Session\Session;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Utilities\SessionUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Starts an Aura session, attaches the default segment to the request,
 * then commits the session after the controller has run.
 */
final class SessionMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var SessionUtility $sessionUtility */
        $sessionUtility = Lapis::sessionUtility();

        // pick the right name & cookie options
        $sessionUtility->initialize($request);

        // start the session
        $sessionUtility->start();

        // Let the rest of the pipeline (including controller) run
        $response = $handler->handle($request);

        // Commit the session (write cookies, save data)
        $sessionUtility->commit();

        return $response;
    }
}
