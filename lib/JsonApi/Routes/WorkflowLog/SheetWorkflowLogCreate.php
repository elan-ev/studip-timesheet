<?php
/**
 * Sheet Workflow Log Create Route Handler
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
use JsonApi\Routes\ValidationTrait;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipTimesheet\JsonApi\Routes\Authority;
use StudipTimesheet\JsonApi\Schemas\WorkflowLogSchema;
use StudipTimesheet\Models\WorkflowLog;
use StudipTimesheet\Models\Sheet;

class SheetWorkflowLogCreate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        WorkflowLogSchema::REL_SHEET,
        WorkflowLogSchema::REL_USER,
    ];

    /**
     * Create Workflow log.
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

        $sheet = Sheet::find($args['id']);
        if (!$sheet) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canCreateSheetWorkflowLog($user, $sheet)) {
            throw new AuthorizationFailedException();
        }

        $workflowLog = $this->createWorkflowLog($json, $sheet);

        return $this->getCreatedResponse($workflowLog);
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
        if (!self::arrayHas($json, 'data.attributes.action')) {
            return 'Missing `action` member of attributes block.';
        }
        $action = self::arrayGet($json, 'data.attributes.action');
        if (!empty($action) && !in_array($action, WorkflowLog::ACTIONS)) {
            return 'Invalid value for attribute `action`.';
        }
    }

    /**
     * Extract data and creates Workflow Log.
     * @param array $json
     * @return WorkflowLog
     */
    private function createWorkflowLog(array $json, Sheet $sheet)
    {
        $userId = self::arrayGet($json, 'data.attributes.user-id');
        $action = self::arrayGet($json, 'data.attributes.action');
        $comment = self::arrayGet($json, 'data.attributes.comment');

        $workflowLog = new WorkflowLog();
        $workflowLog->user_id = $userId;
        $workflowLog->sheet_id = $sheet->id;
        $workflowLog->action = $action;
        $workflowLog->performed_at = time();
        $workflowLog->comment = $comment;
        $workflowLog->store();

        return $workflowLog;
    }
}
