<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Actions\Auth;

use BitSynama\Lapis\Framework\Exceptions\BusinessRuleException;
use BitSynama\Lapis\Framework\Exceptions\NotFoundException;
use BitSynama\Lapis\Framework\Foundation\TokenLifetime;
use BitSynama\Lapis\Lapis;
use Psr\Http\Message\ServerRequestInterface;
use function is_string;
use function strtolower;

class RefreshTokenAction
{
    public function __construct(
        protected ServerRequestInterface $request
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function handle(): array
    {
        $jwtTokenInteractor = Lapis::interactorRegistry()->get('core.security.jwt_token');
        $refreshToken = $jwtTokenInteractor::getRefreshToken($this->request);
        if (! $refreshToken) {
            throw new BusinessRuleException('Missing refresh token');
        }

        $refreshTokenRecord = $jwtTokenInteractor::verifyRefreshToken($refreshToken);
        if (! $refreshTokenRecord) {
            throw new BusinessRuleException('Missing refresh token');
        }

        /** @var lowercase-string $userType */
        $userType = isset($this->data['type']) && is_string($this->data['type']) ? strtolower($this->data['type']) : '';
        if (! Lapis::userTypeRegistry()->has($userType)) {
            throw new BusinessRuleException('Unsupported user type alias');
        }
        if (! Lapis::userTypeRegistry()->isReady($userType)) {
            throw new BusinessRuleException("User type '{$userType}' is not installed or not ready");
        }

        $user = Lapis::userTypeRegistry()->getUserById(alias: $userType, id: $refreshTokenRecord->user_id);
        if ($user) {
            $clientType = Lapis::requestUtility()->getClientType();
            $userAgent = Lapis::requestUtility()->getUserAgent();
            $ipAddress = $this->request->getAttribute('client-ip');

            $newAccessToken = $jwtTokenInteractor::generateAccessToken($user, $clientType);
            $newRefreshToken = $jwtTokenInteractor::rotateRefreshToken(
                $refreshToken,
                $user,
                $clientType,
                $userAgent,
                $ipAddress
            );

            if ($clientType === 'web') {
                $cookieUtility = Lapis::cookieUtility();

                /** @var string $accessTokenLifetime */
                $accessTokenLifetime = Lapis::configRegistry()->get('app.token_lifetime.access');
                $accessTokenExpiry = TokenLifetime::parse($accessTokenLifetime)->getTimestamp();
                $cookieUtility->set('access_token', $newAccessToken, $accessTokenExpiry);

                /** @var string $refreshTokenLifetime */
                $refreshTokenLifetime = Lapis::configRegistry()->get('app.token_lifetime.refresh');
                $refreshTokenExpiry = TokenLifetime::parse($refreshTokenLifetime)->getTimestamp();
                $cookieUtility->set('refresh_token', $newRefreshToken, $refreshTokenExpiry);
            }

            $data = [
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken,
            ];

            return $data;
        }
        throw new NotFoundException('User not found');
    }
}
