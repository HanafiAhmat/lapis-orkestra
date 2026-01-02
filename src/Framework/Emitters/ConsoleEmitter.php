<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Emitters;

use BitSynama\Lapis\Framework\Contracts\EmitterInterface;
use Psr\Http\Message\ResponseInterface;
use function fwrite;
use function trim;
use const PHP_EOL;
use const STDOUT;

class ConsoleEmitter implements EmitterInterface
{
    public function emit(ResponseInterface $response): void
    {
        $body = (string) $response->getBody();

        // Plain text with minimal formatting
        $status = $response->getStatusCode();
        $reason = $response->getReasonPhrase();
        fwrite(STDOUT, "[{$status} {$reason}]" . PHP_EOL);
        fwrite(STDOUT, trim($body) . PHP_EOL);
    }
}
