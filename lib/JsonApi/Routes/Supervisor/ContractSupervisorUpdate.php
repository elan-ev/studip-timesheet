<?php
/**
 * Contract Supervisor Update Route Handler
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
use JsonApi\Routes\ValidationTrait;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipTimesheet\JsonApi\Routes\Authority;
use StudipTimesheet\JsonApi\Schemas\SupervisorSchema;
use StudipTimesheet\Models\Supervisor;
use StudipTimesheet\Models\Contract;

class ContractSupervisorUpdate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        SupervisorSchema::REL_CONTRACT,
        SupervisorSchema::REL_USER,
    ];

    /**
     * Update Supervisor.
     * @param Request $request
     * @param Response $response
     * @param mixed $args
     * @return Response
     * @throws AuthorizationFailedException
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        // In the contest of Supervisor, updating it does not make any sense!
        return $this->getCodeResponse(405);

        // $json = $this->validate($request);
        // $user = $this->getUser($request);

        // $contract = Contract::find($args['contract_id']);
        // if (!$contract || empty($contract->supervisors)) {
        //     throw new RecordNotFoundException();
        // }

        // [$contractId, $userId] = explode('_', $args['id']);
        // $supervisor = $contract->supervisors->find([$contractId, $userId]);
        // if (!$supervisor) {
        //     throw new RecordNotFoundException();
        // }

        // if (!Authority::canUpdateSupervisor($user)) {
        //     throw new AuthorizationFailedException();
        // }

        // $supervisor = $this->updateSupervisor($json, $supervisor);

        // return $this->getCreatedResponse($supervisor);
    }

    /**
     * @inheritDoc
     */
    protected function validateResourceDocument($json, $data)
    {
        // Higher level validation.
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }
        if (!self::arrayHas($json, 'data.attributes')) {
            return 'Missing `attributes` member of data block.';
        }

        // Attributes existence validation.
        if (!self::arrayHas($json, 'data.attributes.user-id')) {
            return 'Missing `user-id` member of attributes block.';
        }
    }

    /**
     * Extract data and updates supervisor.
     * @param array $json
     * @return Supervisor
     */
    private function updateSupervisor(array $json, Supervisor $supervisor)
    {

        $userId = self::arrayGet($json, 'data.attributes.user-id');

        $supervisor->user_id = $userId;
        $supervisor->store();

        return $supervisor;
    }
}
