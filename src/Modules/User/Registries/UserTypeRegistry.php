<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Registries;

use BitSynama\Lapis\Modules\User\Contracts\UserTypeInterface;
use BitSynama\Lapis\Modules\User\Entities\User;
use Exception;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use function is_object;
use function is_subclass_of;

final class UserTypeRegistry implements ContainerInterface
{
    /**
     * @var array<string, class-string<UserTypeInterface>>
     */
    private array $types = [];

    public function set(string $alias, string $userClass): void
    {
        if (! is_subclass_of($userClass, UserTypeInterface::class)) {
            throw new InvalidArgumentException("{$userClass} must implement UserTypeInterface");
        }
        $this->types[$alias] = $userClass;
    }

    /**
     * @return class-string<UserTypeInterface>
     */
    public function get($alias): string
    {
        if (! isset($this->types[$alias])) {
            throw new class(
                "Unknown user type alias: {$alias}"
            ) extends Exception implements NotFoundExceptionInterface {};
        }
        return $this->types[$alias];
    }

    public function getUserById(string $alias, int $id): User|null
    {
        $userTypeResolver = $this->get($alias);
        $user = $userTypeResolver::findById($id);

        return $user;
    }

    public function getUserByEmail(string $alias, string $email): User|null
    {
        $userTypeResolver = $this->get($alias);
        $user = $userTypeResolver::findByEmail($email);

        return $user;
    }

    public function has($alias): bool
    {
        return isset($this->types[$alias]);
    }

    public function modelFor(string $alias): string
    {
        if (! $this->has($alias)) {
            throw new InvalidArgumentException("Unknown user type alias: {$alias}");
        }
        $userTypeResolver = $this->get($alias);

        return $userTypeResolver::getEntityClass();
    }

    /**
     * @return array<string,string> alias => class (for Eloquent morphMap)
     */
    public function morphMap(): array
    {
        $map = [];
        foreach ($this->types as $alias => $userTypeResolver) {
            $map[$alias] = $userTypeResolver::getEntityClass();
        }

        return $map;
    }

    /**
     * Optional: reverse-lookup for convenience
     */
    public function aliasFor(object|string $model): string|null
    {
        $class = is_object($model) ? $model::class : $model;
        foreach ($this->types as $alias => $userTypeResolver) {
            if ($userTypeResolver::getEntityClass() === $class) {
                return $alias;
            }
        }
        return null;
    }

    /**
     * For tests
     */
    public function reset(): void
    {
        $this->types = [];
    }

    public function isReady(string $alias): bool
    {
        if (! $this->has($alias)) {
            return false;
        }

        $type = $this->get($alias);

        return $type::isReady();
    }

    /**
     * @return string[] aliases that are currently ready
     */
    public function aliasesReadied(): array
    {
        $out = [];
        foreach ($this->types as $alias => $cls) {
            if ($cls::isReady()) {
                $out[] = $alias;
            }
        }

        return $out;
    }
}
