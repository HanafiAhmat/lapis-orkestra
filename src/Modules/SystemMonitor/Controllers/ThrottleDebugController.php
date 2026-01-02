<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\SystemMonitor\Controllers;

use BitSynama\Lapis\Framework\Controllers\AbstractController;
use BitSynama\Lapis\Framework\DTO\ActionResponse;
use BitSynama\Lapis\Framework\Foundation\Constants;
use BitSynama\Lapis\Lapis;
use function microtime;
use function round;

class ThrottleDebugController extends AbstractController
{
    // /**
    //  * Only available in non-production environments.
    //  */
    // public function check(): ActionResponse
    // {
    //     $config = Lapis::configRegistry();

    //     if ($config->get('app.env') === Constants::ENV_PRODUCTION) {
    //         return new ActionResponse(
    //             status:  ActionResponse::FAIL,
    //             message: 'Not available in production',
    //             statusCode: Constants::STATUS_CODE_NOT_FOUND
    //         );
    //     }

    //     $key = Lapis::requestUtility()->query['key'] ?? null;
    //     if (! $key) {
    //         return new ActionResponse(
    //             status:  ActionResponse::FAIL,
    //             message: 'Missing required query parameter: key',
    //             statusCode: Constants::STATUS_CODE_NOT_FOUND
    //         );
    //     }

    //     $meta = Lapis::cacheUtility()->retrieve($key, true);
    //     if (! $meta || ! isset($meta['data'])) {
    //         return new ActionResponse(
    //             status:  ActionResponse::FAIL,
    //             message: 'No throttling record found for key',
    //             statusCode: Constants::STATUS_CODE_NOT_FOUND
    //         );
    //     }

    //     $data = [
    //         'key' => $key,
    //         'attempts' => $meta['data'],
    //         'ttl_seconds' => round(($meta['time'] + $meta['expire']) - microtime(true), 2),
    //         'created_at' => $meta['time'],
    //         'expires_in' => $meta['expire'],
    //         'raw_meta' => $meta,
    //     ];

    //     return new ActionResponse(
    //         status:  ActionResponse::SUCCESS,
    //         data: $data,
    //         message: 'Throttle status',
    //     );
    // }

    // /**
    //  * Erase a specific throttle key (only in non-production).
    //  */
    // public function clear(): ActionResponse
    // {
    //     $config = Lapis::configRegistry();

    //     if ($config->get('app.env') === Constants::ENV_PRODUCTION) {
    //         return new ActionResponse(
    //             status:  ActionResponse::FAIL,
    //             message: 'Not available in production',
    //             statusCode: Constants::STATUS_CODE_FORBIDDEN
    //         );
    //     }

    //     $key = Lapis::requestUtility()->query['key'] ?? null;
    //     if (! $key) {
    //         return new ActionResponse(
    //             status:  ActionResponse::FAIL,
    //             message: 'Missing required query parameter: key',
    //             statusCode: Constants::STATUS_CODE_FORBIDDEN
    //         );
    //     }

    //     $success = Lapis::cacheUtility()->eraseKey($key);
    //     if ($success) {
    //         return new ActionResponse(
    //             status:  ActionResponse::SUCCESS,
    //             message: 'Throttle status',
    //         );
    //     } else {
    //         return new ActionResponse(
    //             status:  ActionResponse::FAIL,
    //             message: "Throttle key '{$key}' was not found or already expired",
    //             statusCode: Constants::STATUS_CODE_NOT_FOUND
    //         );
    //     }
    // }
}
