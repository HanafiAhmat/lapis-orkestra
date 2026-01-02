<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Responses;

use BitSynama\Lapis\Framework\DTO\ActionResponse;
use BitSynama\Lapis\Framework\Foundation\Runtime;
use BitSynama\Lapis\Lapis;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Throwable;

class ErrorResponse
{
    public function __invoke(Throwable $e): ResponseInterface
    {
        $fe = FlattenException::createFromThrowable($e);

        $isAdminSite = Lapis::requestUtility()->isAdminSite();
        $templatePrefix = $isAdminSite ? 'admin.' : 'public.';

        $template = $templatePrefix . 'errors.' . $fe->getStatusCode();
        if (! Lapis::viewUtility()->templateExists($template)) {
            $template = $templatePrefix . 'errors.generic';
        }

        /** @var int $statusCode */
        $statusCode = $fe->getCode() > 100 && $fe->getCode() < 600 ? $fe->getCode() : $fe->getStatusCode();
        $data = [];
        if (Runtime::isDev()) {
            $data['fe'] = [
                // 'status_code' => $fe->getStatusCode(),
                // 'status_text' => $fe->getStatusText(),
                'status_code' => $statusCode,
                'status_text' => $fe->getMessage(),
                'class' => $fe->getClass(),
                'message' => $fe->getMessage(),
                'file' => $fe->getFile(),
                'line' => $fe->getLine(),
            ];
        }

        $dto = new ActionResponse(
            status: ActionResponse::ERROR,
            data: $data,
            message: $fe->getMessage(),
            statusCode: $statusCode,
            template: $template
        );

        return Lapis::multiResponse()->handle($dto);
    }
}
