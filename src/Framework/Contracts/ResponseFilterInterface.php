<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Contracts;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ResponseFilterInterface
{
    public function process(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface;
}
