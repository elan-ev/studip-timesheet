<?php
/**
 * Sheet Workflow Log Show Route Handler
 *
 * @package   StudipTimesheet\JsonApi\Routes
 * @since     0.0.1
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipTimesheet\JsonApi\Routes\WorkflowLog;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipTimesheet\JsonApi\Routes\Authority;
use StudipTimesheet\JsonApi\Schemas\WorkflowLogSchema;
use StudipTimesheet\Models\Sheet;

class SheetWorkflowLogShow extends JsonApiController
{
    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        WorkflowLogSchema::REL_SHEET,
        WorkflowLogSchema::REL_USER,
    ];

    /**
     * Show a workflow Log.
     * @param Request $request
     * @param Response $response
     * @param mixed $args
     * @return void
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $user = $this->getUser($request);

        $sheet = Sheet::find($args['sheet_id']);
        if (!$sheet) {
            throw new RecordNotFoundException();
        }

        $workflowLog = $sheet->workflow_logs->find($args['id']);
        if (!$workflowLog) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowSheetWorkflowLog($user, $sheet)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($workflowLog);
    }
}
