<?php declare(strict_types=1);

namespace BitSynama\Lapis\Services;

use BitSynama\Lapis\Framework\Foundation\Atlas;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Services\Contracts\MailerProviderInterface;
use RuntimeException;

class MailerService
{
    private static MailerProviderInterface|null $provider = null;

    public static function send(
        string $to,
        string $subject,
        string $htmlBody,
        string|null $fromAddress = null,
        string|null $fromName = null,
        string|null $textBody = null
    ): bool {
        $provider = self::resolveProvider();

        return $provider->send($to, $subject, $htmlBody, $fromAddress, $fromName, $textBody);
    }

    protected static function resolveProvider(): MailerProviderInterface
    {
        if (self::$provider) {
            return self::$provider;
        }

        /** @var string $projectDir */
        $projectDir = Lapis::varRegistry()->get('project_dir');

        /** @var string $repoDir */
        $repoDir = Lapis::varRegistry()->get('repo_dir');

        /** @var string $providerKey */
        $providerKey = Lapis::configRegistry()->get('service.mail.provider') ?? 'mail';

        /** @var string $provider */
        $provider = Atlas::discover(
            dirPath: 'Services.Providers.Mailer',
            interface: MailerProviderInterface::class,
            attribute: ProviderInfo::class,
            classSuffix: 'MailerProvider',
            type: 'mailer',
            key: $providerKey,
            repoDir: $repoDir,
            projectDir: $projectDir
        );

        if (empty($provider)) {
            throw new RuntimeException("Mailer provider '{$providerKey}' not found.");
        }

        /** @var MailerProviderInterface $provider */
        $provider = new $provider();
        self::$provider = $provider;

        return self::$provider;
    }
}
