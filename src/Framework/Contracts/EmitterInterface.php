<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Contracts;

use Psr\Http\Message\ResponseInterface;

interface EmitterInterface
{
    public function emit(ResponseInterface $response): void;
}
