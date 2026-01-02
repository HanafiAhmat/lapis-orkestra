<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Controllers\Admin;

use BitSynama\Lapis\Framework\Contracts\ActionAccessVerifierInterface;
use BitSynama\Lapis\Framework\Controllers\AbstractAdminController;
use BitSynama\Lapis\Framework\DTO\ActionResponse;
use BitSynama\Lapis\Framework\Exceptions\NotFoundException;
use BitSynama\Lapis\Framework\Exceptions\ValidationException;
use BitSynama\Lapis\Framework\Foundation\Constants;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\User\Actions\StaffStoreAction;
use BitSynama\Lapis\Modules\User\Actions\StaffUpdateAction;
use BitSynama\Lapis\Modules\User\Entities\Staff;
use BitSynama\Lapis\Modules\User\Verifiers\StaffAccessVerifier;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use function rtrim;

class StaffController extends AbstractAdminController
{
    protected string $entityClass = Staff::class;

    /**
     * @var string create form template path.
     */
    protected string $createTemplate = 'admin.staff.create';

    /**
     * @var string edit form template path.
     */
    protected string $editTemplate = 'admin.staff.edit';

    /**
     * @var string list url path.
     */
    protected string $listUrl = '/staffs';

    /**
     * @var string edit url path.
     */
    protected string $editUrl = '/staffs/{id}/edit';

    public function store(ServerRequestInterface $request): ActionResponse
    {
        // if (! $this->isAuthorized('store')) {
        //     return;
        // }

        $sessionUtility = Lapis::sessionUtility();
        $formUrl = Lapis::requestUtility()->getReferer();
        $listUrl = rtrim($formUrl, '/create');
        $inputs = $request->getParsedBody();
        try {
            $action = new StaffStoreAction($request->getParsedBody());
            $user = $action->handle();

            $message = 'New staff created sucessfully';
            $sessionUtility->setAlert('success', $message);

            return new ActionResponse(
                status: ActionResponse::SUCCESS,
                data: [
                    'record' => $user->toArray(),
                ],
                message: $message,
                statusCode: Constants::STATUS_CODE_CREATED,
                htmlRedirect: $listUrl
            );
        } catch (ValidationException $e) {
            $sessionUtility->setAlert('danger', $e->getMessage());

            $data = [
                'error' => $e->getErrors(),
                'fail' => $e->getMessage(),
            ];

            $sessionUtility->setFlash('old_inputs', $inputs);
            $sessionUtility->setFlash('validation_errors', $data['error']);

            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: $data,
                message: $e->getMessage(),
                statusCode: $e->getCode(),
                htmlRedirect: $formUrl
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

        $sessionUtility = Lapis::sessionUtility();
        $listUrl = $this->getAdminListUrl('staffs');
        $formUrl = $this->getAdminEditUrl($id, 'staffs');
        $inputs = $request->getParsedBody();

        try {
            $action = new StaffUpdateAction($request->getParsedBody(), $id);
            $user = $action->handle();

            $message = 'Staff updated sucessfully';
            $sessionUtility->setAlert('success', $message);

            return new ActionResponse(
                status: ActionResponse::SUCCESS,
                data: [
                    'record' => $user->toArray(),
                ],
                message: $message,
                htmlRedirect: $listUrl
            );
        } catch (NotFoundException $e) {
            return new ActionResponse(
                status: ActionResponse::FAIL,
                message: $e->getMessage(),
                statusCode: $e->getCode(),
                htmlRedirect: $listUrl
            );
        } catch (ValidationException $e) {
            $sessionUtility->setAlert('danger', $e->getMessage());

            $data = [
                'error' => $e->getErrors(),
                'fail' => $e->getMessage(),
            ];

            $sessionUtility->setFlash('old_inputs', $inputs);
            $sessionUtility->setFlash('validation_errors', $data['error']);

            return new ActionResponse(
                status: ActionResponse::FAIL,
                data: $data,
                message: $e->getMessage(),
                statusCode: $e->getCode(),
                htmlRedirect: $formUrl
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
        return new StaffAccessVerifier();
    }
}
