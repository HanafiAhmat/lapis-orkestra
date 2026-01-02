<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Controllers;

use BitSynama\Lapis\Framework\DTO\ActionResponse;

class AdminController extends AbstractController
{
    // public function index(): ActionResponse
    // {
    //     return new ActionResponse(
    //         status: ActionResponse::SUCCESS,
    //         data: [ 'success' => 'Authenticated' ],
    //         message: 'Authenticated',
    //     );
    // }

    public function index(): ActionResponse
    {
        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            message: 'Lapis Orkestra - Admin Panel',
            statusCode: 200,
            template: 'admin.home', // <- use dashboard template
            data: [
                'stats' => [
                    'posts' => 128,
                    'tags' => 34,
                    'categories' => 8,
                    'comments' => 562,
                ],
                'recentPosts' => [], // optionally fetch from entity if DB exists
            ]
        );
    }
}
