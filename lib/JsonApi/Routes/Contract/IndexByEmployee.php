<?php
/**
 * Contract Index Route Handler
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

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipTimesheet\JsonApi\Routes\Authority;
use StudipTimesheet\JsonApi\Schemas\ContractSchema;
use StudipTimesheet\Models\Contract;

class IndexByEmployee extends JsonApiController
{
    use FilterTrait;

    /**
     * @inheritDoc
     */
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        ContractSchema::REL_INSTITUTE,
        ContractSchema::REL_SHEETS,
        ContractSchema::REL_SHEETS_RECORDS,
    ];

    /**
     * Index Contracts of an employee
     * @param Request $request
     * @param Response $response
     * @param mixed $args
     * @return void
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $user = $this->getUser($request);

        $employeeId = $args['id'] ?? null;
        $employee = \User::find($employeeId);
        if (!$employee) {
            throw new RecordNotFoundException('Employee not found.');
        }

        if (!Authority::canIndexContractsForEmployee($user, $employee)) {
            throw new AuthorizationFailedException();
        }

        [$offset, $limit] = $this->getOffsetAndLimit();

        $contracts = Contract::findBySQL(
            'employee_id = ? ORDER BY start_date DESC',
            [$employeeId]
        );
        $total = count($contracts);
        $data = array_slice($contracts, $offset, $limit);

        return $this->getPaginatedContentResponse($data, $total);
    }
}
