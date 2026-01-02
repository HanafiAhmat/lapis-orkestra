<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Actions\Mfa;

use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\DTO\JwtPayloadDefinition;
use BitSynama\Lapis\Modules\Security\Interactors\MfaInteractor;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class IssueOtpAction extends BaseMfaAction
{
    protected int $userId;

    protected string $userType;

    protected string $channel;

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
    }

    public function handle(): void
    {
        $otp = MfaInteractor::generateOtp($this->channel);
        $secret = MfaInteractor::upsertSecret($this->userId, $this->userType, $this->channel, $otp);

        match ($this->channel) {
            'email' => $this->handleEmail($otp),
            'sms' => $this->handleSms($otp),
            'totp' => $this->handleTotp(),
            default => throw new InvalidArgumentException("Unsupported MFA channel: {$this->channel}"),
        };

        MfaInteractor::markSent($secret);
        $this->audit("OTP issued via {$this->channel}", [
            'user_type' => $this->userType,
            'user_id' => $this->userId,
        ]);
    }

    private function handleEmail(string $otp): void
    {
        $user = $this->resolveUser($this->userType, $this->userId);
        if (! $user || empty($user->email)) {
            throw new RuntimeException("User email not found for {$this->userType}:{$this->userId}");
        }
        $this->sendEmailOtp($user->email, $otp);
    }

    private function handleSms(string $otp): void
    {
        $user = $this->resolveUser($this->userType, $this->userId);
        if (! $user || empty($user->phone)) {
            throw new RuntimeException("User phone not found for {$this->userType}:{$this->userId}");
        }
        // TODO: Integrate SMS provider here.
    }

    private function handleTotp(): void
    {
        $user = $this->resolveUser($this->userType, $this->userId);
        if (! $user || empty($user->email)) {
            throw new RuntimeException('User email not found for TOTP setup');
        }

        $label = $user->email;
        $secret = MfaInteractor::generateOtp('totp');
        $uri = MfaInteractor::generateTotpSetupUri($label, $secret);

        $this->audit('TOTP setup initiated', [
            'user_type' => $this->userType,
            'user_id' => $this->userId,
            'uri' => $uri,
        ]);
    }
}
