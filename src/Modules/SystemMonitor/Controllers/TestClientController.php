<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\SystemMonitor\Controllers;

use BitSynama\Lapis\Framework\Controllers\AbstractController;
use BitSynama\Lapis\Framework\DTO\ActionResponse;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Services\GeoIpService;
use Psr\Http\Message\ServerRequestInterface;
use function fopen;

final class TestClientController extends AbstractController
{
    public function get(ServerRequestInterface $request): ActionResponse
    {
        // dd($request);
        // $geoIp = GeoIpService::lookup($request->getAttribute('client-ip'));

        $uri = 'http://localhost:8002';
        $queryParams = [
            'product_id' => 123,
        ];
        $headers = [
            'Content-Type' => 'text/plain',
        ];
        $response = Lapis::httpClientUtility()->get($uri, $queryParams, $headers);

        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            data: [
                'response' => $response->getBody()
                    ->getContents(),
            ],
            template: 'admin.default'
        );
    }

    public function postJson(): ActionResponse
    {
        $uri = 'http://localhost:8002';
        $data = [
            'title' => 'test json post',
            'description' => 'some article on json',
        ];
        $format = 'json';
        $queryParams = [
            'article_id' => 123,
        ];
        $response = Lapis::httpClientUtility()->post($uri, $data, $format, $queryParams);

        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            data: [
                'response' => $response->getBody()
                    ->getContents(),
            ],
            template: 'admin.default'
        );
    }

    public function postForm(): ActionResponse
    {
        $uri = 'http://localhost:8002';
        $data = [
            'title' => 'test form post',
            'description' => 'some article on form',
        ];
        $format = 'form';
        $queryParams = [
            'article_id' => 123,
        ];
        $response = Lapis::httpClientUtility()->post($uri, $data, $format, $queryParams);

        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            data: [
                'response' => $response->getBody()
                    ->getContents(),
            ],
            template: 'admin.default'
        );
    }

    public function postXml(): ActionResponse
    {
        $uri = 'http://localhost:8002';
        $data = '<parent><child>Kiddy</child></parent>';
        $format = 'xml';
        $queryParams = [
            'xml_id' => 123,
        ];
        $response = Lapis::httpClientUtility()->post($uri, $data, $format, $queryParams);

        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            data: [
                'response' => $response->getBody()
                    ->getContents(),
            ],
            template: 'admin.default'
        );
    }

    public function postMultipart(): ActionResponse
    {
        /** @var string $projectDir */
        $projectDir = Lapis::varRegistry()->get('project_dir');

        $uri = 'http://localhost:8002';
        $data = [
            [
                'name' => 'avatar',                    // the form field name
                'contents' => fopen($projectDir . '/public/assets/default/images/logo.png', 'r'),
                'filename' => 'logo.png',                    // what the remote server sees as filename
            ],
            [
                'name' => 'user_id',
                'contents' => '42',
            ],
            [
                'name' => 'description',
                'contents' => 'Profile picture upload',
            ],
        ];
        $format = 'multipart';
        $queryParams = [
            'genre_id' => 123,
        ];
        $response = Lapis::httpClientUtility()->post($uri, $data, $format, $queryParams);

        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            data: [
                'response' => $response->getBody()
                    ->getContents(),
            ],
            template: 'admin.default'
        );
    }

    public function prePostMiddleware(ServerRequestInterface $request): ActionResponse
    {
        $vars = $request->getAttribute('dummyVars', []);

        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            data: [
                'vars' => $vars,
            ],
            template: 'admin.default'
        );
    }

    public function receivePostData(ServerRequestInterface $request): ActionResponse
    {
        /** @var string $method */
        $method = $request->getMethod();

        /** @var array<string, mixed> $body */
        $body = $request->getParsedBody() ?: [];

        /** @var array<string, mixed> $queryParams */
        $queryParams = $request->getQueryParams();

        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            data: [
                'method' => $method,
                'body' => $body,
                'queryParams' => $queryParams,
            ],
            template: 'admin.default'
        );
    }

    public function receivePatchData(ServerRequestInterface $request): ActionResponse
    {
        /** @var string $method */
        $method = $request->getMethod();

        /** @var array<string, mixed> $body */
        $body = $request->getParsedBody() ?: [];

        /** @var array<string, mixed> $queryParams */
        $queryParams = $request->getQueryParams();

        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            data: [
                'method' => $method,
                'body' => $body,
                'queryParams' => $queryParams,
            ],
            template: 'admin.default'
        );
    }

    public function receivePutData(ServerRequestInterface $request): ActionResponse
    {
        /** @var string $method */
        $method = $request->getMethod();

        /** @var array<string, mixed> $body */
        $body = $request->getParsedBody() ?: [];

        /** @var array<string, mixed> $queryParams */
        $queryParams = $request->getQueryParams();

        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            data: [
                'method' => $method,
                'body' => $body,
                'queryParams' => $queryParams,
            ],
            template: 'admin.default'
        );
    }

    public function receiveDelete(ServerRequestInterface $request): ActionResponse
    {
        /** @var array<string, mixed> $queryParams */
        $queryParams = $request->getQueryParams();

        return new ActionResponse(status: ActionResponse::SUCCESS, data: $queryParams, template: 'admin.default');
    }
}
