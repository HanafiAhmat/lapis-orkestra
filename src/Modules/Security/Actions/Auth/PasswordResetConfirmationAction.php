<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Actions\Auth;

use BitSynama\Lapis\Framework\Exceptions\BusinessRuleException;
use BitSynama\Lapis\Framework\Exceptions\NotFoundException;
use BitSynama\Lapis\Framework\Exceptions\ValidationException;
use BitSynama\Lapis\Framework\Foundation\Clock;
use BitSynama\Lapis\Framework\Persistences\AbstractEntity;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\Checkers\PasswordResetConfirmationChecker;
use BitSynama\Lapis\Modules\Security\Entities\PasswordResetToken;
use BitSynama\Lapis\Modules\User\Entities\User;
use Psr\Http\Message\ServerRequestInterface;
use function class_exists;
use function hash;
use function is_string;
use function password_hash;
use const PASSWORD_BCRYPT;

class PasswordResetConfirmationAction
{
    /**
     * @var array<string, mixed>
     */
    protected array $data;

    public function __construct(
        protected ServerRequestInterface $request
    ) {
        /** @var array<string, mixed> $body */
        $body = $request->getParsedBody() ?: [];
        $this->data = $body;
    }

    /**
     * @return array<string, AbstractEntity|User>
     */
    public function getTokenInformation(string $token): array
    {
        /** @var PasswordResetToken|null $record */
        $record = PasswordResetToken::where('token_hash', hash('sha256', $token))->first();
        if (
            empty($record)
            || (
                (
                    $record->expires_at instanceof Clock
                    && $record->expires_at->getTimestamp() < Clock::now()->getTimestamp()
                ) || (
                    is_string($record->expires_at)
                    && Clock::parse($record->expires_at)->getTimestamp() < Clock::now()->getTimestamp()
                )
            )
        ) {
            // ApiResponse::fail('Invalid or expired token');
            throw new BusinessRuleException('Invalid or expired token');
        }

        $userType = $record->user_type;
        if (! Lapis::userTypeRegistry()->has($userType)) {
            throw new BusinessRuleException('Unsupported user type alias');
        }
        if (! Lapis::userTypeRegistry()->isReady($userType)) {
            throw new BusinessRuleException("User type '{$userType}' is not installed or not ready");
        }

        /** @var User|null $user */
        $user = Lapis::userTypeRegistry()->getUserById(alias: $userType, id: $record->user_id);
        if (empty($user)) {
            throw new NotFoundException('User not found');
        }

        return [
            'record' => $record,
            'user' => $user,
        ];
    }

    public function getUserFromToken(string $token): User|null
    {
        $tokenInformation = $this->getTokenInformation($token);

        /** @var User|null $user */
        $user = $tokenInformation['user'] ?? null;

        return $user;
    }

    public function handle(): void
    {
        /** @var string $token */
        $token = $this->data['token'];
        $tokenInformation = $this->getTokenInformation($token);

        /** @var AbstractEntity $record */
        $record = $tokenInformation['record'];

        /** @var User $user */
        $user = $tokenInformation['user'];

        $checker = new PasswordResetConfirmationChecker();
        if (! $checker->isValid($this->data)) {
            throw new ValidationException($checker->getErrors());
        }

        /** @var string $password */
        $password = $this->data['password'];
        $user->password = password_hash($password, PASSWORD_BCRYPT);
        $user->save();

        $record->delete();

        $auditLog = Lapis::interactorRegistry()->getOrSkip('core.system_monitor.audit_log');
        if (is_string($auditLog) && class_exists($auditLog)) {
            $clientType = Lapis::requestUtility()->getClientType();
            $auditLog::record('Password Reset successful', [
                'user_type' => $user->user_type,
                'user_id' => $user->getId(),
                'client_type' => $clientType,
            ]);
        }
    }
}
