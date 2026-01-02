<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Validators\Input;

use BitSynama\Lapis\Framework\Persistences\AbstractEntity;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception\InvalidArgumentException;

/**
 * @psalm-type AbstractOptions = array{
 *     entityClass?: string,
 *     currentId?: int,
 *     ...<string, mixed>
 * }
 */
class UniqueSlugValidator extends AbstractValidator
{
    public const ERROR_EXISTS = 'errorExists';

    /**
     * @var string The User entity class (injectable)
     */
    protected string $entityClass;

    /**
     * @var int Existing User ID (injectable)
     */
    protected int $currentId;

    protected array $messageTemplates = [
        self::ERROR_EXISTS => 'Slug value already taken',
    ];

    /**
     * @param AbstractOptions $options
     */
    public function __construct(array $options = [])
    {
        $entityClass = $options['entityClass'] ?? null;
        $currentId = $options['currentId'] ?? 0;

        unset($options['entityClass'], $options['currentId']);

        if ($entityClass === null) {
            throw new InvalidArgumentException('Option `entityClass` is required');
        }

        $this->entityClass = $entityClass;
        $this->currentId = $currentId;

        parent::__construct($options);
    }

    public function isValid(mixed $value): bool
    {
        $entityClass = new $this->entityClass();
        if (! ($entityClass instanceof AbstractEntity)) {
            throw new InvalidArgumentException('Option `entityClass` must be instanceof AbstractEntity');
        }

        /** @var AbstractEntity|null $entity */
        $entity = $entityClass->where('slug', $value)
            ->first();
        if ($entity && $entity->getId() !== $this->currentId) {
            $this->error(self::ERROR_EXISTS);
            return false;
        }

        return true;
    }
}
