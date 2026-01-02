<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\SystemMonitor\Controllers;

use BitSynama\Lapis\Framework\Controllers\AbstractController;
use BitSynama\Lapis\Framework\DTO\ActionResponse;
use BitSynama\Lapis\Framework\Foundation\Constants;
use BitSynama\Lapis\Lapis;
use function sort;

class RoutesController extends AbstractController
{
    public function __invoke(): ActionResponse
    {
        if (! $this->isAuthorized('index')) {
            return new ActionResponse(
                status: ActionResponse::FAIL,
                message: 'Not authorised',
                statusCode: Constants::STATUS_CODE_UNAUTHORIZED,
                template: 'admin.default'
            );
        }

        $data = [];
        foreach (Lapis::routeRegistry()->all() as $route) {
            if ($route->method === 'HEAD') {
                continue;
            }
            $data[] = $route->path;
        }
        sort($data);

        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            data: [
                'record' => $data,
            ],
            message: 'Available Routes',
            template: 'admin.default'
        );
    }
}
