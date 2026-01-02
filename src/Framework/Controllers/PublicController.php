<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Controllers;

use BitSynama\Lapis\Framework\DTO\ActionResponse;

class PublicController extends AbstractController
{
    public function index(): ActionResponse
    {
        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            message: 'Lapis Orkestra',
            template: 'public.home',
            data: []
        );
    }
}
