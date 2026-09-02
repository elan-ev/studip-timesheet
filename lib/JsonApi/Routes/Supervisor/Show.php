<?php
/**
 * Supervisor Show Route Handler
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
use StudipTimesheet\JsonApi\Schemas\SupervisorSchema;
use StudipTimesheet\Models\Supervisor;

class Show extends JsonApiController
{
    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        SupervisorSchema::REL_CONTRACT,
        SupervisorSchema::REL_USER,
    ];

    /**
     * Show a Supervisor.
     * @param Request $request
     * @param Response $response
     * @param mixed $args
     * @return void
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

        if (!Authority::canShowSupervisor($user, $contract)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($supervisor);
    }
}
