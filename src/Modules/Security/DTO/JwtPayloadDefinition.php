<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\DTO;

final class JwtPayloadDefinition
{
    /**
     * Issuer of the token (e.g. API base URL).
     */
    public string $iss;

    /**
     * Audience for the token (e.g. web, mobile).
     */
    public string $aud;

    /**
     * Subject - usually the user ID.
     */
    public int $sub;

    /**
     * User type (e.g. staff, customer).
     */
    public string $type;

    /**
     * Token ID (used for refresh token rotation & revocation).
     */
    public string $jti;

    /**
     * Issued at timestamp (UNIX).
     */
    public int $iat;

    /**
     * Expiry timestamp (UNIX).
     */
    public int $exp;

    /**
     * Optional user role.
     */
    public string|null $role = null;

    /**
     * Optional device fingerprint.
     */
    public string|null $fp = null;

    /**
     * Optional User name.
     */
    public string|null $name = null;
}
