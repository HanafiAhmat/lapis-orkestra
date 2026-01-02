<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Contracts;

use Psr\Http\Message\ServerRequestInterface;

interface RequestAdapterInterface
{
    public function fromGlobals(): ServerRequestInterface;

    public function getHttpFactory(): mixed;
}
