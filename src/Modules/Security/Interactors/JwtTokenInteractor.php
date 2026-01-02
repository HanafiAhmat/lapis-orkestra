<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Interactors;

use BitSynama\Lapis\Framework\Contracts\InteractorInterface;
use BitSynama\Lapis\Framework\Exceptions\TableNotFoundException;
use BitSynama\Lapis\Framework\Foundation\Clock;
use BitSynama\Lapis\Framework\Foundation\Security;
use BitSynama\Lapis\Framework\Foundation\TokenLifetime;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\DTO\JwtPayloadDefinition;
use BitSynama\Lapis\Modules\Security\Entities\RefreshToken;
use BitSynama\Lapis\Modules\Security\Factories\JwtPayloadFactory;
use BitSynama\Lapis\Modules\User\Entities\User;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use function class_exists;
use function count;
use function hash;
use function in_array;
use function is_string;
use function preg_match;

class JwtTokenInteractor implements InteractorInterface
{
    /**
     * Generate a JWT access token for the given user and audience.
     */
    public static function generateAccessToken(User $user, string $audience): string
    {
        if (empty($audience)) {
            throw new InvalidArgumentException('Audience cannot be empty');
        }

        if (! $user->getId()) {
            throw new InvalidArgumentException('User ID is required');
        }

        $configRegistry = Lapis::configRegistry();

        /** @var string $encryptionKey */
        $encryptionKey = $configRegistry->get('app.encryption_key');

        /** @var string $accessTokenLifetime */
        $accessTokenLifetime = $configRegistry->get('app.token_lifetime.access');
        $accessTokenExpiry = TokenLifetime::parse($accessTokenLifetime);
        $fingerprint = Lapis::varRegistry()->getOrSkip('device_fingerprint') ?: null;

        $payload = [
            'iss' => $configRegistry->get('app.jwt_issuer'),
            'aud' => $audience,
            'sub' => $user->getId(),
            'type' => $user->user_type,
            'role' => $user->role ?? null,
            'name' => $user->name ?? null,
            'jti' => self::generateRefreshToken(16),
            'iat' => Carbon::now()->getTimestamp(),
            'exp' => $accessTokenExpiry->getTimestamp(),
            'fp' => $fingerprint,
        ];

        return JWT::encode($payload, $encryptionKey, 'HS256');
    }

    public static function getAccessToken(ServerRequestInterface|null $request = null): string|null
    {
        $requestUtility = Lapis::requestUtility();
        $request ??= $requestUtility->getRequest();
        $clientType = $requestUtility->getClientType();

        if ($clientType === 'web') {
            if (Lapis::cookieUtility()->has('access_token')) {
                /** @var string $accessToken */
                $accessToken = Lapis::cookieUtility()->get('access_token');

                return $accessToken;
            }
        }

        /** @var array<int, string> $allowedAudiences */
        $allowedAudiences = Lapis::configRegistry()->get('app.allowed_audiences');
        if (in_array($clientType, $allowedAudiences, true)) {
            $authorization = $request->getHeader('authorization');
            if (count($authorization) > 0) {
                if (preg_match("/^Bearer\s+(.*)$/", $authorization[0], $matches)) {
                    if (count($matches) > 1) {
                        return $matches[1];
                    }
                }

                return null;
            }
        }

        return null;
    }

    /**
     * Generate a secure refresh token.
     */
    public static function generateRefreshToken(int $length = 64): string
    {
        return Security::generateSecureToken($length);
    }

    /**
     * Extract the refresh token from headers or cookies.
     */
    public static function getRefreshToken(ServerRequestInterface|null $request = null): string|null
    {
        $requestUtility = Lapis::requestUtility();
        $request = $request ?: $requestUtility->getRequest();
        $clientType = $requestUtility->getClientType();

        if ($clientType === 'web') {
            if (Lapis::cookieUtility()->has('refresh_token')) {
                /** @var string $refreshToken */
                $refreshToken = Lapis::cookieUtility()->get('refresh_token');

                return $refreshToken;
            }
        }

        // $allowedAudiences = Lapis::configRegistry()->get('app.allowed_audiences');
        // if (in_array($clientType, $allowedAudiences, true)) {
        $authorization = $request->getHeader('authorization-refresh');
        if (count($authorization) > 0) {
            return $authorization[0];
        }
        // }

        return null;
    }

    /**
     * Rotate refresh token securely.
     */
    public static function rotateRefreshToken(
        string $oldToken,
        User $user,
        string $clientType,
        string $userAgent,
        string $ipAddress
    ): string {
        /** @var RefreshToken|null $record */
        $record = RefreshToken::where('token_hash', hash('sha256', $oldToken))->first();
        if ($record !== null) {
            $record->delete();
        }

        $newRefreshToken = self::generateRefreshToken();

        /** @var string $refreshTokenLifetime */
        $refreshTokenLifetime = Lapis::configRegistry()->get('app.token_lifetime.refresh');
        $refreshTokenExpiry = TokenLifetime::parse($refreshTokenLifetime)->format('Y-m-d H:i:s');

        $entity = new RefreshToken();
        $entity->user_type = $user->user_type;
        $entity->user_id = $user->getId();
        $entity->token_hash = hash('sha256', $newRefreshToken);
        $entity->client_type = $clientType;
        $entity->user_agent = $userAgent;
        $entity->ip_address = $ipAddress;
        $entity->expires_at = $refreshTokenExpiry;
        $entity->save();

        if ($auditLog = Lapis::interactorRegistry()->getOrSkip('core.system_monitor.audit_log')) {
            if (is_string($auditLog) && class_exists($auditLog)) {
                $auditLog::record('User refreshed token', [
                    'user_type' => $user->user_type,
                    'user_id' => $user->getId(),
                    'client_type' => $clientType,
                ]);
            }
        }

        return $newRefreshToken;
    }

    /**
     * Decode and validate the given access token string.
     * Returns a JwtPayloadDefinition or null if invalid.
     */
    public static function verifyAccessToken(string $token): JwtPayloadDefinition|null
    {
        $config = Lapis::configRegistry();

        /** @var string $encryptionKey */
        $encryptionKey = $config->get('app.encryption_key');
        try {
            $decoded = JWT::decode($token, new Key($encryptionKey, 'HS256'));
            /** @var JwtPayloadDefinition $jwtPayload */
            $jwtPayload = JwtPayloadFactory::fromPayload($decoded);
        } catch (Throwable) {
            return null;
        }

        // Check fingerprint match (if required)
        if (! empty($jwtPayload->fp)) {
            $currentFp = Lapis::varRegistry()->get('device_fingerprint') ?? null;
            if ($currentFp !== $jwtPayload->fp) {
                // throw new InvalidArgumentException('Token fingerprint mismatch');
                return null;
            }
        }

        // Revoke check
        if (RevokedTokenInteractor::isRevoked($jwtPayload->jti)) {
            // throw new InvalidArgumentException('Token has been revoked');
            return null;
        }

        return $jwtPayload;
    }

    /**
     * Handle refresh token validation.
     */
    public static function verifyRefreshToken(string $refreshToken): RefreshToken|bool
    {
        if (! RefreshToken::tableExists()) {
            throw new TableNotFoundException((new RefreshToken())->getTable());
        }

        /** @var RefreshToken|null $record */
        $record = RefreshToken::where('token_hash', hash('sha256', $refreshToken))->first();
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
            // ApiResponse::fail('Invalid or expired refresh token', [], Constants::STATUS_CODE_UNAUTHORIZED);
            return false;
        }

        return $record;
    }
}
