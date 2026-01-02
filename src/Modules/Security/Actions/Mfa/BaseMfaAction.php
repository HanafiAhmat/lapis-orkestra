<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Actions\Mfa;

use BitSynama\Lapis\Framework\Foundation\EmailComposer;
use BitSynama\Lapis\Modules\Security\Interactors\MfaInteractor;
// use BitSynama\Lapis\Modules\SystemMonitor\Services\AuditLogService;
use BitSynama\Lapis\Modules\User\Entities\User;
use BitSynama\Lapis\Services\MailerService;

abstract class BaseMfaAction
{
    protected function resolveUser(string $userType, int $userId): User|null
    {
        return MfaInteractor::resolveUser($userType, $userId);
    }

    protected function sendEmailOtp(string $email, string $otp): void
    {
        $emailService = MailerService::send(
            to: $email,
            subject: 'Your One-Time Password (OTP)',
            htmlBody: EmailComposer::renderHtml(
                template: 'email.generic-html',
                data: [
                    'content' => "Your verification code is: <strong>{$otp}</strong><br>This code will expire shortly.",
                ]
            ),
            textBody: EmailComposer::renderText(
                template: 'email.generic-text',
                data: [
                    'content' => "Your verification code is: {$otp}\nThis code will expire shortly.",
                ]
            )
        );

    }

    /**
     * @param array<string, mixed> $context
     */
    protected function audit(string $message, array $context = []): void
    {
        // AuditLogService::record($message, $context);
    }
}
