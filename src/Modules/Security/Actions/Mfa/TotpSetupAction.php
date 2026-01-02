<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Actions\Mfa;

use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\DTO\JwtPayloadDefinition;
use BitSynama\Lapis\Modules\Security\Interactors\MfaInteractor;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use function base64_encode;

class TotpSetupAction extends BaseMfaAction
{
    protected int $userId;

    protected string $userType;

    protected string $userLabel;

    public function __construct()
    {
        /** @var JwtPayloadDefinition $jwtPayload */
        $jwtPayload = Lapis::varRegistry()->get('jwt_payload');

        $this->userId = $jwtPayload->sub;
        $this->userType = $jwtPayload->type;
        $this->userLabel = $jwtPayload->name ?? 'AnonymousUser';
    }

    /**
     * @return array<string, string>
     */
    public function handle(): array
    {
        /** @var string $secret */
        $secret = MfaInteractor::generateOtp('totp');

        /** @var string $uri */
        $uri = MfaInteractor::generateTotpSetupUri($this->userLabel, $secret);

        MfaInteractor::upsertSecret($this->userId, $this->userType, 'totp', $secret);

        $this->audit('TOTP setup issued', [
            'user_id' => $this->userId,
            'user_type' => $this->userType,
        ]);

        $qr = Builder::create()
            ->writer(new PngWriter())
            ->data($uri)
            ->size(300)
            ->build();

        $qrString = 'data:image/png;base64,' . base64_encode((string) $qr->getString());

        return [
            'secret' => $secret,
            'uri' => $uri,
            'qr_base64' => $qrString,
        ];
    }
}
