<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Actions\Mfa;

use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\DTO\JwtPayloadDefinition;
use BitSynama\Lapis\Modules\Security\Entities\MfaSecret;
use Psr\Http\Message\ServerRequestInterface;
use function is_int;

class RevokeTrustedDevicesAction extends BaseMfaAction
{
    protected int $userId;

    protected string $userType;

    protected string|null $fingerprint = null;

    public function __construct(ServerRequestInterface $request)
    {
        /** @var JwtPayloadDefinition $jwtPayload */
        $jwtPayload = Lapis::varRegistry()->get('jwt_payload');

        /** @var array<string, string> $data */
        $data = $request->getParsedBody();

        /** @var string|null $fingerprint */
        $fingerprint = $data['fingerprint'] ?? null;

        $this->userId = $jwtPayload->sub;
        $this->userType = $jwtPayload->type;
        $this->fingerprint = $fingerprint;
    }

    public function handle(): int
    {
        $query = MfaSecret::where('user_id', $this->userId)
            ->where('user_type', $this->userType)
            ->where('type', 'trusted');

        if ($this->fingerprint !== null) {
            $query = $query->where('device_fingerprint', $this->fingerprint);
        }

        $count = $query->delete();

        $this->audit('Trusted device(s) revoked', [
            'user_type' => $this->userType,
            'user_id' => $this->userId,
            'fingerprint' => $this->fingerprint,
        ]);

        return is_int($count) ? $count : 0;
    }
}
