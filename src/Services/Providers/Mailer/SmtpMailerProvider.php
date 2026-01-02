<?php declare(strict_types=1);

namespace BitSynama\Lapis\Services\Providers\Mailer;

use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Services\Contracts\MailerProviderInterface;
use BitSynama\Lapis\Services\ProviderInfo;
use PHPMailer\PHPMailer\PHPMailer;
use function strip_tags;

#[ProviderInfo(type: 'mailer', key: 'smtp', description: 'Uses SMTP connection')]
class SmtpMailerProvider implements MailerProviderInterface
{
    public function __construct(
        protected PHPMailer|null $mailer = null
    ) {
        $this->mailer = $mailer ?? new PHPMailer(true);
    }

    public function send(
        string $to,
        string $subject,
        string $htmlBody,
        string|null $fromAddress = null,
        string|null $fromName = null,
        string|null $textBody = null
    ): bool {
        $config = Lapis::configRegistry();

        /** @var string $fromAddress */
        $fromAddress ??= $config->get('service.mail.default_from');

        /** @var string $fromName */
        $fromName ??= $config->get('service.mail.default_from_name');

        if ($this->mailer instanceof PHPMailer) {
            $this->mailer->isSMTP();

            /** @var string $host */
            $host = $config->get('service.mail.smtp.host');
            $this->mailer->Host = $host;

            /** @var int $port */
            $port = $config->get('service.mail.smtp.port') ?? 587;
            $this->mailer->Port = $port;

            /** @var bool $SMTPAuth */
            $SMTPAuth = $config->get('service.mail.smtp.authenticate') ?? false;
            $this->mailer->SMTPAuth = $SMTPAuth;
            $this->mailer->SMTPAutoTLS = $SMTPAuth;

            if ($this->mailer->SMTPAuth) {
                /** @var string $username */
                $username = $config->get('service.mail.smtp.username');
                $this->mailer->Username = $username;

                /** @var string $password */
                $password = $config->get('service.mail.smtp.password');
                $this->mailer->Password = $password;

                /** @var string $encryption */
                $encryption = $config->get('service.mail.smtp.encryption') ?? 'tls';
                $this->mailer->SMTPSecure = $encryption;
            }

            if (! empty($fromName)) {
                $this->mailer->setFrom($fromAddress, $fromName);
            } else {
                $this->mailer->setFrom($fromAddress);
            }

            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->isHTML(true);
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $textBody ?? strip_tags($htmlBody);

            return $this->mailer->send();
        }

        return false;
    }

    // protected function sendViaSmtp(
    //     string $to,
    //     string $subject,
    //     string $body,
    //     string $fromAddress,
    //     string $fromName = ''
    // ): bool {
    //     $config = Lapis::configRegistry();

    //     $this->mailer->isSMTP();
    //     $this->mailer->Host = $config->get('service.mail.smtp.host');
    //     $this->mailer->Port = $config->get('service.mail.smtp.port', 587);
    //     $this->mailer->SMTPAuth = false;
    //     $this->mailer->SMTPAutoTLS = false;

    //     if ($config->get('service.mail.smtp.authenticate')) {
    //         $this->mailer->SMTPAuth = true;
    //         $this->mailer->SMTPAutoTLS = true;
    //         $this->mailer->Username = $config->get('service.mail.smtp.user');
    //         $this->mailer->Password = $config->get('service.mail.smtp.pass');
    //         $this->mailer->SMTPSecure = $config->get('service.mail.smtp.use_tls', 'tls');
    //     }

    //     if (empty($fromName)) {
    //         $this->mailer->setFrom($fromAddress);
    //     } else {
    //         $this->mailer->setFrom($fromAddress, $fromName);
    //     }

    //     $this->mailer->addAddress($to);
    //     $this->mailer->Subject = $subject;
    //     $this->mailer->isHTML(true);
    //     $this->mailer->Body = $body;

    //     return $this->mailer->send();
    // }
}
