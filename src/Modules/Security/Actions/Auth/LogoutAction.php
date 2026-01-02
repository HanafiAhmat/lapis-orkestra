<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Actions\Auth;

use BitSynama\Lapis\Framework\Exceptions\BusinessRuleException;
use BitSynama\Lapis\Framework\Exceptions\TableNotFoundException;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\DTO\JwtPayloadDefinition;
use BitSynama\Lapis\Modules\Security\Entities\RefreshToken;
use BitSynama\Lapis\Modules\Security\Entities\RevokedToken;
use Carbon\Carbon;
use Psr\Http\Message\ServerRequestInterface;
use function class_exists;
use function hash;
use function is_string;

class LogoutAction
{
    public function __construct(
        protected ServerRequestInterface $request
    ) {
    }

    public function handle(): void
    {
        if (! RefreshToken::tableExists()) {
            throw new TableNotFoundException((new RefreshToken())->getTable());
        }

        if (! RevokedToken::tableExists()) {
            throw new TableNotFoundException((new RevokedToken())->getTable());
        }

        $this->revokeAccessToken();

        /** @var string $clientType */
        $clientType = Lapis::requestUtility()->getClientType();
        if ($clientType === 'web') {
            $cookieUtility = Lapis::cookieUtility();
            $cookieUtility->delete('access_token');
            $cookieUtility->delete('refresh_token');
        }

        Lapis::varRegistry()->remove('user');

        /** @var JwtPayloadDefinition $jwtPayload */
        $jwtPayload = $this->request->getAttribute('jwt_payload');
        if (! $jwtPayload) {
            // throw new BusinessRuleException("Access token has expired");
            return;
        }

        // Revoke current access token by jti
        $entity = new RevokedToken();
        $entity->jti = $jwtPayload->jti;
        $entity->user_type = $jwtPayload->type;
        $entity->user_id = $jwtPayload->sub;
        $entity->revoked_at = Carbon::now()->format('Y-m-d H:i:s');
        $entity->save();

        $auditLog = Lapis::interactorRegistry()->getOrSkip('core.system_monitor.audit_log');
        if (is_string($auditLog) && class_exists($auditLog)) {
            $auditLog::record('User logged token', [
                'user_id' => $jwtPayload->sub,
                'user_type' => $jwtPayload->type,
                'client_type' => $clientType,
            ]);
        }
    }

    protected function revokeAccessToken(): void
    {
        $jwtTokenInteractor = Lapis::interactorRegistry()->get('core.security.jwt_token');

        /** @var string $refreshToken */
        $refreshToken = $jwtTokenInteractor::getRefreshToken($this->request);
        if ($refreshToken) {
            /** @var RefreshToken|null $record */
            $record = RefreshToken::where('token_hash', hash('sha256', $refreshToken))->first();
            if ($record) {
                $record->delete();
            }
        }
    }
}
