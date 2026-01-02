<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Actions\Auth;

use BitSynama\Lapis\Framework\Exceptions\BusinessRuleException;
use BitSynama\Lapis\Framework\Exceptions\NotFoundException;
use BitSynama\Lapis\Framework\Foundation\Clock;
use BitSynama\Lapis\Framework\Persistences\AbstractEntity;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\Entities\EmailVerificationToken;
use function class_exists;
use function hash;
use function is_string;
use function strtolower;

class VerifyEmailAction
{
    public function handle(string $token): AbstractEntity
    {
        if (empty($token)) {
            throw new BusinessRuleException('Missing token');
        }

        /** @var EmailVerificationToken|null $record */
        $record = EmailVerificationToken::where('token_hash', hash('sha256', (string) $token))->first();
        if (
            ! $record
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
            throw new BusinessRuleException('Invalid or expired token');
        }

        $userType = isset($this->data['type']) ? strtolower($this->data['type']) : '';
        if (! Lapis::userTypeRegistry()->has($userType)) {
            throw new BusinessRuleException('Unsupported user type alias');
        }
        if (! Lapis::userTypeRegistry()->isReady($userType)) {
            throw new BusinessRuleException("User type '{$userType}' is not installed or not ready");
        }

        $user = Lapis::userTypeRegistry()->getUserById(alias: $userType, id: $record->user_id);
        if (empty($user)) {
            throw new NotFoundException('User not found');
        }

        $user->email_verified_at = Clock::now()->format('Y-m-d H:i:s');
        $user->save();

        $record->delete();

        $auditLog = Lapis::interactorRegistry()->getOrSkip('core.system_monitor.audit_log');
        if (is_string($auditLog) && class_exists($auditLog)) {
            $clientType = Lapis::requestUtility()->getClientType();
            $auditLog::record('Email verified', [
                'user_type' => $userType,
                'user_id' => $user->getId(),
                'client_type' => $clientType,
            ]);
        }

        return $user;
    }
}
