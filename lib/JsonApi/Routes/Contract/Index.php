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
use JsonApi\JsonApiController;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipTimesheet\JsonApi\Routes\Authority;
use StudipTimesheet\JsonApi\Schemas\ContractSchema;
use StudipTimesheet\Models\Contract;

class Index extends JsonApiController
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
        ContractSchema::REL_EMPLOYEE,
        ContractSchema::REL_INSTITUTE,
        ContractSchema::REL_PREDECESSOR,
    ];

    protected $allowedFilteringParameters = [
        'search',
        'type',
        'employee_id',
        'institute_id',
        'start_date',
        'end_date',
        'hours_per_month',
        'predecessor_id',
    ];

    /**
     * Index Contracts.
     * @param Request $request
     * @param Response $response
     * @param mixed $args
     * @return void
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $user = $this->getUser($request);

        if (!Authority::canIndexContract($user)) {
            throw new AuthorizationFailedException();
        }

        $this->validateFilters();
        $filters = $this->getFilters();

        [$offset, $limit] = $this->getOffsetAndLimit();

        [$whereClause, $params] = $this->buildWhereClause($filters);

        $contracts = Contract::findBySQL("$whereClause ORDER BY start_date DESC", $params);

        $total = count($contracts);
        $data = array_slice($contracts, $offset, $limit);

        return $this->getPaginatedContentResponse($data, $total);
    }
}
