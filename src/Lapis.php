<?php declare(strict_types=1);

namespace BitSynama\Lapis;

use App\AppRoutes;
use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Framework\Contracts\EmitterInterface;
use BitSynama\Lapis\Framework\DTO\ActionResponse;
use BitSynama\Lapis\Framework\DTO\Configs\Utilities\DatabaseConnectionConfig;
use BitSynama\Lapis\Framework\Emitters\ConsoleEmitter;
use BitSynama\Lapis\Framework\Emitters\HttpEmitter;
use BitSynama\Lapis\Framework\Foundation\Constants;
use BitSynama\Lapis\Framework\Foundation\DbReadiness;
use BitSynama\Lapis\Framework\Foundation\Runtime;
use BitSynama\Lapis\Framework\Handlers\FailsafeErrorHandler;
use BitSynama\Lapis\Framework\Kernel\Dispatcher;
use BitSynama\Lapis\Framework\Loaders\ModuleLoader;
use BitSynama\Lapis\Framework\Registries\AdminMenuRegistry;
use BitSynama\Lapis\Framework\Registries\AdminWidgetRegistry;
use BitSynama\Lapis\Framework\Registries\ConfigRegistry;
use BitSynama\Lapis\Framework\Registries\InteractorRegistry;
use BitSynama\Lapis\Framework\Registries\MiddlewareRegistry;
use BitSynama\Lapis\Framework\Registries\PublicMenuRegistry;
use BitSynama\Lapis\Framework\Registries\PublicWidgetRegistry;
use BitSynama\Lapis\Framework\Registries\ResponseFilterRegistry;
use BitSynama\Lapis\Framework\Registries\RouteRegistry;
use BitSynama\Lapis\Framework\Registries\VarRegistry;
use BitSynama\Lapis\Framework\ResponseFilters\AddHeaderResponseFilter;
use BitSynama\Lapis\Framework\Responses\MultiResponse;
use BitSynama\Lapis\Framework\Routes\AdminRoutes;
use BitSynama\Lapis\Framework\Routes\PublicRoutes;
use BitSynama\Lapis\Modules\User\Registries\UserTypeRegistry;
use BitSynama\Lapis\Utilities\CacheUtility;
use BitSynama\Lapis\Utilities\CookieUtility;
use BitSynama\Lapis\Utilities\HttpClientUtility;
use BitSynama\Lapis\Utilities\LoggerUtility;
use BitSynama\Lapis\Utilities\RequestUtility;
use BitSynama\Lapis\Utilities\RouterUtility;
use BitSynama\Lapis\Utilities\SessionUtility;
use BitSynama\Lapis\Utilities\ViewUtility;
use Dotenv\Dotenv;
use Exception;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Relations\Relation;
use LogicException;
use RuntimeException;
use function array_pop;
use function class_exists;
use function count;
use function date_default_timezone_set;
use function dirname;
use function explode;
use function file_exists;
use function function_exists;
use function implode;
use function is_array;
use function is_dir;
use function is_file;
use function is_string;
use function mb_internal_encoding;
use function method_exists;
use function mkdir;
use function setlocale;
use function touch;
use const DIRECTORY_SEPARATOR;
use const LC_ALL;

#[ImplementsPSR(
    'Basic Coding Standard',
    psr: 'PSR-1',
    usage: 'Uses PSR-1 basic coding standard through Easy Coding Standard checker and fixer',
    link: 'https://www.php-fig.org/psr/psr-1/'
)]
#[ImplementsPSR(
    'Auto-Loading',
    psr: 'PSR-4',
    usage: 'Uses PSR-4 auto-loading defined in composer.json',
    link: 'https://www.php-fig.org/psr/psr-4/'
)]
#[ImplementsPSR(
    'Extended Coding Style',
    psr: 'PSR-12',
    usage: 'Uses PSR-12 extended coding style through Easy Coding Standard checker and fixer',
    link: 'https://www.php-fig.org/psr/psr-12/'
)]
final class Lapis
{
    private static bool $booted = false;

    private static AdminMenuRegistry|null $adminMenuRegistry = null;

    private static AdminWidgetRegistry|null $adminWidgetRegistry = null;

    private static ConfigRegistry|null $configRegistry = null;

    private static InteractorRegistry|null $interactorRegistry = null;

    private static MiddlewareRegistry|null $middlewareRegistry = null;

    private static PublicMenuRegistry|null $publicMenuRegistry = null;

    private static PublicWidgetRegistry|null $publicWidgetRegistry = null;

    private static ResponseFilterRegistry|null $responseFilterRegistry = null;

    private static RouteRegistry|null $routeRegistry = null;

    private static UserTypeRegistry|null $userTypeRegistry = null;

    private static VarRegistry|null $varRegistry = null;

    private static CacheUtility|null $cacheUtility = null;

    private static CookieUtility|null $cookieUtility = null;

    private static HttpClientUtility|null $httpClientUtility = null;

    private static LoggerUtility|null $loggerUtility = null;

    private static RequestUtility|null $requestUtility = null;

    private static RouterUtility|null $routerUtility = null;

    private static SessionUtility|null $sessionUtility = null;

    private static ViewUtility|null $viewUtility = null;

    private static ModuleLoader|null $moduleLoader = null;

    private static MultiResponse|null $multiResponse = null;

    private static EmitterInterface|null $emitter = null;

    private static string $repoDir;

    private static string $projectDir;

    private static string $tmpDir;

    /**
     * Bootstrap the application, handle the PSR-7 request, and emit the response.
     */
    public static function run(): void
    {
        self::boot();

        // START PIPELINE
        $dispatcher = new Dispatcher();
        $response = $dispatcher->dispatch();
        self::emitter()->emit($response);
        // END PIPELINE
    }

    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        if (function_exists('mb_internal_encoding')) {
            mb_internal_encoding('UTF-8');
        }

        $ds = DIRECTORY_SEPARATOR;
        self::$repoDir = dirname(__FILE__, 2);
        self::$projectDir = self::senseProjectDir();
        self::$tmpDir = self::$projectDir . $ds . 'tmp';

        if (file_exists(self::$projectDir . $ds . '.env')) {
            $dotenv = Dotenv::createImmutable(self::$projectDir, ['.env-local', '.env']);
            $dotenv->load();
        }

        self::setupEmitter();
        self::setupVarRegistry();
        self::setupConfigRegistry();
        self::setupLoggerUtility();

        $failsafe = new FailsafeErrorHandler();
        $failsafe->register();

        self::setupMultiResponse();

        self::setupModules();

        self::setupCacheUtility();
        self::setupCookieUtility();
        self::setupRequestUtility();
        self::setupHttpClientUtility();
        self::setupSessionUtility();
        self::setupRouterUtility();
        self::setupViewUtility();

        self::setupDbConnection();

        if (! Runtime::isCli()) {
            if (self::varRegistry()->getOrSkip('db_setup_required')) {
                /** @var string $dbSetupError */
                $dbSetupError = self::varRegistry()->getOrSkip('db_setup_error');
                $msg = [
                    'error' => 'Database is not prepared.',
                    'hint' => 'Run migrations (and seeders for development)',
                    'commands' => ['php bin/console migration:migrate', 'php bin/console seed:run --class=...'],
                    'details' => self::configRegistry()->get('app.debug')
                        ? $dbSetupError
                        : null,
                ];

                $isAdminSite = self::requestUtility()->isAdminSite();
                $templatePrefix = $isAdminSite ? 'admin.' : 'public.';
                $template = $templatePrefix . 'errors.setup-required';

                $dto = new ActionResponse(
                    status: ActionResponse::FAIL,
                    data: $msg,
                    message: $msg['error'],
                    statusCode: Constants::STATUS_CODE_SERVICE_UNAVAILABLE,
                    template: $template
                );

                $response = self::multiResponse()->handle($dto);
                self::emitter()->emit($response);
                exit;
            }

            self::setupUIs();
            self::setupRoutes();
        }

        self::$booted = true;
    }

    // Registry setters (called in boot)
    public static function setAdminMenuRegistry(AdminMenuRegistry $registry): void
    {
        self::$adminMenuRegistry = $registry;
    }

    public static function setAdminWidgetRegistry(AdminWidgetRegistry $registry): void
    {
        self::$adminWidgetRegistry = $registry;
    }

    public static function setConfigRegistry(ConfigRegistry $registry): void
    {
        self::$configRegistry = $registry;
    }

    public static function setInteractorRegistry(InteractorRegistry $registry): void
    {
        self::$interactorRegistry = $registry;
    }

    public static function setMiddlewareRegistry(MiddlewareRegistry $registry): void
    {
        self::$middlewareRegistry = $registry;
    }

    public static function setPublicMenuRegistry(PublicMenuRegistry $registry): void
    {
        self::$publicMenuRegistry = $registry;
    }

    public static function setPublicWidgetRegistry(PublicWidgetRegistry $registry): void
    {
        self::$publicWidgetRegistry = $registry;
    }

    public static function setResponseFilterRegistry(ResponseFilterRegistry $registry): void
    {
        self::$responseFilterRegistry = $registry;
    }

    public static function setRouteRegistry(RouteRegistry $registry): void
    {
        self::$routeRegistry = $registry;
    }

    public static function setUserTypeRegistry(UserTypeRegistry $registry): void
    {
        self::$userTypeRegistry = $registry;
    }

    public static function setVarRegistry(VarRegistry $registry): void
    {
        self::$varRegistry = $registry;
    }
    // End Registry setters

    // Utility setters
    public static function setCacheUtility(CacheUtility $utility): void
    {
        self::$cacheUtility = $utility;
    }

    public static function setCookieUtility(CookieUtility $utility): void
    {
        self::$cookieUtility = $utility;
    }

    public static function setHttpClientUtility(HttpClientUtility $utility): void
    {
        self::$httpClientUtility = $utility;
    }

    public static function setLoggerUtility(LoggerUtility $utility): void
    {
        self::$loggerUtility = $utility;
    }

    public static function setRequestUtility(RequestUtility $utility): void
    {
        self::$requestUtility = $utility;
    }

    public static function setRouterUtility(RouterUtility $utility): void
    {
        self::$routerUtility = $utility;
    }

    public static function setSessionUtility(SessionUtility $utility): void
    {
        self::$sessionUtility = $utility;
    }

    public static function setViewUtility(ViewUtility $utility): void
    {
        self::$viewUtility = $utility;
    }
    // End Utility setters

    // Pipeline mechanism setters
    public static function setEmitter(EmitterInterface $emitter): void
    {
        self::$emitter = $emitter;
    }

    public static function setMultiResponse(MultiResponse $multiResponse): void
    {
        self::$multiResponse = $multiResponse;
    }
    // End Pipeline mechanism setters

    // Registry Getters
    public static function adminMenuRegistry(): AdminMenuRegistry
    {
        return self::$adminMenuRegistry
            ?? throw new LogicException('AdminMenuRegistry not initialized');
    }

    public static function adminWidgetRegistry(): AdminWidgetRegistry
    {
        return self::$adminWidgetRegistry
            ?? throw new LogicException('AdminWidgetRegistry not initialized');
    }

    public static function configRegistry(): ConfigRegistry
    {
        return self::$configRegistry
            ?? throw new LogicException('Config not initialized');
    }

    public static function interactorRegistry(): InteractorRegistry
    {
        return self::$interactorRegistry
            ?? throw new LogicException('InteractorRegistry not initialized');
    }

    public static function middlewareRegistry(): MiddlewareRegistry
    {
        return self::$middlewareRegistry
            ?? throw new LogicException('MiddlewareRegistry not initialized');
    }

    public static function publicMenuRegistry(): PublicMenuRegistry
    {
        return self::$publicMenuRegistry
            ?? throw new LogicException('PublicMenuRegistry not initialized');
    }

    public static function publicWidgetRegistry(): PublicWidgetRegistry
    {
        return self::$publicWidgetRegistry
            ?? throw new LogicException('PublicWidgetRegistry not initialized');
    }

    public static function responseFilterRegistry(): ResponseFilterRegistry
    {
        return self::$responseFilterRegistry
            ?? throw new LogicException('ResponseFilterRegistry not initialized');
    }

    public static function routeRegistry(): RouteRegistry
    {
        return self::$routeRegistry
            ?? throw new LogicException('RouteRegistry not initialized');
    }

    public static function userTypeRegistry(): UserTypeRegistry
    {
        return self::$userTypeRegistry
            ?? throw new LogicException('UserTypeRegistry not initialized');
    }

    public static function varRegistry(): VarRegistry
    {
        return self::$varRegistry
            ?? throw new LogicException('VarRegistry not initialized');
    }
    // End Registry Getters

    // Utility Getters
    public static function cacheUtility(): CacheUtility
    {
        return self::$cacheUtility
            ?? throw new LogicException('Cache not initialized');
    }

    public static function cookieUtility(): CookieUtility
    {
        return self::$cookieUtility
            ?? throw new LogicException('Cookie not initialized');
    }

    public static function httpClientUtility(): HttpClientUtility
    {
        return self::$httpClientUtility
            ?? throw new LogicException('Http Client not initialized');
    }

    public static function loggerUtility(): LoggerUtility
    {
        return self::$loggerUtility
            ?? throw new LogicException('Logger not initialized');
    }

    public static function requestUtility(): RequestUtility
    {
        return self::$requestUtility
            ?? throw new LogicException('RequestUtility not initialized');
    }

    public static function routerUtility(): RouterUtility
    {
        return self::$routerUtility
            ?? throw new LogicException('RouterUtility not initialized');
    }

    public static function sessionUtility(): SessionUtility
    {
        return self::$sessionUtility
            ?? throw new LogicException('SessionUtility not initialized');
    }

    public static function viewUtility(): ViewUtility
    {
        return self::$viewUtility
            ?? throw new LogicException('ViewUtility not initialized');
    }
    // End Utility Getters

    // Pipeline Mechanism Getters
    public static function emitter(): EmitterInterface
    {
        return self::$emitter
            ?? throw new LogicException('Response Emitter not initialized');
    }

    public static function multiResponse(): MultiResponse
    {
        return self::$multiResponse
            ?? throw new LogicException('MultiResponse not initialized');
    }
    // End Pipeline Mechanism Getters

    // Setup Registries
    private static function setupAdminMenuRegistry(): void
    {
        $registry = new AdminMenuRegistry();
        self::setAdminMenuRegistry($registry);
    }

    private static function setupAdminWidgetRegistry(): void
    {
        $registry = new AdminWidgetRegistry();
        self::setAdminWidgetRegistry($registry);
    }

    private static function setupConfigRegistry(): void
    {
        $configRegistry = new ConfigRegistry();
        $configRegistry->load();

        /** @var string $defaultTimezone */
        $defaultTimezone = $configRegistry->get('app.timezone') ?: 'UTC';
        date_default_timezone_set($defaultTimezone);
        if (function_exists('setlocale')) {
            /** @var string $defaultLocale */
            $defaultLocale = $configRegistry->get('app.locale') ?: 'en_SG.UTF-8';
            setlocale(LC_ALL, $defaultLocale);
        }

        self::setConfigRegistry($configRegistry);
    }

    private static function setupInteractorRegistry(): void
    {
        $registry = new InteractorRegistry();
        self::setInteractorRegistry($registry);
    }

    private static function setupMiddlewareRegistry(): void
    {
        $registry = new MiddlewareRegistry();
        self::setMiddlewareRegistry($registry);
    }

    private static function setupPublicMenuRegistry(): void
    {
        $registry = new PublicMenuRegistry();
        self::setPublicMenuRegistry($registry);
    }

    private static function setupPublicWidgetRegistry(): void
    {
        $registry = new PublicWidgetRegistry();
        self::setPublicWidgetRegistry($registry);
    }

    private static function setupResponseFilterRegistry(): void
    {
        $registry = new ResponseFilterRegistry();
        self::setResponseFilterRegistry($registry);
        // self::responseFilterRegistry()->set('framework.add_header', AddHeaderResponseFilter::class);
    }

    private static function setupRouteRegistry(): void
    {
        $route = new RouteRegistry();
        self::setRouteRegistry($route);
    }

    private static function setupUserTypeRegistry(): void
    {
        $userType = new UserTypeRegistry();
        self::setUserTypeRegistry($userType);
    }

    private static function setupVarRegistry(): void
    {
        $registry = new VarRegistry();
        self::setVarRegistry($registry);

        self::varRegistry()->set('repo_dir', self::$repoDir);
        self::varRegistry()->set('project_dir', self::$projectDir);
        self::varRegistry()->set('tmp_dir', self::$tmpDir);
        self::varRegistry()->set('user', null);
    }
    // End Setup Registries

    // Setup Utilities
    private static function setupCacheUtility(): void
    {
        $cacheUtility = new CacheUtility();
        self::setCacheUtility($cacheUtility);
    }

    private static function setupCookieUtility(): void
    {
        $cookieUtility = new CookieUtility();
        self::setCookieUtility($cookieUtility);
    }

    private static function setupHttpClientUtility(): void
    {
        $httpClientUtility = new HttpClientUtility();
        self::setHttpClientUtility($httpClientUtility);
    }

    private static function setupLoggerUtility(): void
    {
        // Set up PSR-3 logger
        $loggerUtility = new LoggerUtility();
        self::setLoggerUtility($loggerUtility);
    }

    private static function setupRequestUtility(): void
    {
        $requestUtility = new RequestUtility();
        self::setRequestUtility($requestUtility);
    }

    private static function setupRouterUtility(): void
    {
        $routerUtility = new RouterUtility();
        self::setRouterUtility($routerUtility);
    }

    private static function setupSessionUtility(): void
    {
        $sessionUtil = new SessionUtility();
        self::setSessionUtility($sessionUtil);
    }

    private static function setupViewUtility(): void
    {
        $viewUtility = new ViewUtility();
        self::setViewUtility($viewUtility);
    }
    // End Setup Utilities

    // Miscelleneous
    private static function senseProjectDir(): string
    {
        $ds = DIRECTORY_SEPARATOR;

        // Create an exception
        $ex = new Exception();

        // Call getTrace() function
        $trace = $ex->getTrace();

        // Index 0 would be the line that called boot function
        // Index 1 would be the boot function
        $finalCall = $trace[2]['file'] ?? 'unknown';

        $segments = explode("{$ds}bin{$ds}", $finalCall);
        if (count($segments) > 1) {
            return $segments[0];
        }

        $segments = explode("{$ds}public{$ds}", $finalCall);
        if (count($segments) > 1) {
            return $segments[0];
        }

        $segments = explode("{$ds}", $finalCall);
        array_pop($segments);

        return implode($ds, $segments);
    }

    private static function setupDbConnection(): void
    {
        $dbConfig = self::configRegistry()->get('database');
        if (! is_array($dbConfig)) {
            throw new RuntimeException('`$dbConfig` must be an array of database connection information');
        }

        $defaultConnection = $dbConfig['default'];
        if (! is_string($defaultConnection)) {
            throw new RuntimeException('`$defaultConnection` must be a string');
        }

        $connections = $dbConfig['connections'];
        if (! is_array($connections)) {
            throw new RuntimeException('`$connections` must be an array of database connection information');
        }

        $connInfo = $connections[$defaultConnection];
        if (! $connInfo instanceof DatabaseConnectionConfig) {
            throw new RuntimeException(
                '`$connInfo` must be an instance of `DatabaseConnectionConfig` data transfer object'
            );
        }
        if ($connInfo->driver === 'sqlite') {
            $defaultSqliteDir = self::$tmpDir . DIRECTORY_SEPARATOR . 'database';
            /** @var non-falsy-string $sqliteDir */
            $sqliteDir = $connInfo->sqlite_dir ?: $defaultSqliteDir;
            if (! is_dir($sqliteDir)) {
                if (! mkdir($sqliteDir, 0744, true)) {
                    throw new RuntimeException('Failed to create database file directory...');
                }
            }
            $connInfo->database = $sqliteDir . DIRECTORY_SEPARATOR . $connInfo->sqlite_name . $connInfo->sqlite_ext;
            if (! is_file($connInfo->database)) {
                if (! touch($connInfo->database)) {
                    throw new RuntimeException('Failed to create database sqlite file...');
                }
            }
            $connInfo->name = 'default';
        }
        $capsule = new Capsule();
        $capsule->addConnection($connInfo->toArray(), 'default');

        // (skip events if you don’t need them)
        // if (! empty($dbConfig['events'])) {
        //     $dispatcher = new \Illuminate\Events\Dispatcher(new \Illuminate\Container\Container);
        //     $capsule->setEventDispatcher($dispatcher);
        // }

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        self::varRegistry()->set('db', $capsule);

        self::prepareDatabaseIfNeeded();

        // For SQLite, enforce FK constraints
        if ($connInfo->driver === 'sqlite') {
            $capsule->getConnection()
                ->statement('PRAGMA foreign_keys = ON');
        }

        // For MySQL, ensure proper charset/collation if not set in config
        // $capsule->getConnection()->getPdo()->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

        // NEW: once Eloquent is alive, wire the morph map
        self::setupEloquentMorphMap();
    }

    private static function prepareDatabaseIfNeeded(): void
    {
        if (DbReadiness::isReady()) {
            return; // already migrated
        }

        self::varRegistry()->set('db_setup_required', true);
        return;
    }

    private static function setupEloquentMorphMap(): void
    {
        // Build alias => class map from everything registered so far
        $map = self::userTypeRegistry()->morphMap();

        if (! empty($map)) {
            Relation::morphMap($map);

            // // If available in your Illuminate version, enforce aliases only
            // if (method_exists(Relation::class, 'enforceMorphMap')) {
            Relation::enforceMorphMap($map);
            // }
        }
    }

    private static function setupModules(): void
    {
        // auto‐discover modules using modules.php
        self::$moduleLoader = new ModuleLoader();

        self::setupUserTypeRegistry();
        self::setupInteractorRegistry();
        self::setupMiddlewareRegistry();
        self::setupResponseFilterRegistry();

        if (self::$moduleLoader !== null) {
            self::$moduleLoader->registerHandlers();
        }
    }

    private static function setupMultiResponse(): void
    {
        $multiResponse = new MultiResponse();
        self::setMultiResponse($multiResponse);
    }

    private static function setupEmitter(): void
    {
        if (Runtime::isCli()) {
            $emitter = new ConsoleEmitter();
        } else {
            $emitter = new HttpEmitter();
        }

        self::setEmitter($emitter);
    }

    private static function setupRoutes(): void
    {
        self::setupRouteRegistry();

        if (self::$moduleLoader !== null) {
            self::$moduleLoader->registerRoutes();
        }

        AdminRoutes::register();
        PublicRoutes::register();

        if (class_exists(AppRoutes::class)) {
            AppRoutes::register();
        }
    }

    private static function setupUIs(): void
    {
        self::setupAdminMenuRegistry();
        self::setupAdminWidgetRegistry();
        self::setupPublicMenuRegistry();
        self::setupPublicWidgetRegistry();

        if (self::$moduleLoader !== null) {
            self::$moduleLoader->registerUIs();
        }
    }
}
