<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Entities;

/**
 * User class for customers table.
 *
 * @property int $customer_id
 * @property string|null $name
 * @property string $email
 * @property string $password
 * @property string|null $email_verified_at
 * @property string $status
 * @property string|null $suspended_at
 * @property string $created_at
 * @property string $updated_at
 * @property string $entity_user_type
 */
class Customer extends User
{
    protected $table = 'customers';

    protected $primaryKey = 'customer_id';

    /**
     * @var string
     */
    protected $entity_user_type = 'customer';
}
