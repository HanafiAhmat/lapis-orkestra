<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Controllers;

use BitSynama\Lapis\Framework\Controllers\AbstractController;
use BitSynama\Lapis\Framework\DTO\ActionResponse;
use BitSynama\Lapis\Framework\Foundation\Constants;
use BitSynama\Lapis\Framework\Foundation\Runtime;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\Actions\Mfa\IssueOtpAction;
use BitSynama\Lapis\Modules\Security\Actions\Mfa\RevokeTrustedDevicesAction;
use BitSynama\Lapis\Modules\Security\Actions\Mfa\TotpResetAction;
use BitSynama\Lapis\Modules\Security\Actions\Mfa\TotpSetupAction;
use BitSynama\Lapis\Modules\Security\Actions\Mfa\VerifyOtpAction;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class MfaController extends AbstractController
{
    public function issue(ServerRequestInterface $request): ActionResponse
    {
        $notify = $this->notifyIfNotReady();
        if ($notify instanceof ActionResponse) {
            return $notify;
        }

        if (! Lapis::varRegistry()->has('user')) {
            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: [
                    'fail' => 'User not authenticated',
                ],
                message: 'User not authenticated',
                statusCode: Constants::STATUS_CODE_UNAUTHORIZED
            );
        }

        try {
            (new IssueOtpAction($request))->handle();

            return new ActionResponse(
                status: ActionResponse::SUCCESS,
                data: [
                    'success' => 'OTP sent successfully',
                ],
                message: 'OTP sent successfully'
            );
        } catch (Throwable $e) {
            // should log here. possible coding error
            Lapis::loggerUtility()->error('Failed to send OTP', [
                'type' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => 'Failed to send OTP: ' . $e->getMessage(),
                ],
                message: $e->getMessage(),
                statusCode: Constants::STATUS_CODE_SERVER_ERROR
            );
        }
    }

    public function verify(ServerRequestInterface $request): ActionResponse
    {
        $notify = $this->notifyIfNotReady();
        if ($notify instanceof ActionResponse) {
            return $notify;
        }

        if (! Lapis::varRegistry()->has('user')) {
            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: [
                    'fail' => 'User not authenticated',
                ],
                message: 'User not authenticated',
                statusCode: Constants::STATUS_CODE_UNAUTHORIZED
            );
        }

        try {
            $action = new VerifyOtpAction($request);
            $isValid = $action->handle();
            if (! $isValid) {
                return new ActionResponse(
                    status: ActionResponse::FAIL,
                    data: [
                        'fail' => 'Invalid or expired OTP',
                    ],
                    message: 'Invalid or expired OTP',
                    statusCode: Constants::STATUS_CODE_UNAUTHORIZED
                );
            }

            return new ActionResponse(
                status: ActionResponse::SUCCESS,
                data: [
                    'success' => 'OTP verified successfully',
                ],
                message: 'OTP verified successfully'
            );
        } catch (Throwable $e) {
            // should log here. possible coding error
            Lapis::loggerUtility()->error('Unexpected error during OTP verification', [
                'type' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => 'Unexpected error during OTP verification: ' . $e->getMessage(),
                ],
                message: $e->getMessage(),
                statusCode: Constants::STATUS_CODE_SERVER_ERROR
            );
        }
    }

    public function totpSetup(ServerRequestInterface $request): ActionResponse
    {
        $notify = $this->notifyIfNotReady();
        if ($notify instanceof ActionResponse) {
            return $notify;
        }

        if (! Lapis::varRegistry()->has('user')) {
            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: [
                    'fail' => 'User not authenticated',
                ],
                message: 'User not authenticated',
                statusCode: Constants::STATUS_CODE_UNAUTHORIZED
            );
        }

        try {
            $action = new TotpSetupAction();
            $data = $action->handle();
            $data['success'] = 'TOTP Setup';

            return new ActionResponse(status: ActionResponse::SUCCESS, data: $data, message: $data['success']);
        } catch (Throwable $e) {
            // should log here. possible coding error
            Lapis::loggerUtility()->error('Failed to setup TOTP', [
                'type' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => 'Failed to setup TOTP: ' . $e->getMessage(),
                ],
                message: $e->getMessage(),
                statusCode: Constants::STATUS_CODE_SERVER_ERROR
            );
        }
    }

    public function totpReset(ServerRequestInterface $request): ActionResponse
    {
        $notify = $this->notifyIfNotReady();
        if ($notify instanceof ActionResponse) {
            return $notify;
        }

        if (! Lapis::varRegistry()->has('user')) {
            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: [
                    'fail' => 'User not authenticated',
                ],
                message: 'User not authenticated',
                statusCode: Constants::STATUS_CODE_UNAUTHORIZED
            );
        }

        try {
            $action = new TotpResetAction();
            $deleted = $action->handle();
            if ($deleted) {
                return new ActionResponse(
                    status: ActionResponse::SUCCESS,
                    data: [
                        'success' => 'TOTP secret has been reset.',
                    ],
                    message: 'TOTP secret has been reset.'
                );
            }
            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: [
                    'fail' => 'No TOTP record found to reset.',
                ],
                message: 'No TOTP record found to reset.',
            );

        } catch (Throwable $e) {
            // should log here. possible coding error
            Lapis::loggerUtility()->error('Failed to reset TOTP', [
                'type' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => 'Failed to reset TOTP: ' . $e->getMessage(),
                ],
                message: $e->getMessage(),
                statusCode: Constants::STATUS_CODE_SERVER_ERROR
            );
        }
    }

    public function revoke(ServerRequestInterface $request): ActionResponse
    {
        $notify = $this->notifyIfNotReady();
        if ($notify instanceof ActionResponse) {
            return $notify;
        }

        if (! Lapis::varRegistry()->has('user')) {
            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: [
                    'fail' => 'User not authenticated',
                ],
                message: 'User not authenticated',
                statusCode: Constants::STATUS_CODE_UNAUTHORIZED
            );
        }

        try {
            $action = new RevokeTrustedDevicesAction($request);
            $count = $action->handle();
            if ($count > 0) {
                $message = "{$count} trusted device(s) revoked";
            } else {
                $message = 'No trusted device(s) found to revoke.';
            }

            return new ActionResponse(
                status: ActionResponse::SUCCESS,
                data: [
                    'success' => $message,
                ],
                message: $message
            );
        } catch (Throwable $e) {
            // should log here. possible coding error
            Lapis::loggerUtility()->error('Failed to revoke trusted devices', [
                'type' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new ActionResponse(
                status: ActionResponse::ERROR,
                data: [
                    'error' => 'Failed to revoke trusted devices: ' . $e->getMessage(),
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
}
