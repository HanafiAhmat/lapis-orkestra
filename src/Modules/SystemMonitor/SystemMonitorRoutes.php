<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\SystemMonitor;

use BitSynama\Lapis\Framework\Contracts\ModuleRoutesInterface;
use BitSynama\Lapis\Framework\DTO\ActionResponse;
// use BitSynama\Lapis\Modules\SystemMonitor\Controllers\ThrottleDebugController;
use BitSynama\Lapis\Framework\DTO\ResponseFilterDefinition;
use BitSynama\Lapis\Framework\Registries\RouteRegistry;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\SystemMonitor\Controllers\AuditLogController;
use BitSynama\Lapis\Modules\SystemMonitor\Controllers\HealthCheckController;
use BitSynama\Lapis\Modules\SystemMonitor\Controllers\RoutesController;
use BitSynama\Lapis\Modules\SystemMonitor\Controllers\SummaryController;
use BitSynama\Lapis\Modules\SystemMonitor\Controllers\TestClientController;
use BitSynama\Lapis\Modules\SystemMonitor\Controllers\TestMailerController;
use Psr\Http\Message\ServerRequestInterface;

class SystemMonitorRoutes implements ModuleRoutesInterface
{
    public static function register(): void
    {
        /** @var string $adminPrefix */
        $adminPrefix = Lapis::configRegistry()->get('app.routes.admin_prefix');
        $route = Lapis::routeRegistry();

        $route->addGroup(prefix: $adminPrefix, callback: function (RouteRegistry $route) {
            $route->addGroup(prefix: '/monitor', callback: function (RouteRegistry $route) {
                $index = fn (ServerRequestInterface $req): ActionResponse =>
                    new ActionResponse(
                        status: ActionResponse::SUCCESS,
                        data: [
                            'record' => 'BitSynama Lapis Core Application - System Monitor',
                        ],
                        message: 'BitSynama Lapis Core Application - System Monitor',
                        template: 'admin.default'
                    );
                $route->add('GET', '', $index);

                $route->addGroup(prefix: '/health-check', callback: function (RouteRegistry $route) {
                    $route->add(
                        'GET',
                        '',
                        [HealthCheckController::class, 'index'],
                        // [
                        //     ['security', 'rate_limiter'],
                        //     ['staffuser', 'role_factory', [['superuser', 'manager']]],
                        // ],
                    );

                    // $route->get('/full', [HealthCheckController::class, 'full'])
                    //     ->middleware(
                    //         MiddlewareQueueBuilder::from([
                    //             ['security', 'rate_limiter'],
                    //             ['staffuser', 'role_factory', [['superuser', 'manager']]],
                    //         ])
                    //     );
                });

                $route->add('GET', '/routes', RoutesController::class);

                // $route->addGroup(prefix: '/throttle', callback: function(RouteRegistry $route) {
                //     $route->get('/status', [ThrottleDebugController::class, 'check'])
                //         ->middleware(
                //             MiddlewareQueueBuilder::from([
                //                 ['security', 'ip_access'],
                //                 ['staffuser', 'role_factory', [['superuser']]],
                //             ])
                //         );
                //     $route->delete('/clear', [ThrottleDebugController::class, 'clear'])
                //         ->middleware(
                //             MiddlewareQueueBuilder::from([
                //                 ['security', 'ip_access'],
                //                 ['security', 'csrf'],
                //                 ['staffuser', 'role_factory', [['superuser']]],
                //             ])
                //         );
                // });

                $route->add(
                    'GET',
                    '/summary/psr',
                    [SummaryController::class, 'psr'],
                    [] // no extra middleware for now
                );

                $route->add('GET', '/audit-logs/recent', [AuditLogController::class, 'recent']);

                $route->addGroup(prefix: '/test', callback: function (RouteRegistry $route) {
                    $route->add('GET', '/mailer', TestMailerController::class);
                    $route->add('GET', '/get', [TestClientController::class, 'get']);
                    $route->add('GET', '/post-json', [TestClientController::class, 'postJson']);
                    $route->add('GET', '/post-form', [TestClientController::class, 'postForm']);
                    $route->add('GET', '/post-xml', [TestClientController::class, 'postXml']);
                    $route->add('GET', '/post-multipart', [TestClientController::class, 'postMultipart']);
                    $route->add(
                        'GET',
                        '/pre-post-middleware',
                        [TestClientController::class, 'prePostMiddleware'],
                        [],
                        [
                            new ResponseFilterDefinition('framework.add_header', [
                                'headerName' => 'X-Test-Middleware',
                                'headerValue' => 'post-action',
                            ]),
                        ]
                    );
                    $route->add('POST', '/receive-post-data', [TestClientController::class, 'receivePostData']);
                    $route->add('PATCH', '/receive-patch-data', [TestClientController::class, 'receivePatchData']);
                    $route->add('PUT', '/receive-put-data', [TestClientController::class, 'receivePutData']);
                    $route->add('DELETE', '/receive-delete', [TestClientController::class, 'receiveDelete']);
                });
            });
        });
    }
}
