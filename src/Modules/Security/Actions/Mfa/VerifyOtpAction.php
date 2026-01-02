<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Actions\Mfa;

use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\DTO\JwtPayloadDefinition;
use BitSynama\Lapis\Modules\Security\Interactors\MfaInteractor;
use Psr\Http\Message\ServerRequestInterface;

class VerifyOtpAction extends BaseMfaAction
{
    protected int $userId;

    protected string $userType;

    protected string $channel;

    protected string $otp;

    protected bool $remember = false;

    public function __construct(ServerRequestInterface $request)
    {
        /** @var JwtPayloadDefinition $jwtPayload */
        $jwtPayload = Lapis::varRegistry()->get('jwt_payload');

        $this->userId = $jwtPayload->sub;
        $this->userType = $jwtPayload->type;

        /** @var array<string, string|bool> $data */
        $data = $request->getParsedBody();

        /** @var string $channel */
        $channel = $data['type'] ?? 'email';
        $this->channel = $channel;

        /** @var string $otp */
        $otp = $data['otp'] ?? '';
        $this->otp = $otp;

        $this->remember = ($data['remember'] ?? false) === true;
    }

    public function handle(): bool
    {
        $secret = MfaInteractor::getLatest($this->userType, $this->userId, $this->channel);

        if (! $secret || ! MfaInteractor::validate($this->channel, $this->otp, $secret)) {
            $this->audit("OTP failed via {$this->channel}", [
                'user_type' => $this->userType,
                'user_id' => $this->userId,
            ]);
            return false;
        }

        MfaInteractor::markVerified($secret);

        if ($this->remember) {
            /** @var string $fingerprint */
            $fingerprint = Lapis::varRegistry()->get('device_fingerprint');
            if ($fingerprint) {
                MfaInteractor::trustDevice($this->userId, $this->userType, $fingerprint);
            }
        }

        $this->audit("OTP verified via {$this->channel}", [
            'user_type' => $this->userType,
            'user_id' => $this->userId,
        ]);

        return true;
    }
}
