<?php
/**
 * Contract Index Route Handler
 *
 * @package   StudipTimesheet\JsonApi\Routes
 * @since     0.1.0
 * @author    Ron Lucke <lucke@elan-ev.de>
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

class IndexByInstitute extends JsonApiController
{
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

        $institutId = $args['id'] ?? null;
        $institute = \Institute::find($institutId);
        if (!$institute) {
            throw new RecordNotFoundException('Institute not found.');
        }
        
        if (!Authority::canIndexContractForInstitute($user, $institutId)) {
            throw new AuthorizationFailedException();
        }

        [$offset, $limit] = $this->getOffsetAndLimit();

        $contracts = Contract::findBySQL(
            'institut_id = ? ORDER BY start_date DESC',
            [$institutId]
        );
        $total = count($contracts);
        $data = array_slice($contracts, $offset, $limit);

        return $this->getPaginatedContentResponse($data, $total);
    }
}
