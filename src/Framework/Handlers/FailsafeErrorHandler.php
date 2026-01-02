<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Handlers;

use BitSynama\Lapis\Framework\DTO\ActionResponse;
use BitSynama\Lapis\Framework\Foundation\Runtime;
use BitSynama\Lapis\Lapis;
use ErrorException;
use stdClass;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Throwable;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as WhoopsRun;
use function error_log;
use function error_reporting;
use function gmdate;
use function header;
use function http_response_code;
use function is_string;
use function json_encode;
use function random_bytes;
use function set_error_handler;
use function set_exception_handler;
use function sha1;
use function sprintf;
use function str_contains;
use function substr;
use const E_ALL;
use const JSON_UNESCAPED_SLASHES;

final class FailsafeErrorHandler
{
    private readonly bool $isDev;

    public function __construct()
    {
        $this->isDev = Runtime::isDev();
    }

    public function register(): void
    {
        set_exception_handler($this->handle(...));
        set_error_handler($this->handlePhpError(...));
    }

    public function handlePhpError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        $this->handle(new ErrorException($errstr, 0, $errno, $errfile, $errline));
        return true;
    }

    public function handle(Throwable $e): void
    {
        $this->render($e);
    }

    public function render(Throwable $e): void
    {
        $httpAccept = str_contains(
            $_SERVER['HTTP_ACCEPT'] && is_string($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '',
            'application/json'
        );
        $httpRequestedWith = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' ? true : false;
        $wantsJson = $httpAccept || $httpRequestedWith;

        $cid = $this->makeCorrelationId();
        // $this->basicLog($e, $cid);

        http_response_code(500);

        if ($wantsJson) {
            header('Content-Type: application/json');
            echo json_encode([
                'ok' => false,
                'error' => [
                    'code' => 'GENERIC.ERROR',
                    'http_status' => 500,
                    'message' => $this->isDev ? $e->getMessage() : 'An unexpected error occurred.',
                    'correlation_id' => $cid,
                    'timestamp' => gmdate('c'),
                    // dev-only debug crumbs (safe & short)
                    'details' => $this->isDev ? [
                        'exception' => $e::class,
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ] : new stdClass(),
                ],
            ], JSON_UNESCAPED_SLASHES);
            return;
        }

        header('Content-Type: text/html; charset=UTF-8');
        if ($this->isDev) {
            error_reporting(E_ALL);

            // Development: pretty Whoops pages + nice dumps
            $whoops = new WhoopsRun();
            $whoops->pushHandler(new PrettyPageHandler());
            $whoops->register();

            throw $e; // hand over to Whoops
        }
        Lapis::loggerUtility()->emergency($e->getMessage(), [
            'reference' => $cid,
            'exception' => $e,
        ]);

        // Minimal prod-safe page (your custom template can replace this)
        echo "<!doctype html><meta charset='utf-8'><title>Error</title>"
           . '<style>body{font-family:system-ui;margin:10vh 8vw;color:#222}</style>'
           . '<h1>Something went wrong</h1>'
           . '<p>Please try again later.</p>'
           . "<small>Ref: {$cid}</small>";

        // Production: catch & log, then render a friendly template
        // ErrorHandler::register();

        // $logger = $loggerUtility->getLogger();
        // $logger->error($e->getMessage(), [
        //     'exception' => $e,
        // ]);

        // $fe = FlattenException::createFromThrowable($e);
        // $template = 'public.errors.' . $fe->getStatusCode();
        // if (! Lapis::viewUtility()->templateExists($template)) {
        //     $template = 'public.errors.generic';
        // }
        // $data = [
        //     'fe' => [
        //         'status_code' => $fe->getStatusCode(),
        //         'status_text' => $fe->getStatusText(),
        //         'class' => $fe->getClass(),
        //         'message' => $fe->getMessage(),
        //         'file' => $fe->getFile(),
        //         'line' => $fe->getLine(),
        //     ],
        // ];

        // $dto = new ActionResponse(
        //     status: ActionResponse::ERROR,
        //     data: $data,
        //     message: $fe->getStatusText(),
        //     statusCode: $fe->getStatusCode(),
        //     template: $template
        // );
        // $response = Lapis::multiResponse()->handle($dto);
        // Lapis::responseEmitter()->emit($response);
    }

    // private function basicLog(Throwable $e, string $cid): void
    // {
    //     // Use PHP's error_log so we don't depend on Monolog yet
    //     $msg = sprintf(
    //         '[%s] [%s] %s at %s:%d (ref=%s)',
    //         gmdate('c'),
    //         $e::class,
    //         $e->getMessage(),
    //         $e->getFile(),
    //         $e->getLine(),
    //         $cid
    //     );
    //     error_log($msg);
    // }

    private function makeCorrelationId(): string
    {
        return 'req_' . gmdate('Ymd_His') . '_' . substr(sha1(random_bytes(8)), 0, 8);
    }
}
