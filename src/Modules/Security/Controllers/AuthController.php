<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Controllers;

use BitSynama\Lapis\Framework\Controllers\AbstractController;
use BitSynama\Lapis\Framework\DTO\ActionResponse;
use BitSynama\Lapis\Framework\Exceptions\BusinessRuleException;
use BitSynama\Lapis\Framework\Exceptions\NotFoundException;
use BitSynama\Lapis\Framework\Exceptions\ValidationException;
use BitSynama\Lapis\Framework\Foundation\Constants;
use BitSynama\Lapis\Framework\Foundation\Runtime;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\Actions\Auth\LoginAction;
use BitSynama\Lapis\Modules\Security\Actions\Auth\LogoutAction;
use BitSynama\Lapis\Modules\Security\Actions\Auth\PasswordResetConfirmationAction;
use BitSynama\Lapis\Modules\Security\Actions\Auth\PasswordResetRequestAction;
use BitSynama\Lapis\Modules\Security\Actions\Auth\RefreshTokenAction;
use BitSynama\Lapis\Modules\Security\Actions\Auth\RegisterAction;
use BitSynama\Lapis\Modules\Security\Actions\Auth\ResendVerifyEmailAction;
use BitSynama\Lapis\Modules\Security\Actions\Auth\VerifyEmailAction;
use BitSynama\Lapis\Modules\User\Entities\User;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use function array_map;
use function explode;
use function implode;
use function strtoupper;
use function substr_replace;

class AuthController extends AbstractController
{
    public function index(): ActionResponse
    {
        $notify = $this->notifyIfNotReady();
        if ($notify instanceof ActionResponse) {
            return $notify;
        }

        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            data: [
                'success' => 'Authenticated',
            ],
            message: 'Authenticated',
        );
    }

    public function emailVerification(ServerRequestInterface $request): ActionResponse
    {
        $notify = $this->notifyIfNotReady();
        if ($notify instanceof ActionResponse) {
            return $notify;
        }

        try {
            $params = $request->getQueryParams();
            $token = $params['token'] ?? '';

            $action = new VerifyEmailAction();
            $action->handle($token);

            return new ActionResponse(
                status: ActionResponse::SUCCESS,
                data: [
                    'success' => 'Email has been verified successfully',
                ],
                message: 'Email has been verified successfully'
            );
        } catch (NotFoundException | BusinessRuleException $e) {
            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: [
                    'fail' => $e->getMessage(),
                ],
                message: $e->getMessage(),
                statusCode: $e->getCode()
            );
        } catch (Throwable $e) {
            // should log here. possible coding error
            Lapis::loggerUtility()->error('Unhandled Exception On Email Verification', [
                'type' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => $e->getMessage(),
                ],
                message: $e->getMessage(),
                statusCode: Constants::STATUS_CODE_SERVER_ERROR
            );
        }
    }

    public function login(ServerRequestInterface $request): ActionResponse
    {
        $notify = $this->notifyIfNotReady();
        if ($notify instanceof ActionResponse) {
            return $notify;
        }

        $readiedUserTypes = Lapis::userTypeRegistry()->aliasesReadied();

        $sessionUtility = Lapis::sessionUtility();
        $csrfToken = $sessionUtility->getCsrfToken();
        $oldInputs = $sessionUtility->getFlash('old_inputs', []);
        $errors = $sessionUtility->getFlash('validation_errors', []);

        $templatePrefix = Lapis::viewUtility()->getTemplatePrefix();
        $template = $templatePrefix . 'auth.login';
        $redirectUrl = $this->getRedirectUrl();

        $data = [
            'csrf_token' => $csrfToken,
            'errors' => $errors,
            'old' => $oldInputs,
            'redied_user_types' => $readiedUserTypes,
            'password_reset_url' => $this->getPasswordResetUrl(),
            'login_url' => $this->getLoginUrl(),
            'register_url' => $this->getRegisterUrl(),
        ];

        $method = strtoupper($request->getMethod());
        switch ($method) {
            case 'GET':
                return new ActionResponse(
                    status: ActionResponse::SUCCESS,
                    data: $data,
                    message: 'Login endpoint ready',
                    template: $template
                );
            case 'POST':
                $inputs = $request->getParsedBody();
                try {
                    $action = new LoginAction($request);
                    $tokens = $action->handle();

                    return new ActionResponse(
                        status: ActionResponse::SUCCESS,
                        data: $tokens,
                        message: 'Login successful',
                        statusCode: Constants::STATUS_CODE_OK,
                        htmlRedirect: $redirectUrl
                    );
                } catch (ValidationException | BusinessRuleException $e) {
                    $data['old'] = $inputs;
                    $data['fail'] = $e->getMessage();
                    if ($e instanceof ValidationException) {
                        $data['errors'] = $e->getErrors();
                    }

                    return new ActionResponse(
                        status: ActionResponse::FAIL,
                        data: $data,
                        message: $e->getMessage(),
                        statusCode: $e->getCode(),
                        template: $template
                    );
                } catch (Throwable $e) {
                    // should log here. possible coding error
                    Lapis::loggerUtility()->error('Unhandled Exception On Login', [
                        'type' => $e::class,
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    return new ActionResponse(
                        status: ActionResponse::ERROR,
                        data: [
                            'error' => $e->getMessage(),
                        ],
                        message: $e->getMessage(),
                        statusCode: Constants::STATUS_CODE_SERVER_ERROR
                    );
                }

            default:
                return new ActionResponse(
                    status: ActionResponse::FAIL,
                    message: 'Method not supported',
                    statusCode: Constants::STATUS_CODE_FORBIDDEN
                );
        }
    }

    public function logout(ServerRequestInterface $request): ActionResponse
    {
        $notify = $this->notifyIfNotReady();
        if ($notify instanceof ActionResponse) {
            return $notify;
        }

        $redirectUrl = $this->getRedirectUrl();

        try {
            $action = new LogoutAction($request);
            $action->handle();
            return new ActionResponse(
                status: ActionResponse::SUCCESS,
                data: [
                    'success' => 'Logout successful',
                ],
                message: 'Logout successful',
                htmlRedirect: $redirectUrl
            );
        } catch (BusinessRuleException $e) {
            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: [
                    'fail' => $e->getMessage(),
                ],
                message: $e->getMessage(),
                statusCode: $e->getCode()
            );
        } catch (Throwable $e) {
            // should log here. possible coding error
            Lapis::loggerUtility()->error('Unhandled Exception On Logout', [
                'type' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => $e->getMessage(),
                ],
                statusCode: Constants::STATUS_CODE_SERVER_ERROR
            );
        }
    }

    public function passwordResetConfirmation(ServerRequestInterface $request): ActionResponse
    {
        $notify = $this->notifyIfNotReady();
        if ($notify instanceof ActionResponse) {
            return $notify;
        }

        $readiedUserTypes = Lapis::userTypeRegistry()->aliasesReadied();

        $sessionUtility = Lapis::sessionUtility();
        $csrfToken = $sessionUtility->getCsrfToken();

        $templatePrefix = Lapis::viewUtility()->getTemplatePrefix();
        $template = $templatePrefix . 'auth.password-reset-confirmation';

        $requestParams = $request->getQueryParams();
        $token = $requestParams['token'] ? (string) $requestParams['token'] : '';

        $data = [
            'masked_email' => '',
            'token' => $token,
            'csrf_token' => $csrfToken,
            'errors' => [],
            'old' => [],
            'redied_user_types' => $readiedUserTypes,
            'password_reset_url' => $this->getPasswordResetUrl(),
            'login_url' => $this->getLoginUrl(),
            'register_url' => $this->getRegisterUrl(),
        ];

        $action = new PasswordResetConfirmationAction($request);
        try {
            /** @var User $user */
            $user = $action->getUserFromToken($token);
            $emailFirstParts = explode('@', $user->email);
            $emailSecondParts = explode('.', $emailFirstParts[1]);
            $data['masked_email'] = substr_replace($emailFirstParts[0], '***', 2);
            $data['masked_email'] .= '@' . implode(
                '.',
                array_map(fn ($part) => substr_replace($part, '***', 2), $emailSecondParts)
            );
        } catch (BusinessRuleException | ValidationException | NotFoundException $e) {
            $data['fail'] = $e->getMessage();
            if ($e instanceof ValidationException) {
                $data['errors'] = $e->getErrors();
            }

            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: $data,
                message: $e->getMessage(),
                statusCode: $e->getCode(),
                template: $template
            );
        } catch (Throwable $e) {
            // should log here. possible coding error
            Lapis::loggerUtility()->error('Unhandled Exception On Password Reset Confirmation', [
                'type' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => $e->getMessage(),
                ],
                message: $e->getMessage(),
                statusCode: Constants::STATUS_CODE_SERVER_ERROR
            );
        }

        $method = strtoupper($request->getMethod());
        switch ($method) {
            case 'GET':
                return new ActionResponse(
                    status: ActionResponse::SUCCESS,
                    data: $data,
                    message: 'Password Reset Confirmation endpoint ready',
                    template: $template
                );
            case 'POST':
                $inputs = $request->getParsedBody();
                try {
                    $action->handle();

                    $successMessage = 'Password has been reset successfully';
                    $data['success'] = $successMessage;

                    return new ActionResponse(
                        status: ActionResponse::SUCCESS,
                        data: $data,
                        message: $successMessage,
                        template: $template
                    );
                } catch (BusinessRuleException | ValidationException | NotFoundException $e) {
                    $data['fail'] = $e->getMessage();
                    if ($e instanceof ValidationException) {
                        $data['errors'] = $e->getErrors();
                    }

                    return new ActionResponse(
                        status: ActionResponse::FAIL,
                        data: $data,
                        message: $e->getMessage(),
                        statusCode: $e->getCode(),
                        template: $template
                    );
                } catch (Throwable $e) {
                    // should log here. possible coding error
                    Lapis::loggerUtility()->error('Unhandled Exception On Password Reset Confirmation', [
                        'type' => $e::class,
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    return new ActionResponse(
                        status: ActionResponse::ERROR,
                        data: [
                            'error' => $e->getMessage(),
                        ],
                        message: $e->getMessage(),
                        statusCode: Constants::STATUS_CODE_SERVER_ERROR
                    );
                }

            default:
                return new ActionResponse(
                    status: ActionResponse::FAIL,
                    message: 'Method not supported',
                    statusCode: Constants::STATUS_CODE_FORBIDDEN
                );
        }
    }

    public function passwordResetRequest(ServerRequestInterface $request): ActionResponse
    {
        $notify = $this->notifyIfNotReady();
        if ($notify instanceof ActionResponse) {
            return $notify;
        }

        $readiedUserTypes = Lapis::userTypeRegistry()->aliasesReadied();

        $sessionUtility = Lapis::sessionUtility();
        $csrfToken = $sessionUtility->getCsrfToken();
        $data = [
            'csrf_token' => $csrfToken,
            'redied_user_types' => $readiedUserTypes,
            'password_reset_url' => $this->getPasswordResetUrl(),
            'login_url' => $this->getLoginUrl(),
            'register_url' => $this->getRegisterUrl(),
        ];

        $templatePrefix = Lapis::viewUtility()->getTemplatePrefix();
        $template = $templatePrefix . 'auth.password-reset-request';

        $method = strtoupper($request->getMethod());
        switch ($method) {
            case 'GET':
                return new ActionResponse(
                    status: ActionResponse::SUCCESS,
                    data: $data,
                    message: 'Password Reset Request endpoint ready',
                    template: $template
                );
            case 'POST':
                $inputs = $request->getParsedBody();
                $data['old'] = $inputs;
                try {
                    $action = new PasswordResetRequestAction($request);
                    $action->handle();

                    $message = 'Password Reset Request email sent if it exists';
                    $data['success'] = $message;

                    return new ActionResponse(
                        status: ActionResponse::SUCCESS,
                        data: $data,
                        message: $message,
                        template: $template
                    );
                } catch (BusinessRuleException | ValidationException $e) {
                    $data['fail'] = $e->getMessage();
                    if ($e instanceof ValidationException) {
                        $data['errors'] = $e->getErrors();
                    }

                    return new ActionResponse(
                        status: ActionResponse::FAIL,
                        data: $data,
                        message: $e->getMessage(),
                        statusCode: $e->getCode(),
                        template: $template
                    );
                } catch (Throwable $e) {
                    // should log here. possible coding error
                    Lapis::loggerUtility()->error('Unhandled Exception On Password Reset Request', [
                        'type' => $e::class,
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    return new ActionResponse(
                        status: ActionResponse::ERROR,
                        data: [
                            'error' => $e->getMessage(),
                        ],
                        message: $e->getMessage(),
                        statusCode: Constants::STATUS_CODE_SERVER_ERROR
                    );
                }

            default:
                return new ActionResponse(
                    status: ActionResponse::FAIL,
                    message: 'Method not supported',
                    statusCode: Constants::STATUS_CODE_FORBIDDEN
                );
        }
    }

    public function refreshToken(ServerRequestInterface $request): ActionResponse
    {
        $notify = $this->notifyIfNotReady();
        if ($notify instanceof ActionResponse) {
            return $notify;
        }

        try {
            $action = new RefreshTokenAction($request);
            $data = $action->handle();
            return new ActionResponse(
                status: ActionResponse::SUCCESS,
                data: $data,
                message: 'Tokens refreshed successfully'
            );
        } catch (BusinessRuleException | NotFoundException $e) {
            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: [
                    'fail' => $e->getMessage(),
                ],
                message: $e->getMessage(),
                statusCode: $e->getCode()
            );
        } catch (Throwable $e) {
            // should log here. possible coding error
            Lapis::loggerUtility()->error('Unhandled Exception On refresh token', [
                'type' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => $e->getMessage(),
                ],
                message: $e->getMessage(),
                statusCode: Constants::STATUS_CODE_SERVER_ERROR
            );
        }
    }

    public function register(ServerRequestInterface $request): ActionResponse
    {
        $notify = $this->notifyIfNotReady();
        if ($notify instanceof ActionResponse) {
            return $notify;
        }

        $readiedUserTypes = Lapis::userTypeRegistry()->aliasesReadied();
        $sessionUtility = Lapis::sessionUtility();
        $csrfToken = $sessionUtility->getCsrfToken();
        $oldInputs = $sessionUtility->getFlash('old_inputs', []);
        $errors = $sessionUtility->getFlash('validation_errors', []);

        $templatePrefix = Lapis::viewUtility()->getTemplatePrefix();

        $method = strtoupper($request->getMethod());
        switch ($method) {
            case 'GET':
                $data = [
                    'csrf_token' => $csrfToken,
                    'errors' => $errors,
                    'old' => $oldInputs,
                    'redied_user_types' => $readiedUserTypes,
                ];

                return new ActionResponse(
                    status: ActionResponse::SUCCESS,
                    data: $data,
                    message: 'Register endpoint ready',
                    template: $templatePrefix . 'auth.register'
                );
            case 'POST':
                $formUrl = Lapis::requestUtility()->getReferer();
                $inputs = $request->getParsedBody();
                try {
                    $action = new RegisterAction($request);
                    $user = $action->handle();

                    return new ActionResponse(
                        status: ActionResponse::SUCCESS,
                        data: [
                            'record' => $user->toArray(),
                        ],
                        message: 'Registration successful',
                        statusCode: Constants::STATUS_CODE_CREATED,
                        htmlRedirect: $formUrl
                    );
                } catch (ValidationException | BusinessRuleException $e) {
                    $data = $e instanceof ValidationException
                        ? [
                            'csrf_token' => $csrfToken,
                            'errors' => $e->getErrors(),
                            'fail' => $e->getMessage(),
                        ] : [
                            'csrf_token' => $csrfToken,
                            'errors' => [],
                            'fail' => $e->getMessage(),
                        ];

                    $sessionUtility->setFlash('old_inputs', $inputs);
                    $sessionUtility->setFlash('validation_errors', $data['errors']);

                    return new ActionResponse(
                        status: ActionResponse::FAIL,
                        data: $data,
                        message: $e->getMessage(),
                        statusCode: $e->getCode(),
                        htmlRedirect: $formUrl
                    );
                } catch (Throwable $e) {
                    // should log here. possible coding error
                    Lapis::loggerUtility()->error('Unhandled Exception On Registration', [
                        'type' => $e::class,
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    return new ActionResponse(
                        status: ActionResponse::ERROR,
                        data: [
                            'error' => $e->getMessage(),
                        ],
                        message: $e->getMessage(),
                        statusCode: Constants::STATUS_CODE_SERVER_ERROR
                    );
                }

            default:
                return new ActionResponse(
                    status: ActionResponse::FAIL,
                    message: 'Method not supported',
                    statusCode: Constants::STATUS_CODE_FORBIDDEN
                );
        }
    }

    public function resendEmailVerification(ServerRequestInterface $request): ActionResponse
    {
        $notify = $this->notifyIfNotReady();
        if ($notify instanceof ActionResponse) {
            return $notify;
        }

        try {
            /** @var User|null $user */
            $user = $request->getAttribute('user');
            $action = new ResendVerifyEmailAction();
            $action->handle($user);

            return new ActionResponse(
                status: ActionResponse::SUCCESS,
                data: [
                    'success' => 'Verification email sent',
                ],
                message: 'Verification email sent'
            );
        } catch (BusinessRuleException $e) {
            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: [
                    'fail' => $e->getMessage(),
                ],
                message: $e->getMessage(),
                statusCode: $e->getCode()
            );
        } catch (Throwable $e) {
            // should log here. possible coding error
            Lapis::loggerUtility()->error('Unhandled Exception On Resending Email Verification', [
                'type' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => $e->getMessage(),
                ],
                message: $e->getMessage(),
                statusCode: Constants::STATUS_CODE_SERVER_ERROR
            );
        }
    }

    private function notifyIfNotReady(): ActionResponse|bool
    {
        $templatePrefix = Lapis::viewUtility()->getTemplatePrefix();
        $readiedUserTypes = Lapis::userTypeRegistry()->aliasesReadied();
        // If none are ready, render a setup-needed page
        if (empty($readiedUserTypes)) {
            $data = [
                'error' => 'No user types are ready.',
            ];

            if (Runtime::isDev()) {
                $data['hint'] = 'Run database migrations and seeders for at least one user type.';
                $data['commands'] = ['php bin/console migration:migrate'];
            }

            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: $data,
                message: 'Login temporarily unavailable',
                statusCode: Constants::STATUS_CODE_SERVICE_UNAVAILABLE,
                template: $templatePrefix . 'errors.setup-required'
            );
        }

        return false;
    }

    private function getRedirectUrl(): string
    {
        /** @var string $adminPrefix */
        $adminPrefix = Lapis::configRegistry()->get('app.routes.admin_prefix');
        $redirectUrl = '/';
        if (Lapis::requestUtility()->isAdminSite()) {
            $redirectUrl = $adminPrefix;
        }

        return $redirectUrl;
    }

    private function getPasswordResetUrl(): string
    {
        /** @var string $adminPrefix */
        $adminPrefix = Lapis::configRegistry()->get('app.routes.admin_prefix');
        $url = '/auth/password-reset-request';
        if (Lapis::requestUtility()->isAdminSite()) {
            $url = $adminPrefix . $url;
        }

        return $url;
    }

    private function getLoginUrl(): string
    {
        /** @var string $adminPrefix */
        $adminPrefix = Lapis::configRegistry()->get('app.routes.admin_prefix');
        $url = '/auth/login';
        if (Lapis::requestUtility()->isAdminSite()) {
            $url = $adminPrefix . $url;
        }

        return $url;
    }

    private function getRegisterUrl(): string
    {
        /** @var string $adminPrefix */
        $adminPrefix = Lapis::configRegistry()->get('app.routes.admin_prefix');
        $url = '/auth/register';
        if (Lapis::requestUtility()->isAdminSite()) {
            $url = $adminPrefix . $url;
        }

        return $url;
    }

    // /**
    //  * Handle user profile update.
    //  */
    // public function updateProfile(): void
    // {
    //     $decoded = $this->app->get('user');
    //     $userEntity = new $this->userEntityClass();
    //     $entity = $userEntity->find($decoded->sub);

    //     if ($entity->isHydrated()) {
    //         $checker = Loader::dice()->create(UserUpdateChecker::class);
    //         if ($checker instanceof UserUpdateChecker) {
    //             $checker->setEntity($entity);
    //             if (! $checker->isValid()) {
    //                 ApiResponse::fail('Form has errors', $checker->getErrors(), Constants::STATUS_CODE_DATA_CONFLICT);
    //             }
    //         }

    //         $inputs = $this->app->request()
    //             ->data->getData();
    //         $user = AuthManager::updateProfile($decoded->sub, $inputs);

    //         ApiResponse::success('Profile updated successfully', $user->getData());
    //     } else {
    //         ApiResponse::fail('User not found', [], Constants::STATUS_CODE_NOT_FOUND);
    //     }
    // }
}
