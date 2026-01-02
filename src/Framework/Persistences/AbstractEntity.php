<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Persistences;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

/**
 * @mixin Builder
 * @mixin Model
 */
abstract class AbstractEntity extends Model
{
    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = ['entity_id'];

    public function getId(): int|string
    {
        return $this->{$this->primaryKey};
    }

    public static function countWithinDays(int $days = 7, string $timestampColumn = 'created_at'): int
    {
        $cutoff = Carbon::now()->subDays($days);

        return static::where($timestampColumn, '>=', $cutoff)->count();
    }

    public static function tableExists(): bool
    {
        /** @var static $m */
        $m = new static(); // @phpstan-ignore new.static

        $conn = $m->getConnection();

        /** @var SchemaBuilder $schema */
        $schema = $conn->getSchemaBuilder();

        return $schema->hasTable($m->getTable());
    }

    protected function entityId(): Attribute
    {
        return Attribute::make(get: fn () => $this->{$this->primaryKey});
    }
}
