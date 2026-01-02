<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Responses;

use BitSynama\Lapis\Framework\Foundation\Constants;
use BitSynama\Lapis\Lapis;
use Psr\Http\Message\ResponseInterface;
use function htmlspecialchars;
use const ENT_QUOTES;

class RedirectResponse
{
    public function __invoke(string $redirectUrl): ResponseInterface
    {
        $responseFactory = Lapis::requestUtility()->getResponseFactory();
        $streamFactory = Lapis::requestUtility()->getStreamFactory();
        $html = '<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0;url=' . htmlspecialchars(
            $redirectUrl,
            ENT_QUOTES
        ) .
            '"><title>Redirecting</title></head><body>Redirecting to <a href="' . htmlspecialchars(
                $redirectUrl,
                ENT_QUOTES
            ) . '">' .
            htmlspecialchars($redirectUrl) . '</a></body></html>';

        return $responseFactory->createResponse(Constants::STATUS_CODE_OK)
            ->withHeader('Content-Type', 'text/html; charset=UTF-8')
            ->withHeader('Location', $redirectUrl)
            ->withBody($streamFactory->createStream($html));
    }
}
