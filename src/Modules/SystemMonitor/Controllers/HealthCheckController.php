<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\SystemMonitor\Controllers;

use BitSynama\Lapis\Framework\Controllers\AbstractController;
use BitSynama\Lapis\Framework\DTO\ActionResponse;
use BitSynama\Lapis\Lapis;
use Carbon\Carbon;
use DirectoryIterator;
use PDOException;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;
use function date;
use function date_default_timezone_get;
use function disk_free_space;
use function disk_total_space;
use function file_exists;
use function get_loaded_extensions;
use function getcwd;
use function getenv;
use function is_dir;
use function is_writable;
use function memory_get_usage;
use function realpath;
use function round;
use function str_contains;
use const PHP_VERSION;

final class HealthCheckController extends AbstractController
{
    public function index(ServerRequestInterface $request): ActionResponse
    {
        $status = $this->getBasicHealthInfo();

        $testObject = new stdClass();
        $testObject->before_execute = 'I am single';

        if (Lapis::interactorRegistry()->has('coredummy.dummytalkie')) {
            $dummyTalkie = Lapis::interactorRegistry()->get('coredummy.dummytalkie');
            $status['dummy_talkie'] = $dummyTalkie::execute($testObject);
        }

        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            data: $status,
            message: 'API health status',
            template: 'admin.default'
        );
    }

    public function full(): void
    {
        // if (! $this->isAuthorized('full')) {
        //     return;
        // }

        // $status = $this->getBasicHealthInfo();

        // // Cache directory check
        // $cacheDir = Loader::cache()->getCacheDir();
        // $status['cache_path'] = $cacheDir;
        // $status['cache_writable'] = is_writable($cacheDir);

        // // Disk usage
        // $status['disk_free'] = round(disk_free_space('/') / 1073741824, 2) . ' GB';
        // $status['disk_total'] = round(disk_total_space('/') / 1073741824, 2) . ' GB';

        // // Modules & Migrations (if available)
        // $status['modules'] = $this->getModuleStatuses();

        // MultiResponse::success('Full API health status', $status);
    }

    public function testEmail(): void
    {
        // if (! $this->isAuthorized('test-email')) {
        //     return;
        // }

        // $to = Loader::config()->get('email.testing_to', 'developer@example.com');
        // $subject = 'Test Email from SystemMonitor';

        // $sent = MailService::send(
        //     to: $to,
        //     subject: $subject,
        //     htmlBody: EmailComposer::renderHtml('SystemMonitor.emails.test-email.html'),
        //     textBody: EmailComposer::renderText('SystemMonitor.emails.test-email.text')
        // );

        // if ($sent) {
        //     MultiResponse::success('Testing email sent', []);
        // } else {
        //     MultiResponse::fail('Testing email unable to be sent');
        // }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getBasicHealthInfo(): array
    {
        $status = [
            'timestamp' => Carbon::now()->toIso8601String(),
            'version' => '1.0.0',
            'status' => 'ok',
            'database' => 'pending',
            'environment' => getenv('APP_ENV'),
            'debug' => 'disabled',
            'timezone' => date_default_timezone_get(),
            'memory_usage' => round(memory_get_usage(true) / 1048576, 2) . ' MB',
            'php_version' => PHP_VERSION,
            'loaded_extensions' => get_loaded_extensions(),
        ];

        // try {
        //     $db = Loader::db();
        //     $db->query('SELECT 1');
        //     $status['database'] = 'connected';
        // } catch (PDOException $e) {
        //     $status['database'] = 'unreachable';
        //     $status['db_error'] = $e->getMessage();
        //     $status['status'] = 'degraded';
        // }

        return $status;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function getModuleStatuses(): array
    {
        $repoDir = realpath(__DIR__ . '/../../../../');
        $projectDir = realpath(getcwd() . '/../');

        $repoModules = [];
        $repoModulesFile = $repoDir . '/src/Configs/modules.php';
        if (file_exists($repoModulesFile)) {
            $repoModules = require $repoModulesFile;
        }

        $result = [];
        foreach ($repoModules as $module => $enabled) {
            $result[$module] = [
                'enabled' => $enabled,
                'last_migration' => $this->getLastMigrationTimestamp($repoDir . "/src/Modules/{$module}/Migrations"),
            ];
        }

        $projectModules = [];
        $projectModulesFile = $projectDir . '/app/Configs/modules.php';
        if (file_exists($projectModulesFile)) {
            $projectModules = require $projectModulesFile;
        }

        foreach ($projectModules as $module => $enabled) {
            $result[$module] = [
                'enabled' => $enabled,
                'last_migration' => $this->getLastMigrationTimestamp($projectDir . "/app/Modules/{$module}/Migrations"),
            ];
        }

        return $result;
    }

    protected function getLastMigrationTimestamp(string $fullPath): string|null
    {
        if (! is_dir($fullPath)) {
            return null;
        }

        $latest = null;
        foreach (new DirectoryIterator($fullPath) as $fileInfo) {
            if ($fileInfo->isFile() && str_contains($fileInfo->getFilename(), 'Version')) {
                $time = $fileInfo->getMTime();
                if ($latest === null || $time > $latest) {
                    $latest = $time;
                }
            }
        }

        return $latest ? date('c', $latest) : null;
    }

    // protected function resolveAccessVerifier(): ActionAccessVerifierInterface
    // {
    //     if (ServiceRegistry::has('stafuser', 'permission')) {
    //         $accessVerifier = ServiceRegistry::get('stafuser', 'permission')::getAccessVerifier();
    //         return $accessVerifier instanceof ActionAccessVerifierInterface ? $accessVerifier : parent::resolveAccessVerifier();
    //     }

    //     return parent::resolveAccessVerifier();
    // }
}
