<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Actions\Auth;

use BitSynama\Lapis\Framework\Exceptions\BusinessRuleException;
use BitSynama\Lapis\Framework\Exceptions\ValidationException;
use BitSynama\Lapis\Framework\Foundation\EmailComposer;
use BitSynama\Lapis\Framework\Foundation\Security;
use BitSynama\Lapis\Framework\Foundation\TokenLifetime;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\Checkers\PasswordResetRequestChecker;
use BitSynama\Lapis\Modules\Security\Entities\PasswordResetToken;
use BitSynama\Lapis\Services\MailerService;
use Psr\Http\Message\ServerRequestInterface;
use function class_exists;
use function hash;
use function is_string;
use function strtolower;

class PasswordResetRequestAction
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

    public function handle(): void
    {
        /** @var lowercase-string $userType */
        $userType = isset($this->data['type']) && is_string($this->data['type']) ? strtolower($this->data['type']) : '';
        if (! Lapis::userTypeRegistry()->has($userType)) {
            throw new BusinessRuleException('Unsupported user type alias');
        }
        if (! Lapis::userTypeRegistry()->isReady($userType)) {
            throw new BusinessRuleException("User type '{$userType}' is not installed or not ready");
        }

        $checker = new PasswordResetRequestChecker();
        if (! $checker->isValid($this->data)) {
            throw new ValidationException($checker->getErrors());
        }

        /** @var string $email */
        $email = $this->data['email'] ?? '';
        $user = Lapis::userTypeRegistry()->getUserByEmail(alias: $userType, email: $email);
        if (empty($user)) {
            return; // Do not reveal user existence
        }

        $token = Security::generateSecureToken(64);

        /** @var string $clientType */
        $clientType = Lapis::requestUtility()->getClientType();

        /** @var string $userAgent */
        $userAgent = Lapis::requestUtility()->getUserAgent();

        /** @var string $ipAddress */
        $ipAddress = $this->request->getAttribute('client-ip');

        $entity = new PasswordResetToken();
        $entity->user_type = $user->user_type;

        /** @var int $userId */
        $userId = $user->getId();
        $entity->user_id = $userId;
        $entity->token_hash = hash('sha256', $token);
        $entity->client_type = $clientType;
        $entity->user_agent = $userAgent;
        $entity->ip_address = $ipAddress;

        /** @var string $tokenLifetime */
        $tokenLifetime = Lapis::configRegistry()->get('app.token_lifetime.password_reset');
        $entity->expires_at = TokenLifetime::parse($tokenLifetime)->format('Y-m-d H:i:s');
        $entity->save();

        $emailData = [
            'baseUrl' => Lapis::requestUtility()->getCurrentAppBaseUrl(),
            'token' => $token,
        ];
        $emailService = MailerService::send(
            to: $user->email,
            subject: 'Password Reset Request',
            htmlBody: EmailComposer::renderHtml(template: 'email.password-reset-request-html', data: $emailData),
            textBody: EmailComposer::renderText(template: 'email.password-reset-request-text', data: $emailData)
        );

        $auditLog = Lapis::interactorRegistry()->getOrSkip('core.system_monitor.audit_log');
        if (is_string($auditLog) && class_exists($auditLog)) {
            $auditLog::record('Password Reset Request', [
                'user_type' => $user->user_type,
                'user_id' => $user->getId(),
            ]);
        }
    }
}
