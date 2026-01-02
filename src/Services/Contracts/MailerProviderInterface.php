<?php declare(strict_types=1);

namespace BitSynama\Lapis\Services\Contracts;

interface MailerProviderInterface
{
    public function send(
        string $to,
        string $subject,
        string $htmlBody,
        string|null $fromAddress = null,
        string|null $fromName = null,
        string|null $textBody = null
    ): bool;
}
