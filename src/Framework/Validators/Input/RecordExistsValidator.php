<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Validators\Input;

use BitSynama\Lapis\Framework\Persistences\AbstractEntity;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception\InvalidArgumentException;

/**
 * @psalm-type AbstractOptions = array{
 *     entityClass?: string,
 *     shouldExists?: bool,
 *     ...<string, mixed>
 * }
 */
class RecordExistsValidator extends AbstractValidator
{
    public const ERROR_EXISTS = 'errorExists';

    public const ERROR_NOT_FOUND = 'errorNotFound';

    /**
     * @var string The entity class (injectable)
     */
    protected string $entityClass;

    protected bool $shouldExists;

    protected array $messageTemplates = [
        self::ERROR_EXISTS => 'The record exists',
        self::ERROR_NOT_FOUND => 'The record is not found',
    ];

    /**
     * @param AbstractOptions $options
     */
    public function __construct(array $options = [])
    {
        $entityClass = $options['entityClass'] ?? null;
        $shouldExists = $options['shouldExists'] ?? true;

        unset($options['entityClass'], $options['shouldExists']);

        if ($entityClass === null) {
            throw new InvalidArgumentException('Option `entityClass` is required');
        }

        $this->entityClass = $entityClass;
        $this->shouldExists = $shouldExists;

        parent::__construct($options);
    }

    public function isValid(mixed $value): bool
    {
        $entity = new $this->entityClass();
        if (! ($entity instanceof AbstractEntity)) {
            throw new InvalidArgumentException('Option `entityClass` must be instanceof AbstractEntity');
        }

        $entity = $entity->find($value);
        if ($this->shouldExists && ! $entity) {
            $this->error(self::ERROR_NOT_FOUND);
            return false;
        }

        if (! $this->shouldExists && $entity) {
            $this->error(self::ERROR_EXISTS);
            return false;
        }

        return true;
    }
}
