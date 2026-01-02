<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Actions\Auth;

use BitSynama\Lapis\Framework\Contracts\LapisEnumInterface;
use BitSynama\Lapis\Framework\Exceptions\BusinessRuleException;
use BitSynama\Lapis\Framework\Exceptions\ValidationException;
use BitSynama\Lapis\Framework\Foundation\EmailComposer;
use BitSynama\Lapis\Framework\Foundation\Security;
use BitSynama\Lapis\Framework\Foundation\TokenLifetime;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\Checkers\RegisterChecker;
use BitSynama\Lapis\Modules\Security\Entities\EmailVerificationToken;
use BitSynama\Lapis\Modules\User\Entities\User;
use BitSynama\Lapis\Services\MailerService;
use Psr\Http\Message\ServerRequestInterface;
use function class_exists;
use function hash;
use function is_string;
use function password_hash;
use function strtolower;
use const PASSWORD_BCRYPT;

class RegisterAction
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

    public function handle(): User
    {
        /** @var lowercase-string $userType */
        $userType = isset($this->data['type']) && is_string($this->data['type']) ? strtolower($this->data['type']) : '';
        if (! Lapis::userTypeRegistry()->has($userType)) {
            throw new BusinessRuleException('Unsupported user type alias');
        }
        if (! Lapis::userTypeRegistry()->isReady($userType)) {
            throw new BusinessRuleException("User type '{$userType}' is not installed or not ready");
        }

        $userTypeResolver = Lapis::userTypeRegistry()->get($userType);
        if (! $userTypeResolver::allowRegistration()) {
            throw new BusinessRuleException('Not allowed to register for this user type');
        }

        $entityClass = $userTypeResolver::getEntityClass();

        /** @var class-string<LapisEnumInterface> $statusEnum */
        $statusEnum = $userTypeResolver::getStatusEnum();

        /** @var class-string<LapisEnumInterface> $roleEnum */
        $roleEnum = $userTypeResolver::getRoleEnum();

        $checker = new RegisterChecker($entityClass, $statusEnum, $roleEnum);
        if (! $checker->isValid($this->data)) {
            throw new ValidationException($checker->getErrors());
        }

        /** @var User $user */
        $user = new $entityClass();

        /** @var string $name */
        $name = $this->data['name'];
        $user->name = $name;

        /** @var string $email */
        $email = $this->data['email'];
        $user->email = $email;

        /** @var string $password */
        $password = $this->data['password'];
        $user->password = password_hash($password, PASSWORD_BCRYPT);

        /** @var string $status */
        $status = $this->data['status'] ?? $statusEnum::default()->value;
        $user->status = $status;
        if (isset($user->role)) {
            /** @var string $role */
            $role = $this->data['role'] ?? $roleEnum::default()->value;
            $user->role = $role;
        }
        $user->save();

        $token = Security::generateSecureToken(64);

        $entity = new EmailVerificationToken();
        $entity->user_type = $userType;
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

        $auditLog = Lapis::interactorRegistry()->getOrSkip('core.system_monitor.audit_log');
        if (is_string($auditLog) && class_exists($auditLog)) {
            $auditLog::record('User registered', [
                'user_type' => $userType,
                'user_id' => $user->getId(),
            ]);
        }

        return $user;
    }
}
