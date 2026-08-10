<?php
/**
 * Contract Update Route Handler
 *
 * @package   StudipTimesheet\JsonApi\Routes
 * @since     0.0.1
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipTimesheet\JsonApi\Routes\Contract;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipTimesheet\JsonApi\Routes\Authority;
use StudipTimesheet\JsonApi\Schemas\ContractSchema;
use StudipTimesheet\Models\Contract;

class Update extends JsonApiController
{
    use ValidationTrait;

    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        ContractSchema::REL_EMPLOYEE,
        ContractSchema::REL_INSTITUTE,
        ContractSchema::REL_PREDECESSOR,
    ];

    /**
     * Update Contract.
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

        if (!Authority::canUpdateContract($user)) {
            throw new AuthorizationFailedException();
        }

        $contract = Contract::find($args['id']);
        if (!$contract) {
            throw new RecordNotFoundException();
        }

        $contract = $this->updateContract($json, $contract);
        $contract->id = '';
        return $this->getContentResponse($contract);
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
        if (!self::arrayHas($json, 'data.attributes.type')) {
            return 'Missing `type` member of attributes block.';
        }
        $type = self::arrayGet($json, 'data.attributes.type');
        if (!in_array($type, Contract::TYPES)) {
            return 'Invalid value for attribute `type`.';
        }

        if (!self::arrayHas($json, 'data.attributes.hours-per-month')) {
            return 'Missing `hours-per-month` member of attributes block.';
        }
        if (!self::arrayHas($json, 'data.attributes.start-date')) {
            return 'Missing `start-date` member of attributes block.';
        }
        if (!self::arrayHas($json, 'data.attributes.end-date')) {
            return 'Missing `end-date` member of attributes block.';
        }
    }

    /**
     * Extract data and creates contract.
     * @param array $json
     * @return Contract
     */
    private function updateContract(array $json, Contract $contract)
    {
        $type = self::arrayGet($json, 'data.attributes.type');
        $predecessorId = self::arrayGet($json, 'data.attributes.predecessor-id');
        $label = self::arrayGet($json, 'data.attributes.label');
        $hoursPerMonth = self::arrayGet($json, 'data.attributes.hours-per-month');
        $startDate = self::arrayGet($json, 'data.attributes.start-date');
        $endDate = self::arrayGet($json, 'data.attributes.end-date');
        $halfHoursFirstMonth = self::arrayGet($json, 'data.attributes.half-hours-first-month', false);

        $contract->type = $type;
        $contract->predecessor_id = $predecessorId;
        $contract->label = $label;
        $contract->hours_per_month = $hoursPerMonth;
        $contract->start_date = !empty($startDate) ? strtotime($startDate) : 0;
        $contract->end_date = !empty($endDate) ? strtotime($endDate) : 0;
        $contract->half_hours_first_month = $halfHoursFirstMonth;
        $contract->store();

        return $contract;
    }
}
