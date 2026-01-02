<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Kernel;

use BitSynama\Lapis\Framework\Contracts\ResponseFilterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ResponseFilterRelay
{
    private int $index = 0;

    /**
     * @param ResponseFilterInterface[] $filters
     */
    public function __construct(
        private array $filters
    ) {
    }

    public function run(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        if (! isset($this->filters[$this->index])) {
            return $response;
        }
        $filter = $this->filters[$this->index++];
        $newRes = $filter->process($response, $request);

        return $this->run($newRes, $request);
    }
}
