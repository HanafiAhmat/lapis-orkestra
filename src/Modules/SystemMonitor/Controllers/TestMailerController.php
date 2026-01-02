<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\SystemMonitor\Controllers;

use BitSynama\Lapis\Framework\Controllers\AbstractController;
use BitSynama\Lapis\Framework\DTO\ActionResponse;
use BitSynama\Lapis\Framework\Foundation\EmailComposer;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Services\MailerService;
use Psr\Http\Message\ServerRequestInterface;

final class TestMailerController extends AbstractController
{
    public function __invoke(ServerRequestInterface $request): ActionResponse
    {
        // if (! $this->isAuthorized('test-email')) {
        //     return;
        // }

        /** @var string $to */
        $to = Lapis::configRegistry()->get('services.mail.testing_to') ?? 'developer@example.com';
        $subject = 'Test Email from System Monitor';

        $sent = MailerService::send(
            to: $to,
            subject: $subject,
            htmlBody: EmailComposer::renderHtml('system_monitor.test-email.html'),
            textBody: EmailComposer::renderText('system_monitor.test-email.text')
        );

        if ($sent) {
            return new ActionResponse(
                status: ActionResponse::SUCCESS,
                message: 'Test Mailer Success',
                template: 'admin.default'
            );
        }
        return new ActionResponse(
            status: ActionResponse::FAIL,
            message: 'Test Mailer Failed',
            template: 'admin.default'
        );
    }
}
