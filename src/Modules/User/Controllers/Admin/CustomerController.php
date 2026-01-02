<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Controllers\Admin;

use BitSynama\Lapis\Framework\Contracts\ActionAccessVerifierInterface;
use BitSynama\Lapis\Framework\Controllers\AbstractAdminController;
use BitSynama\Lapis\Framework\DTO\ActionResponse;
use BitSynama\Lapis\Framework\Exceptions\NotFoundException;
use BitSynama\Lapis\Framework\Exceptions\ValidationException;
use BitSynama\Lapis\Framework\Foundation\Constants;
use BitSynama\Lapis\Modules\User\Actions\CustomerStoreAction;
use BitSynama\Lapis\Modules\User\Actions\CustomerUpdateAction;
use BitSynama\Lapis\Modules\User\Entities\Customer;
use BitSynama\Lapis\Modules\User\Verifiers\CustomerAccessVerifier;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * CustomerController class for Customer resource.
 */
class CustomerController extends AbstractAdminController
{
    /**
     * inherited
     */
    protected string $entityClass = Customer::class;

    public function store(ServerRequestInterface $request): ActionResponse
    {
        // if (! $this->isAuthorized('store')) {
        //     return;
        // }

        try {
            $action = new CustomerStoreAction($request->getParsedBody());
            $user = $action->handle();
            return new ActionResponse(
                status: ActionResponse::SUCCESS,
                data: [
                    'record' => $user->toArray(),
                ],
                message: 'Customer stored',
                statusCode: Constants::STATUS_CODE_CREATED
            );
        } catch (ValidationException $e) {
            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: $e->getErrors(),
                message: $e->getMessage(),
                statusCode: $e->getCode()
            );
        } catch (Throwable $e) {
            return new ActionResponse(
                status: ActionResponse::ERROR,
                message: $e->getMessage(),
                statusCode: $e->getCode()
            );
        }
    }

    public function update(ServerRequestInterface $request, int|string $id): ActionResponse
    {
        // if (! $this->isAuthorized('update')) {
        //     return;
        // }

        try {
            $action = new CustomerUpdateAction($request->getParsedBody(), $id);
            $user = $action->handle();
            return new ActionResponse(
                status: ActionResponse::SUCCESS,
                data: [
                    'record' => $user->toArray(),
                ],
                message: 'Customer updated'
            );
        } catch (NotFoundException $e) {
            return new ActionResponse(
                status: ActionResponse::FAIL,
                message: $e->getMessage(),
                statusCode: $e->getCode()
            );
        } catch (ValidationException $e) {
            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: $e->getErrors(),
                message: $e->getMessage(),
                statusCode: $e->getCode()
            );
        } catch (Throwable $e) {
            return new ActionResponse(
                status: ActionResponse::ERROR,
                message: $e->getMessage(),
                statusCode: $e->getCode()
            );
        }
    }

    protected function resolveAccessVerifier(): ActionAccessVerifierInterface
    {
        return new CustomerAccessVerifier();
    }
}
