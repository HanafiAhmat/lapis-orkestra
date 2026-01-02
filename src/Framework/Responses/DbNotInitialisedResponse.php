<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Responses;

use BitSynama\Lapis\Framework\DTO\ActionResponse;
use BitSynama\Lapis\Framework\Foundation\Constants;
use BitSynama\Lapis\Framework\Foundation\Runtime;
use BitSynama\Lapis\Lapis;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class DbNotInitialisedResponse
{
    public function __invoke(Throwable $e): ResponseInterface
    {
        $templatePrefix = Lapis::viewUtility()->getTemplatePrefix();

        $data = [
            'error' => 'No user types are ready.',
            'hint' => 'Contact system administrator for support.',
        ];

        if (Runtime::isDev()) {
            $data['hint'] = 'Run database migrations and seeders for at least one user type.';
            $data['commands'] = ['php bin/console migration:migrate'];
        }

        $dto = new ActionResponse(
            status: ActionResponse::FAIL,
            data: $data,
            message: 'Login temporarily unavailable',
            statusCode: Constants::STATUS_CODE_SERVICE_UNAVAILABLE,
            template: $templatePrefix . 'errors.setup-required'
        );

        return Lapis::multiResponse()->handle($dto);
    }
}
