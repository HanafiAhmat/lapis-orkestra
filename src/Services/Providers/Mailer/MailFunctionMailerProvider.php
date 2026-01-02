<?php declare(strict_types=1);

namespace BitSynama\Lapis\Services\Providers\Mailer;

use BitSynama\Lapis\Services\Contracts\MailerProviderInterface;
use BitSynama\Lapis\Services\ProviderInfo;
use function implode;
use function mail;
use function uniqid;

#[ProviderInfo(type: 'mailer', key: 'mail', description: 'Uses OS built-in mail program')]
class MailFunctionMailerProvider implements MailerProviderInterface
{
    public function send(
        string $to,
        string $subject,
        string $htmlBody,
        string|null $fromAddress = null,
        string|null $fromName = null,
        string|null $textBody = null
    ): bool {
        $from = ! empty($fromName) ? "{$fromName} <{$fromAddress}>" : $fromAddress;
        $headers = ['MIME-Version: 1.0'];

        if ($htmlBody && $textBody) {
            // Send as multipart/alternative
            $boundary = uniqid('mixed_');
            $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundary}\"";
            $headers[] = "From: {$from}";

            $body = "--{$boundary}\r\n"
                  . "Content-Type: text/plain; charset=UTF-8\r\n"
                  . "Content-Transfer-Encoding: 7bit\r\n\r\n"
                  . $textBody . "\r\n"
                  . "--{$boundary}\r\n"
                  . "Content-Type: text/html; charset=UTF-8\r\n"
                  . "Content-Transfer-Encoding: 7bit\r\n\r\n"
                  . $htmlBody . "\r\n"
                  . "--{$boundary}--";

        } elseif ($htmlBody) {
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
            $headers[] = "From: {$from}";
            $body = $htmlBody;
        } elseif ($textBody) {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
            $headers[] = "From: {$from}";
            $body = $textBody;
        } else {
            // Nothing to send
            return false;
        }

        return mail($to, $subject, $body, implode("\r\n", $headers));
    }
}
