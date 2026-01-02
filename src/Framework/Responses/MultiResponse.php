<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Responses;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Framework\DTO\ActionResponse;
use BitSynama\Lapis\Framework\DTO\Configs\Utilities\ViewConfig;
use BitSynama\Lapis\Framework\Foundation\ConsoleNotice;
use BitSynama\Lapis\Framework\Foundation\Runtime;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Utilities\Contracts\ViewAdapterInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;
use function implode;
use function is_array;
use function json_encode;
use function strtoupper;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;
use const PHP_EOL;

#[ImplementsPSR(
    ResponseFactoryInterface::class,
    psr: 'PSR-17',
    usage: 'Receives HTTP Response Factory Interface',
    link: 'https://www.php-fig.org/psr/psr-17/#32-psrhttpmessageresponsefactoryinterface'
)]
#[ImplementsPSR(
    StreamFactoryInterface::class,
    psr: 'PSR-17',
    usage: 'Receives HTTP Stream Factory Interface',
    link: 'https://www.php-fig.org/psr/psr-17/#33-psrhttpmessagestreamfactoryinterface'
)]
#[ImplementsPSR(
    ResponseInterface::class,
    psr: 'PSR-7',
    usage: 'Returns HTTP Response Interface',
    link: 'https://www.php-fig.org/psr/psr-7/#33-psrhttpmessageresponseinterface'
)]
final class MultiResponse
{
    public function handle(ActionResponse $dto): ResponseInterface
    {
        $responseFactory = Lapis::requestUtility()->getResponseFactory();
        $streamFactory = Lapis::requestUtility()->getStreamFactory();

        if (Runtime::isCli()) {
            // Always pretty text for CLI
            $body = ConsoleNotice::format($dto);

            return $responseFactory->createResponse($dto->statusCode)
                ->withHeader('Content-Type', 'text/plain; charset=UTF-8')
                ->withBody($streamFactory->createStream($body));
        }

        /** @var ViewConfig $viewConfig */
        $viewConfig = Lapis::configRegistry()->get('utility.view');

        $jsonOutputRequested = Lapis::requestUtility()->jsonOutputRequested();

        if ($jsonOutputRequested && $viewConfig->outputs_enabled->json) {
            $payload = json_encode([
                'status' => $dto->status,
                'data' => $dto->data,
                'message' => $dto->message,
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

            $response = $responseFactory->createResponse($dto->statusCode)
                ->withHeader('Content-Type', 'application/json')
                ->withBody($streamFactory->createStream($payload));

            return $response;
        }

        if (! $viewConfig->outputs_enabled->html) {
            throw new RuntimeException('At least JSON or HTML output must be enabled');
        }

        if (! empty($dto->htmlRedirect)) {
            return (new RedirectResponse())($dto->htmlRedirect);
        }

        $dto = $this->applyDefaults($dto);

        /** @var ViewAdapterInterface $viewAdapter */
        $viewAdapter = Lapis::viewUtility()->getAdapter();
        $html = $viewAdapter->render($dto->template ?: '', $dto->data);

        return $responseFactory->createResponse($dto->statusCode)
            ->withHeader('Content-Type', 'text/html; charset=UTF-8')
            ->withBody($streamFactory->createStream($html));
    }

    /*
    private function toConsoleText(ActionResponse $dto): string
    {
        $lines = [];
        $lines[] = ($dto->statusCode ?? 500) . ' ' . strtoupper($dto->status);
        if (! empty($dto->message)) {
            $lines[] = $dto->message;
        }
        foreach ($dto->data as $k => $v) {
            if (is_array($v)) {
                $lines[] = $k . ':';
                foreach ($v as $vv) {
                    $lines[] = '  - ' . (string) $vv;
                }
            } else {
                $lines[] = $k . ': ' . (string) $v;
            }
        }
        return implode(PHP_EOL, $lines) . PHP_EOL;
    }
     */

    private function applyDefaults(ActionResponse $dto): ActionResponse
    {
        // Decide admin/public without the DTO knowing
        $isAdmin = Lapis::requestUtility()->isAdminSite();
        $prefix = $isAdmin ? 'admin.' : 'public.';

        $template = $dto->template ?? $prefix . 'default';

        if ($template === $dto->template) {
            return $dto;
        }

        return $dto->withTemplate($template);
    }
}
