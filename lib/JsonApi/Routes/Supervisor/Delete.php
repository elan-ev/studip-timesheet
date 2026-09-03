<?php
/**
 * Supervisor Delete Route Handler
 *
 * @package   StudipTimesheet\JsonApi\Routes
 * @since     0.0.1
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipTimesheet\JsonApi\Routes\Supervisor;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipTimesheet\JsonApi\Routes\Authority;
use StudipTimesheet\Models\Supervisor;
use StudipTimesheet\Models\Contract;

class Delete extends JsonApiController
{
    /**
     * Delete Supervisor.
     * @param Request $request
     * @param Response $response
     * @param mixed $args
     * @return Response
     * @throws AuthorizationFailedException
     * @throws RecordNotFoundException
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $user = $this->getUser($request);



        [$contractId, $userId] = explode('_', $args['id']);
        $supervisor = Supervisor::find([$contractId, $userId]);
        if (!$supervisor) {
            throw new RecordNotFoundException();
        }

        $contract = Contract::find($contractId);
        if (!$contract) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canDeleteSupervisor($user, $contract)) {
            throw new AuthorizationFailedException();
        }

        $supervisor->delete();

        return $this->getCodeResponse(204);
    }
}
