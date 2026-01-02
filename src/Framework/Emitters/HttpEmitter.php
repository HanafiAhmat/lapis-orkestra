<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Emitters;

use BitSynama\Lapis\Framework\Contracts\EmitterInterface;
use BitSynama\Lapis\Framework\Foundation\Constants;
use Psr\Http\Message\ResponseInterface;
use function header;
use function http_response_code;

class HttpEmitter implements EmitterInterface
{
    public function emit(ResponseInterface $response): void
    {
        // status line
        http_response_code($response->getStatusCode());

        // headers
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("{$name}: {$value}", false);
            }
        }

        // body
        if ($response->getStatusCode() !== Constants::STATUS_CODE_DELETED) {
            echo $response->getBody();
        }
    }
}
