<?php
/**
 * Sheet Update Route Handler
 *
 * @package   StudipTimesheet\JsonApi\Routes
 * @since     0.0.1
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipTimesheet\JsonApi\Routes\Sheet;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipTimesheet\JsonApi\Routes\Authority;
use StudipTimesheet\JsonApi\Schemas\SheetSchema;
use StudipTimesheet\Models\Sheet;

class Update extends JsonApiController
{
    use ValidationTrait;

    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        SheetSchema::REL_CONTRACT,
    ];

    /**
     * Create Sheet.
     * @param Request $request
     * @param Response $response
     * @param mixed $args
     * @return Response
     * @throws AuthorizationFailedException
     * @throws RecordNotFoundException
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        $user = $this->getUser($request);

        $sheet = Sheet::find($args['id']);
        if (!$sheet) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canUpdateSheet($user, $sheet->contract)) {
            throw new AuthorizationFailedException();
        }

        $sheet = $this->updateSheet($json, $sheet);

        return $this->getCreatedResponse($sheet);
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
        if (!self::arrayHas($json, 'data.attributes.year')) {
            return 'Missing `year` member of attributes block.';
        }
        if (!self::arrayHas($json, 'data.attributes.month')) {
            return 'Missing `month` member of attributes block.';
        }
        if (!self::arrayHas($json, 'data.attributes.status')) {
            return 'Missing `status` member of attributes block.';
        }
        $status = self::arrayGet($json, 'data.attributes.status');
        if (!empty($status) && !in_array($status, Sheet::STATUSES)) {
            return 'Invalid value for attribute `status`.';
        }
        if (!self::arrayHas($json, 'data.attributes.workflow-config')) {
            return 'Missing `workflow-config` member of attributes block.';
        }
        $workflowConfig = self::arrayGet($json, 'data.attributes.workflow-config', []);
        if (empty($workflowConfig)) {
            return '`workflow-config` is required.';
        }
        if (!self::arrayHas($json, 'data.attributes.frozen-hours-per-month')) {
            return 'Missing `frozen-hours-per-month` member of attributes block.';
        }
    }

    /**
     * Extract data and updates sheet.
     * @param array $json
     * @param Sheet $sheet
     * @return Sheet
     */
    private function updateSheet(array $json, Sheet $sheet)
    {
        $year = self::arrayGet($json, 'data.attributes.year');
        $month = self::arrayGet($json, 'data.attributes.month');
        $status = self::arrayGet($json, 'data.attributes.status', Sheet::STATUS_OPEN);
        $workflowConfig = self::arrayGet($json, 'data.attributes.workflow-config', []);
        $isSuspended = self::arrayGet($json, 'data.attributes.is-suspended', false);
        $frozenHoursPerMonth = self::arrayGet($json, 'data.attributes.frozen-hours-per-month', 0);

        $sheet->year = $year;
        $sheet->month = $month;
        $sheet->status = $status;
        $sheet->workflow_config = $workflowConfig;
        $sheet->is_suspended = $isSuspended;
        $sheet->frozen_hours_per_month = $frozenHoursPerMonth;
        $sheet->store();

        return $sheet;
    }
}
