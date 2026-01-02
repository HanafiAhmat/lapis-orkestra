<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Actions\Auth;

use BitSynama\Lapis\Framework\Exceptions\BusinessRuleException;
use BitSynama\Lapis\Framework\Foundation\EmailComposer;
use BitSynama\Lapis\Framework\Foundation\Security;
use BitSynama\Lapis\Framework\Foundation\TokenLifetime;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\Entities\EmailVerificationToken;
use BitSynama\Lapis\Modules\User\Entities\User;
use BitSynama\Lapis\Services\MailerService;
use function hash;

class ResendVerifyEmailAction
{
    public function handle(User|null $user): void
    {
        if (empty($user)) {
            throw new BusinessRuleException('Logged In User is required');
        }

        $token = Security::generateSecureToken(64);

        $entity = new EmailVerificationToken();
        $entity->user_type = $user->user_type;
        $entity->user_id = (int) $user->getId();
        $entity->token_hash = hash('sha256', $token);

        /** @var string $tokenLifetime */
        $tokenLifetime = Lapis::configRegistry()->get('app.token_lifetime.email_verification');
        $entity->expires_at = TokenLifetime::parse($tokenLifetime)->format('Y-m-d H:i:s');
        $entity->save();

        $emailData = [
            'baseUrl' => Lapis::configRegistry()->get('app.frontend_url'),
            'token' => $token,
        ];
        $emailService = MailerService::send(
            to: $user->email,
            subject: 'Email Verification',
            htmlBody: EmailComposer::renderHtml(template: 'email.email-verification-html', data: $emailData),
            textBody: EmailComposer::renderText(template: 'email.email-verification-text', data: $emailData)
        );
    }
}
