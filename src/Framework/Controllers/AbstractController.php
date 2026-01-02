<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Controllers;

use BitSynama\Lapis\Framework\Checkers\AbstractChecker;
use BitSynama\Lapis\Framework\Contracts\ActionAccessVerifierInterface;
use BitSynama\Lapis\Framework\DTO\ActionResponse;
use BitSynama\Lapis\Framework\Foundation\Constants;
use BitSynama\Lapis\Framework\Persistences\AbstractEntity;
use BitSynama\Lapis\Framework\Verifiers\AllowAllVerifier;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\User\Entities\User;
use Illuminate\Database\Eloquent\Collection;
use Psr\Http\Message\ServerRequestInterface;
use function ceil;
use function class_exists;
use function in_array;
use function is_array;
use function is_numeric;
use function is_scalar;
use function is_string;
use function ltrim;
use function max;
use function method_exists;
use function property_exists;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function strtolower;
use function ucwords;

/**
 * The AbstractController class contains the core functionality of the Controller part.
 * It is responsible for handling basic RESTful API HTTP requests.
 *
 * @method ActionResponse list(ServerRequestInterface $request) List records with filters and pagination.
 * @method ActionResponse store(ServerRequestInterface $request) Store new entity record.
 * @method ActionResponse show(ServerRequestInterface $request, int|string $id) Retrieve an entity record.
 * @method ActionResponse update(ServerRequestInterface $request, int|string $id) Update an entity record.
 * @method ActionResponse destroy(ServerRequestInterface $request, int|string $id) Delete an entity record.
 *
 * These are additional endpoints created to handle form view for monolithic setup.
 * @method ActionResponse create(ServerRequestInterface $request) Show create new record form.
 * @method ActionResponse edit(ServerRequestInterface $request, int|string $id) Show edit existing record form.
 *
 * @link https://github.com/omniti-labs/jsend Guide on building API response.
 */
abstract class AbstractController
{
    /**
     * @var string Main entity class related to this controller.
     */
    protected string $entityClass = '';

    /**
     * @var string Related checker class for store operation.
     */
    protected string $storeCheckerClass = '';

    /**
     * @var string Related checker class for update operation.
     */
    protected string $updateCheckerClass = '';

    /**
     * @var string list template path.
     */
    protected string $listTemplate = 'public.list';

    /**
     * @var string show template path.
     */
    protected string $showTemplate = 'public.show';

    /**
     * @var string create form template path.
     */
    protected string $createTemplate = 'public.create';

    /**
     * @var string edit form template path.
     */
    protected string $editTemplate = 'public.edit';

    protected ActionAccessVerifierInterface $accessVerifier;

    protected bool $isAdminSite;

    protected string $templatePrefix;

    protected bool $isDebugMode;

    protected string $appEnv;

    public function __construct()
    {
        $this->accessVerifier = $this->resolveAccessVerifier();
        $this->isAdminSite = Lapis::requestUtility()->isAdminSite();
        $this->templatePrefix = $this->isAdminSite ? 'admin.' : 'public.';

        $appConfig = Lapis::configRegistry()->get('app');
        $appConfig = is_array($appConfig) ? $appConfig : [];

        $this->isDebugMode = (bool) ($appConfig['debug'] ?? false);
        $this->appEnv = (string) ($appConfig['env'] ?? 'production');
    }

    /**
     * List records with filters and pagination.
     */
    public function list(ServerRequestInterface $request): ActionResponse
    {
        $queryParams = $request->getQueryParams();

        $filter = $queryParams['filter'] ?? [];
        $filter = is_array($filter) ? $filter : [];

        $sort = $queryParams['sort'] ?? null;
        $sort = is_string($sort) ? $sort : null;

        $page = $queryParams['page'] ?? [];
        $page = is_array($page) ? $page : [];

        if ($this->entityClass === '') {
            $data = [
                'error' => 'Entity Class not defined',
            ];
            if ($this->isDebugMode || $this->appEnv === 'development') {
                $data['details'] = [
                    'file' => __FILE__,
                    'function' => __FUNCTION__,
                    'method' => __METHOD__,
                ];
            }

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: $data,
                message: $data['error'],
                statusCode: Constants::STATUS_CODE_SERVER_ERROR,
                template: $this->templatePrefix . 'errors.500'
            );
        }

        /** @var class-string<AbstractEntity> $entityClass */
        $entityClass = $this->entityClass;

        // Prototype instance ONLY for metadata (table + connection + columns)
        $prototype = new $entityClass();
        if (! ($prototype instanceof AbstractEntity)) {
            $data = [
                'error' => 'Unable to create Entity Class',
            ];
            if ($this->isDebugMode || $this->appEnv === 'development') {
                $data['details'] = [
                    'file' => __FILE__,
                    'function' => __FUNCTION__,
                    'method' => __METHOD__,
                ];
            }

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: $data,
                message: $data['error'],
                statusCode: Constants::STATUS_CODE_SERVER_ERROR,
                template: $this->templatePrefix . 'errors.500'
            );
        }

        $availableColumns = $prototype->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing($prototype->getTable());

        // Queries (keep them as Builders from start to finish)
        $countQuery = $entityClass::query();
        $listQuery = $entityClass::query();

        // Filters
        foreach ($filter as $attribute => $value) {
            if (! is_string($attribute) || ! in_array($attribute, $availableColumns, true)) {
                continue;
            }

            if (is_string($value) && str_contains($value, '%')) {
                $countQuery->where($attribute, 'like', $value);
                $listQuery->where($attribute, 'like', $value);
                continue;
            }

            // Accept scalar-ish values safely
            if (is_scalar($value) || $value === null) {
                $countQuery->where($attribute, $value);
                $listQuery->where($attribute, $value);
            }
        }

        // Sorting
        if ($sort !== null && $sort !== '') {
            $isDesc = str_starts_with($sort, '-');          // leading "-" means DESC
            $attribute = ltrim($sort, '-');

            if (in_array($attribute, $availableColumns, true)) {
                $listQuery->orderBy($attribute, $isDesc ? 'DESC' : 'ASC');
            }
        }

        // Pagination
        $defaultLimit = 10;

        $num = $page['num'] ?? 1;
        $limit = $page['limit'] ?? $defaultLimit;

        if (! is_numeric($num)) {
            $num = 1;
        }
        if (! is_numeric($limit)) {
            $limit = $defaultLimit;
        }

        $pageNum = max(1, (int) $num);
        $pageLimit = max(1, (int) $limit);

        $offset = ($pageNum - 1) * $pageLimit;

        $listQuery->offset($offset)
            ->limit($pageLimit);

        // Results
        $totalRecords = (int) $countQuery->count();

        /** @var Collection<int, AbstractEntity> $entities */
        $entities = $listQuery->get();

        $title = 'List of ' . $this->getFormattedEntityTablename();

        $data = [
            'title' => $title,
            'records' => $entities->map(fn ($entity) => $entity->toArray()),
            'pagination' => [
                'total' => $totalRecords,
                'page' => [
                    'num' => $pageNum,
                    'limit' => $pageLimit,
                ],
                'total_pages' => (int) ceil($totalRecords / $pageLimit),
                'current_query' => $queryParams,
                'url_path' => $request->getUri()
                    ->getPath(),
            ],
            'csrf_token' => Lapis::sessionUtility()->getCsrfToken(),
            'current_url' => Lapis::requestUtility()->getCurrentUrl(),
        ];

        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            data: $data,
            message: $title,
            template: $this->listTemplate
        );
    }

    /**
     * Show create new entity form.
     */
    public function create(ServerRequestInterface $request): ActionResponse
    {
        if (empty($this->createTemplate)) {
            $data = [
                'error' => 'Create Template must be assigned',
            ];
            if ($this->isDebugMode || $this->appEnv === 'development') {
                $data['details'] = [
                    'file' => __FILE__,
                    'function' => __FUNCTION__,
                    'method' => __METHOD__,
                ];
            }

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: $data,
                message: $data['error'],
                statusCode: Constants::STATUS_CODE_SERVER_ERROR,
                template: $this->templatePrefix . 'errors.500'
            );
        }

        $sessionUtility = Lapis::sessionUtility();
        $csrfToken = $sessionUtility->getCsrfToken();
        $oldInputs = $sessionUtility->getFlash('old_inputs', []);
        $errors = $sessionUtility->getFlash('validation_errors', []);

        $title = 'Create new ' . $this->getFormattedEntityTablename() . ' record';

        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            data: [
                'title' => $title,
                'csrf_token' => $csrfToken,
                'errors' => $errors,
                'old' => $oldInputs,
                'back_action' => Lapis::requestUtility()->getAllowedReferer(),
                'post_action' => Lapis::requestUtility()->getAllowedReferer(),
            ],
            message: 'Create New Record Endpoint Ready',
            template: $this->createTemplate
        );
    }

    /**
     * Store new entity record.
     */
    public function store(ServerRequestInterface $request): ActionResponse
    {
        // if (! $this->isAuthorized('store')) {
        //     return;
        // }

        $sessionUtility = Lapis::sessionUtility();
        $htmlRedirect = Lapis::requestUtility()->getReferer();

        if (empty($this->entityClass)) {
            $errorMessage = 'Entity Class not defined';
            $sessionUtility->setAlert('danger', $errorMessage);

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => $errorMessage,
                ],
                message: $errorMessage,
                statusCode: Constants::STATUS_CODE_SERVER_ERROR,
                htmlRedirect: $htmlRedirect
            );
        }

        $entity = method_exists($this, 'newEntityInstance')
            ? $this->newEntityInstance()
            : new $this->entityClass();
        if (! ($entity instanceof AbstractEntity)) {
            $errorMessage = 'Unable to create Entity Class';
            $sessionUtility->setAlert('danger', $errorMessage);

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => $errorMessage,
                ],
                message: $errorMessage,
                statusCode: Constants::STATUS_CODE_SERVER_ERROR,
                htmlRedirect: $htmlRedirect
            );
        }

        if (empty($this->storeCheckerClass)) {
            $errorMessage = 'Store Checker Class not defined';
            $sessionUtility->setAlert('danger', $errorMessage);

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => $errorMessage,
                ],
                message: $errorMessage,
                statusCode: Constants::STATUS_CODE_SERVER_ERROR,
                htmlRedirect: $htmlRedirect
            );
        }

        $checker = new $this->storeCheckerClass();
        if (! ($checker instanceof AbstractChecker)) {
            $errorMessage = 'Checker does not extends AbstractChecker';
            $sessionUtility->setAlert('danger', $errorMessage);

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => $errorMessage,
                ],
                message: $errorMessage,
                statusCode: Constants::STATUS_CODE_SERVER_ERROR,
                htmlRedirect: $htmlRedirect
            );
        }

        /** @var array<string, mixed> $inputs */
        $inputs = $request->getParsedBody();
        if (! $checker->isValid($inputs)) {
            $sessionUtility->setFlash('old_inputs', $inputs);
            $sessionUtility->setFlash('validation_errors', $checker->getErrors());

            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: [
                    'fail' => $checker->getErrors(),
                ],
                message: 'Failed validation',
                statusCode: Constants::STATUS_CODE_DATA_CONFLICT,
                htmlRedirect: $htmlRedirect
            );
        }

        $validInputs = $checker->getInputs();
        $columns = $entity->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing($entity->getTable());
        foreach ($validInputs as $key => $value) {
            if (in_array($key, $columns, true)) {
                $entity->{$key} = $value;
            }
        }

        /** @var User $user */
        $user = $request->getAttribute('user');
        if (in_array('created_by_type', $columns, true) && property_exists($entity, 'created_by_type')) {
            $entity->created_by_type = $user->user_type;
        }
        if (in_array('created_by_id', $columns, true) && property_exists($entity, 'created_by_id')) {
            $entity->created_by_id = $user->getId();
        }
        $entity->save();

        $auditLog = Lapis::interactorRegistry()->getOrSkip('core.system_monitor.audit_log');
        if (is_string($auditLog) && class_exists($auditLog) && method_exists($auditLog, 'record')) {
            $auditLog::record($this->entityClass . ' stored', [
                'stored_entity_id' => $entity->getId(),
                'user_type' => $user->user_type,
                'user_id' => $user->getId(),
                'client_type' => Lapis::requestUtility()->getClientType(),
                'user_agent' => Lapis::requestUtility()->getUserAgent(),
                'ip_address' => $request->getAttribute('client-ip'),
            ]);
        }

        $successMessage = 'Record stored successfully';
        $sessionUtility->setAlert('success', $successMessage);
        $sessionUtility->setFlash('old_inputs', $entity->toArray());

        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            data: [
                'record' => $entity->toArray(),
            ],
            message: $successMessage,
            htmlRedirect: $htmlRedirect
        );
    }

    /**
     * Retrieve an entity record.
     */
    public function show(ServerRequestInterface $request, int|string $id): ActionResponse
    {
        // if (! $this->isAuthorized('show')) {
        //     return;
        // }

        if (empty($this->entityClass)) {
            $data = [
                'error' => 'Entity Class not defined',
            ];
            if ($this->isDebugMode || $this->appEnv === 'development') {
                $data['details'] = [
                    'file' => __FILE__,
                    'function' => __FUNCTION__,
                    'method' => __METHOD__,
                ];
            }

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: $data,
                message: $data['error'],
                statusCode: Constants::STATUS_CODE_SERVER_ERROR,
                template: $this->templatePrefix . 'errors.500'
            );
        }

        $entityClass = method_exists($this, 'newEntityInstance')
            ? $this->newEntityInstance()
            : new $this->entityClass();

        if (! ($entityClass instanceof AbstractEntity)) {
            $data = [
                'error' => 'Unable to create Entity Class',
            ];
            if ($this->isDebugMode || $this->appEnv === 'development') {
                $data['details'] = [
                    'file' => __FILE__,
                    'function' => __FUNCTION__,
                    'method' => __METHOD__,
                ];
            }

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: $data,
                message: $data['error'],
                statusCode: Constants::STATUS_CODE_SERVER_ERROR,
                template: $this->templatePrefix . 'errors.500'
            );
        }

        /** @var AbstractEntity|null $entity */
        $entity = $entityClass->find($id);
        if ($entity === null) {
            $data = [
                'fail' => 'Record not found',
                'requestedPath' => $request->getUri()
                    ->__toString(),
            ];

            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: $data,
                message: $data['fail'],
                statusCode: Constants::STATUS_CODE_NOT_FOUND,
                template: $this->templatePrefix . 'errors.404'
            );
        }

        $title = $this->getFormattedEntityTablename() . ' single record details';
        $data = [
            'title' => $title,
            'record' => $entity->toArray(),
            'back_action' => Lapis::requestUtility()->getAllowedReferer(),
        ];

        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            data: $data,
            message: $title,
            template: $this->showTemplate
        );
    }

    /**
     * Show edit new entity form.
     */
    public function edit(ServerRequestInterface $request, int|string $id): ActionResponse
    {
        if (empty($this->editTemplate)) {
            $data = [
                'error' => 'Edit Template must be assigned',
            ];
            if ($this->isDebugMode || $this->appEnv === 'development') {
                $data['details'] = [
                    'file' => __FILE__,
                    'function' => __FUNCTION__,
                    'method' => __METHOD__,
                ];
            }

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: $data,
                message: $data['error'],
                statusCode: Constants::STATUS_CODE_SERVER_ERROR,
                template: $this->templatePrefix . 'errors.500'
            );
        }

        $sessionUtility = Lapis::sessionUtility();
        $csrfToken = $sessionUtility->getCsrfToken();
        $oldInputs = $sessionUtility->getFlash('old_inputs', []);
        $errors = $sessionUtility->getFlash('validation_errors', []);

        $htmlRedirect = Lapis::requestUtility()->getReferer();
        if (empty($this->entityClass)) {
            $errorMessage = 'Entity Class not defined';
            $sessionUtility->setAlert('danger', $errorMessage);

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => $errorMessage,
                ],
                message: $errorMessage,
                statusCode: Constants::STATUS_CODE_SERVER_ERROR,
                htmlRedirect: $htmlRedirect
            );
        }

        $entityClass = method_exists($this, 'newEntityInstance')
            ? $this->newEntityInstance()
            : new $this->entityClass();
        if (! ($entityClass instanceof AbstractEntity)) {
            $errorMessage = 'Unable to create Entity Class';
            $sessionUtility->setAlert('danger', $errorMessage);

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => $errorMessage,
                ],
                message: $errorMessage,
                statusCode: Constants::STATUS_CODE_SERVER_ERROR,
                htmlRedirect: $htmlRedirect
            );
        }

        /** @var AbstractEntity|null $entity */
        $entity = $entityClass->find($id);
        if (! $entity) {
            $errorMessage = 'Record not found';
            $sessionUtility->setAlert('warning', $errorMessage);

            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: [
                    'fail' => $errorMessage,
                ],
                message: $errorMessage,
                statusCode: Constants::STATUS_CODE_NOT_FOUND,
                htmlRedirect: $htmlRedirect
            );
        }

        $title = 'Edit ' . $this->getFormattedEntityTablename() . ' record `' . $id . '`';
        $listUrl = Lapis::requestUtility()->isAdminSite() ? $this->getAdminListUrl() : $this->getListUrl();
        $postUrl = Lapis::requestUtility()->isAdminSite() ? $this->getAdminUpdateUrl($id) : $this->getUpdateUrl($id);

        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            data: [
                'title' => $title,
                'csrf_token' => $csrfToken,
                'errors' => $errors,
                'old' => $oldInputs,
                'record' => $entity->toArray(),
                'back_action' => $listUrl,
                'post_action' => $postUrl,
            ],
            message: 'Update New Record Endpoint Ready',
            template: $this->editTemplate
        );
    }

    /**
     * Update an entity record.
     */
    public function update(ServerRequestInterface $request, int|string $id): ActionResponse
    {
        // if (! $this->isAuthorized('update')) {
        //     return;
        // }

        $sessionUtility = Lapis::sessionUtility();
        $htmlRedirect = Lapis::requestUtility()->getReferer();

        if (empty($this->entityClass)) {
            $errorMessage = 'Entity Class not defined';
            $sessionUtility->setAlert('danger', $errorMessage);

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => $errorMessage,
                ],
                message: $errorMessage,
                statusCode: Constants::STATUS_CODE_SERVER_ERROR,
                htmlRedirect: $htmlRedirect
            );
        }

        if (empty($this->updateCheckerClass)) {
            $errorMessage = 'Update Checker Class not defined';
            $sessionUtility->setAlert('danger', $errorMessage);

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => $errorMessage,
                ],
                message: $errorMessage,
                statusCode: Constants::STATUS_CODE_SERVER_ERROR,
                htmlRedirect: $htmlRedirect
            );
        }

        $checker = new $this->updateCheckerClass();
        if (! $checker instanceof AbstractChecker) {
            $errorMessage = 'Checker does not extends AbstractChecker';
            $sessionUtility->setAlert('danger', $errorMessage);

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => $errorMessage,
                ],
                message: $errorMessage,
                statusCode: Constants::STATUS_CODE_SERVER_ERROR,
                htmlRedirect: $htmlRedirect
            );
        }

        $entityClass = method_exists($this, 'newEntityInstance')
            ? $this->newEntityInstance()
            : new $this->entityClass();
        if (! ($entityClass instanceof AbstractEntity)) {
            $errorMessage = 'Unable to create Entity Class';
            $sessionUtility->setAlert('danger', $errorMessage);

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => $errorMessage,
                ],
                message: $errorMessage,
                statusCode: Constants::STATUS_CODE_SERVER_ERROR,
                htmlRedirect: $htmlRedirect
            );
        }

        /** @var AbstractEntity|null $entity */
        $entity = $entityClass->find($id);
        if (! $entity) {
            $errorMessage = 'Record not found';
            $sessionUtility->setAlert('warning', $errorMessage);

            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: [
                    'fail' => $errorMessage,
                ],
                message: $errorMessage,
                statusCode: Constants::STATUS_CODE_NOT_FOUND,
                htmlRedirect: $htmlRedirect
            );
        }

        $checker->setEntity($entity);

        /** @var array<string, mixed> $inputs */
        $inputs = $request->getParsedBody();
        if (! $checker->isValid($inputs)) {
            $sessionUtility->setFlash('old_inputs', $inputs);
            $sessionUtility->setFlash('validation_errors', $checker->getErrors());

            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: [
                    'error' => $checker->getErrors(),
                ],
                message: 'Failed validation',
                statusCode: Constants::STATUS_CODE_DATA_CONFLICT,
                htmlRedirect: $htmlRedirect
            );
        }

        $validInputs = $checker->getInputs();
        $columns = $entity->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing($entity->getTable());
        foreach ($validInputs as $key => $value) {
            if (in_array($key, $columns, true)) {
                $entity->{$key} = $value;
            }
        }

        /** @var User $user */
        $user = $request->getAttribute('user');
        if (in_array('updated_by_type', $columns, true) && property_exists($entity, 'updated_by_type')) {
            $entity->updated_by_type = $user->user_type;
        }
        if (in_array('updated_by_id', $columns, true) && property_exists($entity, 'updated_by_id')) {
            $entity->updated_by_id = $user->getId();
        }
        $entity->update();

        $auditLog = Lapis::interactorRegistry()->getOrSkip('core.system_monitor.audit_log');
        if (is_string($auditLog) && class_exists($auditLog) && method_exists($auditLog, 'record')) {
            $auditLog::record($this->entityClass . ' updated', [
                'stored_entity_id' => $id,
                'user_type' => $user->user_type,
                'user_id' => $user->getId(),
                'client_type' => Lapis::requestUtility()->getClientType(),
                'user_agent' => Lapis::requestUtility()->getUserAgent(),
                'ip_address' => $request->getAttribute('client-ip'),
            ]);
        }

        $successMessage = 'Record updated successfully';
        $sessionUtility->setAlert('success', $successMessage);

        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            data: [
                'record' => $entity->toArray(),
            ],
            message: $successMessage,
            htmlRedirect: $htmlRedirect
        );
    }

    /**
     * Delete an entity record.
     */
    public function destroy(ServerRequestInterface $request, int|string $id): ActionResponse
    {
        // if (! $this->isAuthorized('destroy')) {
        //     return;
        // }

        if (empty($this->entityClass)) {
            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => 'Entity Class not defined',
                ],
                message: 'Entity Class not defined',
                statusCode: Constants::STATUS_CODE_SERVER_ERROR
            );
        }

        $entityClass = method_exists($this, 'newEntityInstance')
            ? $this->newEntityInstance()
            : new $this->entityClass();

        if (! ($entityClass instanceof AbstractEntity)) {
            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => 'Unable to create Entity Class',
                ],
                message: 'Unable to create Entity Class',
                statusCode: Constants::STATUS_CODE_SERVER_ERROR
            );
        }

        /** @var AbstractEntity|null $entity */
        $entity = $entityClass->find($id);
        if (empty($entity)) {
            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: [
                    'fail' => 'Record not found',
                ],
                message: 'Record not found',
                statusCode: Constants::STATUS_CODE_NOT_FOUND
            );
        }

        $entity->delete();

        $auditLog = Lapis::interactorRegistry()->getOrSkip('core.system_monitor.audit_log');
        if (is_string($auditLog) && class_exists($auditLog) && method_exists($auditLog, 'record')) {
            /** @var User $user */
            $user = $request->getAttribute('user');
            $auditLog::record($this->entityClass . ' destroyed', [
                'destroyed_entity_id' => $id,
                'user_type' => $user->user_type,
                'user_id' => $user->getId(),
                'client_type' => Lapis::requestUtility()->getClientType(),
                'user_agent' => Lapis::requestUtility()->getUserAgent(),
                'ip_address' => $request->getAttribute('client-ip'),
            ]);
        }

        return new ActionResponse(status: ActionResponse::SUCCESS, statusCode: Constants::STATUS_CODE_DELETED);
    }

    protected function resolveAccessVerifier(): ActionAccessVerifierInterface
    {
        return new AllowAllVerifier();
    }

    protected function getFormattedEntityTablename(): string
    {
        /** @var AbstractEntity $entity */
        $entity = new $this->entityClass();

        return ucwords(str_replace('_', ' ', $entity->getTable()));
    }

    protected function getListUrl(string $module = ''): string
    {
        return '/' . (! empty($module) ? $module : strtolower($this->getFormattedEntityTablename()));
    }

    protected function getAdminListUrl(string $module = ''): string
    {
        /** @var string $adminPrefix */
        $adminPrefix = Lapis::configRegistry()->get('app.routes.admin_prefix');

        return $adminPrefix . $this->getListUrl($module);
    }

    protected function getEditUrl(int|string $id, string $module = ''): string
    {
        return $this->getListUrl($module) . '/' . ((string) $id) . '/edit';
    }

    protected function getAdminEditUrl(int|string $id, string $module = ''): string
    {
        /** @var string $adminPrefix */
        $adminPrefix = Lapis::configRegistry()->get('app.routes.admin_prefix');

        return $adminPrefix . $this->getEditUrl($id, $module);
    }

    protected function getUpdateUrl(int|string $id, string $module = ''): string
    {
        return $this->getListUrl($module) . '/' . ((string) $id);
    }

    protected function getAdminUpdateUrl(int|string $id, string $module = ''): string
    {
        /** @var string $adminPrefix */
        $adminPrefix = Lapis::configRegistry()->get('app.routes.admin_prefix');

        return $adminPrefix . $this->getUpdateUrl($id, $module);
    }

    protected function isAuthorized(string $action): bool
    {
        if (! $this->accessVerifier->can($action)) {
            // return new ActionResponse('fail', [], 'Forbidden: You do not have permission.', Constants::STATUS_CODE_FORBIDDEN);
            return false;
        }

        return true;
    }
}
