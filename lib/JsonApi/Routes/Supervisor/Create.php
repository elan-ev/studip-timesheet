<?php
/**
 * Supervisor Create Route Handler
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
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipTimesheet\JsonApi\Routes\Authority;
use StudipTimesheet\JsonApi\Schemas\SupervisorSchema;
use StudipTimesheet\Models\Supervisor;
use StudipTimesheet\Models\Contract;

class Create extends JsonApiController
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
     * Create Supervisor.
     * @param Request $request
     * @param Response $response
     * @param mixed $args
     * @return Response
     * @throws AuthorizationFailedException
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        $user = $this->getUser($request);

        $contractId = self::arrayGet($json, 'data.attributes.contract-id');

        $contract = Contract::find($contractId);

        if (!Authority::canCreateSupervisor($user, $contract)) {
            throw new AuthorizationFailedException();
        }

        $supervisor = $this->createSupervisor($json);

        return $this->getCreatedResponse($supervisor);
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
        if (!self::arrayHas($json, 'data.attributes.contract-id')) {
            return 'Missing `contract-id` member of attributes block.';
        }
        if (!self::arrayHas($json, 'data.attributes.user-id')) {
            return 'Missing `user-id` member of attributes block.';
        }
    }

    /**
     * Extract data and creates supervisor.
     * @param array $json
     * @return Supervisor
     */
    private function createSupervisor(array $json)
    {
        $userId = self::arrayGet($json, 'data.attributes.user-id');
        $contractId = self::arrayGet($json, 'data.attributes.contract-id');

        $supervisor = new Supervisor();
        $supervisor->user_id = $userId;
        $supervisor->contract_id = $contractId;
        $supervisor->store();

        return $supervisor;
    }
}
