<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Actions\Auth;

use BitSynama\Lapis\Framework\Exceptions\BusinessRuleException;
use BitSynama\Lapis\Framework\Exceptions\TableNotFoundException;
use BitSynama\Lapis\Framework\Exceptions\ValidationException;
use BitSynama\Lapis\Framework\Foundation\Constants;
use BitSynama\Lapis\Framework\Foundation\TokenLifetime;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\Checkers\LoginChecker;
use BitSynama\Lapis\Modules\Security\DTO\JwtPayloadDefinition;
use BitSynama\Lapis\Modules\Security\Entities\RefreshToken;
use BitSynama\Lapis\Modules\User\Entities\User;
use Psr\Http\Message\ServerRequestInterface;
use function class_exists;
use function hash;
use function is_string;
use function password_verify;
use function strtolower;

class LoginAction
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
     * @return array<string, string>
     */
    public function handle(): array
    {
        /** @var lowercase-string $userType */
        $userType = isset($this->data['type']) && is_string($this->data['type']) ? strtolower($this->data['type']) : '';
        if (! Lapis::userTypeRegistry()->has($userType)) {
            throw new BusinessRuleException('Unsupported user type alias');
        }
        if (! Lapis::userTypeRegistry()->isReady($userType)) {
            throw new BusinessRuleException("User type '{$userType}' is not installed or not ready");
        }

        if (! RefreshToken::tableExists()) {
            throw new TableNotFoundException((new RefreshToken())->getTable());
        }

        $checker = new LoginChecker();
        if (! $checker->isValid($this->data)) {
            throw new ValidationException($checker->getErrors());
        }

        /** @var string $email */
        $email = $this->data['email'];

        /** @var User|null $user */
        $user = Lapis::userTypeRegistry()->getUserByEmail(alias: $userType, email: $email);

        /** @var string $password */
        $password = $this->data['password'];
        if ($user === null || ! password_verify($password, $user->password)) {
            $errors = [
                'email' => 'Check your entered email',
                'password' => 'Check your entered password',
            ];
            throw new ValidationException($errors, 'Invalid credentials', Constants::STATUS_CODE_UNAUTHORIZED);
        }

        /** @var string $clientType */
        $clientType = Lapis::requestUtility()->getClientType();

        /** @var string $userAgent */
        $userAgent = Lapis::requestUtility()->getUserAgent();

        /** @var string $ipAddress */
        $ipAddress = $this->request->getAttribute('client-ip');

        $jwtTokenInteractor = Lapis::interactorRegistry()->get('core.security.jwt_token');

        /** @var string $accessToken */
        $accessToken = $jwtTokenInteractor::generateAccessToken($user, $clientType);

        /** @var string $refreshToken */
        $refreshToken = $jwtTokenInteractor::generateRefreshToken();

        /** @var string $refreshTokenLifetime */
        $refreshTokenLifetime = Lapis::configRegistry()->get('app.token_lifetime.refresh');
        $refreshTokenExpiry = TokenLifetime::parse($refreshTokenLifetime)->format('Y-m-d H:i:s');

        $record = new RefreshToken();
        $record->user_type = $userType;
        $record->user_id = $user->getId();
        $record->token_hash = hash('sha256', $refreshToken);
        $record->client_type = $clientType;
        $record->user_agent = $userAgent;
        $record->ip_address = $ipAddress;
        $record->expires_at = $refreshTokenExpiry;
        $record->save();

        $auditLog = Lapis::interactorRegistry()->getOrSkip('core.system_monitor.audit_log');
        if (is_string($auditLog) && class_exists($auditLog)) {
            $auditLog::record('User logged in', [
                'user_type' => $userType,
                'user_id' => $user->getId(),
                'client_type' => $clientType,
            ]);
        }

        // Clear login attempts on successful login
        if ($throttleService = Lapis::interactorRegistry()->getOrSkip('core.system_monitor.throttle')) {
            // $urlEndpoint = Lapis::requestUtility()->getCurrentUrl();
            // $cacheKey = $throttleService::generateCacheKey('login_throttle', $urlEndpoint);
            // $throttleService::clear($cacheKey);
        }

        // Set cookies if client is web
        if ($clientType === 'web') {
            $cookieUtility = Lapis::cookieUtility();

            /** @var string $accessTokenLifetime */
            $accessTokenLifetime = Lapis::configRegistry()->get('app.token_lifetime.access');
            $accessTokenExpiry = TokenLifetime::parse($accessTokenLifetime)->getTimestamp();
            $cookieUtility->set('access_token', $accessToken, $accessTokenExpiry);

            /** @var string $refreshTokenLifetime */
            $refreshTokenLifetime = Lapis::configRegistry()->get('app.token_lifetime.refresh');
            $refreshTokenExpiry = TokenLifetime::parse($refreshTokenLifetime)->getTimestamp();
            $cookieUtility->set('refresh_token', $refreshToken, $refreshTokenExpiry);
        }

        /** @var JwtPayloadDefinition $jwtPayload */
        $jwtPayload = $jwtTokenInteractor::verifyAccessToken($accessToken);
        $this->request = $this->request->withAttribute('jwt_payload', $jwtPayload);
        $this->request = $this->request->withAttribute('user', $user);
        Lapis::varRegistry()->set('jwt_payload', $jwtPayload);
        Lapis::varRegistry()->set('user', $user);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }
}
