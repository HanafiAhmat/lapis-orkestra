<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\DTO;

final class OtpVerificationDefinition
{
    public string $channel;         // e.g., 'sms' or 'email'

    public string $destination;     // phone number or email address

    public string $token;           // OTP token string

    public string $code;            // OTP code entered by user

    public string|null $fingerprint = null; // optional for device fingerprint
}
