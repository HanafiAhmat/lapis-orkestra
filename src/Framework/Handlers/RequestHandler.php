<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Handlers;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Framework\Exceptions\TableNotFoundException;
use BitSynama\Lapis\Framework\Persistences\AbstractEntity;
use BitSynama\Lapis\Lapis;
use Closure;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;
use RuntimeException;
use function array_key_exists;
use function class_exists;
use function count;
use function is_array;
use function is_callable;
use function is_string;
use function is_subclass_of;
use function sprintf;

#[ImplementsPSR(
    RequestHandlerInterface::class,
    psr: 'PSR-15',
    usage: 'Implements handle function',
    link: 'https://www.php-fig.org/psr/psr-15/#21-psrhttpserverrequesthandlerinterface'
)]
#[ImplementsPSR(
    ServerRequestInterface::class,
    psr: 'PSR-7',
    usage: 'handle() function accepts HTTP Server Request Interface',
    link: 'https://www.php-fig.org/psr/psr-7/#321-psrhttpmessageserverrequestinterface'
)]
#[ImplementsPSR(
    ResponseInterface::class,
    psr: 'PSR-7',
    usage: 'handle() function returns HTTP Response Interface',
    link: 'https://www.php-fig.org/psr/psr-7/#33-psrhttpmessageresponseinterface'
)]
final class RequestHandler implements RequestHandlerInterface
{
    /**
     * @param string|Closure|array<string|int, string> $fn
     */
    public function __construct(
        private readonly string|Closure|array $fn
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Let resolver inspect method signature & build $arguments[]
        // [$type, $arguments] = $this->resolve($request);
        [$reflectionObject, $type] = $this->detectType();
        $arguments = $this->retrieveArguments($reflectionObject, $request);

        // Invoke the action to get an ActionResponse DTO
        if ($type === 'closure' && $this->fn instanceof Closure) {
            $dto = ($this->fn)(...$arguments);
        } elseif ($type === 'method' && is_array($this->fn)) {
            $dto = (new $this->fn[0]())->{$this->fn[1]}(...$arguments);
        } elseif ($type === 'invoke') {
            $fn = new $this->fn();
            if (is_callable($fn)) {
                $dto = $fn(...$arguments);
            } else {
                throw new InvalidArgumentException('$fn is not callable.');
            }
        } else {
            throw new InvalidArgumentException('$fn is not callable.');
        }

        // Convert DTO to PSR-7 response
        return Lapis::multiResponse()->handle($dto);
    }

    /**
     * @return array{0: ReflectionFunctionAbstract, 1: 'method'|'closure'|'invoke'}
     */
    private function detectType(): array
    {
        $controller = $this->fn;
        $type = 'method';

        if (is_array($controller)) {
            if (
                count($controller) === 2
                && is_string($controller[0])
                && class_exists($controller[0])
                && is_string($controller[1])
            ) {
                $reflectionObject = new ReflectionMethod($controller[0], $controller[1]);
            } elseif (
                count($controller) === 1
                && is_string($controller[0])
                && class_exists($controller[0])
            ) {
                $instance = new $controller[0]();
                if (! is_callable($instance)) {
                    throw new InvalidArgumentException('$fn is not callable.');
                }
                $reflectionObject = new ReflectionMethod($instance, '__invoke');
                $type = 'invoke';
            } else {
                throw new InvalidArgumentException('$fn is not callable.');
            }
        } elseif ($controller instanceof Closure) {
            $reflectionObject = new ReflectionFunction($controller);
            $type = 'closure';
        } elseif (class_exists($controller)) {
            $instance = new $controller();
            if (! is_callable($instance)) {
                throw new InvalidArgumentException('$fn is not callable.');
            }
            $reflectionObject = new ReflectionMethod($controller, '__invoke');
            $type = 'invoke';
        } else {
            throw new InvalidArgumentException('$fn is not callable.');
        }

        return [$reflectionObject, $type];
    }

    /**
     * @return list<mixed>
     */
    private function retrieveArguments(
        ReflectionFunctionAbstract $reflectionObject,
        ServerRequestInterface $request
    ): array {
        $arguments = [];

        $params = $reflectionObject->getParameters();

        $routeVars = $request->getAttribute('routeVars', []);
        $routeVars = is_array($routeVars) ? $routeVars : [];

        $queryVars = $request->getQueryParams();

        foreach ($params as $param) {
            $paramName = $param->getName();
            $paramType = $param->getType();

            $resolved = false;

            if ($paramType instanceof ReflectionNamedType) {
                if (! $paramType->isBuiltin()) {
                    $className = $paramType->getName();
                    $argument = $this->retrieveArgumentForClass($className, $request, $routeVars, $queryVars);
                    if ($argument !== null) {
                        $arguments[] = $argument;
                        $resolved = true;
                    }
                }
            } elseif ($paramType instanceof ReflectionUnionType) {
                foreach ($paramType->getTypes() as $childType) {
                    // union parts can be named types (and in future could include others)
                    if ($childType instanceof ReflectionNamedType && ! $childType->isBuiltin()) {
                        $className = $childType->getName();
                        $argument = $this->retrieveArgumentForClass($className, $request, $routeVars, $queryVars);
                        if ($argument !== null) {
                            $arguments[] = $argument;
                            $resolved = true;
                            break;
                        }
                    }
                }
            } elseif (class_exists(
                ReflectionIntersectionType::class
            ) && $paramType instanceof ReflectionIntersectionType) {
                // intersection types have getTypes(), each is ReflectionNamedType
                foreach ($paramType->getTypes() as $childType) {
                    if ($childType instanceof ReflectionNamedType && ! $childType->isBuiltin()) {
                        $className = $childType->getName();
                        $argument = $this->retrieveArgumentForClass($className, $request, $routeVars, $queryVars);
                        if ($argument !== null) {
                            $arguments[] = $argument;
                            $resolved = true;
                            break;
                        }
                    }
                }
            }

            if ($resolved) {
                continue;
            }

            if (array_key_exists($paramName, $routeVars)) {
                $arguments[] = $routeVars[$paramName];
                continue;
            }

            if (array_key_exists($paramName, $queryVars)) {
                $arguments[] = $queryVars[$paramName];
                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                $arguments[] = $param->getDefaultValue();
                continue;
            }

            if ($param->allowsNull()) {
                $arguments[] = null;
                continue;
            }

            throw new RuntimeException(sprintf(
                'Unable to resolve parameter $%s for %s',
                $paramName,
                $reflectionObject::class . '::' . $reflectionObject->getName()
            ));
        }

        return $arguments;
    }

    /**
     * @param array<string, mixed> $queryVars
     * @param array<string, mixed> $routeVars
     */
    private function retrieveArgumentForClass(
        string $className,
        ServerRequestInterface $request,
        array $routeVars,
        array $queryVars
    ): mixed {
        if ($className === ServerRequestInterface::class) {
            return $request;
        }

        if (is_subclass_of($className, AbstractEntity::class)) {
            /** @var class-string<AbstractEntity> $className */
            $instance = new $className();

            if (! $className::tableExists()) {
                throw new TableNotFoundException($instance->getTable());
            }

            $primaryKeyName = $instance->getKeyName();

            if (array_key_exists($primaryKeyName, $routeVars)) {
                return $className::findOrFail($routeVars[$primaryKeyName]);
            }

            if (array_key_exists('slug', $routeVars)) {
                return $className::where('slug', $routeVars['slug'])->first();
            }

            if (array_key_exists($primaryKeyName, $queryVars)) {
                return $className::findOrFail($queryVars[$primaryKeyName]);
            }

            if (array_key_exists('slug', $queryVars)) {
                return $className::where('slug', $queryVars['slug'])->first();
            }

            return null;
        }

        return null;
    }
}
