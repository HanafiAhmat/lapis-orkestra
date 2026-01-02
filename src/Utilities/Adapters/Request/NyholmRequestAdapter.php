<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Adapters\Request;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Utilities\AdapterInfo;
use BitSynama\Lapis\Utilities\Contracts\RequestAdapterInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Nyholm\Psr7Server\ServerRequestCreatorInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

#[ImplementsPSR(
    RequestFactoryInterface::class,
    psr: 'PSR-17',
    usage: 'Retrieves Request Factory Interface through NyHolm ServerRequestCreatorInterface',
    link: 'https://www.php-fig.org/psr/psr-17/#21-requestfactoryinterface'
)]
#[ImplementsPSR(
    ResponseFactoryInterface::class,
    psr: 'PSR-17',
    usage: 'Retrieves Response Factory Interface through NyHolm ServerRequestCreatorInterface',
    link: 'https://www.php-fig.org/psr/psr-17/#22-responsefactoryinterface'
)]
#[ImplementsPSR(
    StreamFactoryInterface::class,
    psr: 'PSR-17',
    usage: 'Retrieves Stream Factory Interface through NyHolm ServerRequestCreatorInterface',
    link: 'https://www.php-fig.org/psr/psr-17/#24-streamfactoryinterface'
)]
#[ImplementsPSR(
    UriFactoryInterface::class,
    psr: 'PSR-17',
    usage: 'Retrieves URI Factory Interface through NyHolm ServerRequestCreatorInterface',
    link: 'https://www.php-fig.org/psr/psr-17/#26-urifactoryinterface'
)]
#[ImplementsPSR(
    UploadedFileFactoryInterface::class,
    psr: 'PSR-17',
    usage: 'Retrieves Uploaded File Factory Interface through NyHolm ServerRequestCreatorInterface',
    link: 'https://www.php-fig.org/psr/psr-17/#25-uploadedfilefactoryinterface'
)]
#[ImplementsPSR(
    ServerRequestFactoryInterface::class,
    psr: 'PSR-17',
    usage: 'Retrieves Server Request Factory Interface through NyHolm ServerRequestCreatorInterface',
    link: 'https://www.php-fig.org/psr/psr-17/#23-serverrequestfactoryinterface'
)]
#[ImplementsPSR(
    ServerRequestInterface::class,
    psr: 'PSR-7',
    usage: 'fromGlobals() function returns HTTP Server Request Interface',
    link: 'https://www.php-fig.org/psr/psr-7/#321-psrhttpmessageserverrequestinterface'
)]
#[AdapterInfo(type: 'request', key: 'nyholm', description: 'NyHolm PSR-17 Server Request')]
final class NyholmRequestAdapter implements RequestAdapterInterface
{
    private readonly ServerRequestCreatorInterface $creator;

    private readonly mixed $httpFactory;

    public function __construct()
    {
        $this->httpFactory = new Psr17Factory();
        $this->creator = new ServerRequestCreator(
            $this->httpFactory,
            $this->httpFactory,
            $this->httpFactory,
            $this->httpFactory
        );
    }

    public function fromGlobals(): ServerRequestInterface
    {
        return $this->creator->fromGlobals();
    }

    public function getHttpFactory(): mixed
    {
        return $this->httpFactory;
    }
}
