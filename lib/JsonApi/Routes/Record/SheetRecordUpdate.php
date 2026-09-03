<?php
/**
 * Sheet Record Update Route Handler
 *
 * @package   StudipTimesheet\JsonApi\Routes
 * @since     0.0.1
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipTimesheet\JsonApi\Routes\Record;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipTimesheet\JsonApi\Routes\Authority;
use StudipTimesheet\JsonApi\Schemas\RecordSchema;
use StudipTimesheet\Models\Record;
use StudipTimesheet\Models\Sheet;
use StudipTimesheet\Helpers\MiscHelper;

class SheetRecordUpdate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        RecordSchema::REL_SHEET,
    ];

    /**
     * Update Record.
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

        $sheet = Sheet::find($args['sheet_id']);
        if (!$sheet) {
            throw new RecordNotFoundException();
        }

        $record = $sheet->records->find($args['id']);
        if (!$record) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canUpdateRecord($user, $sheet)) {
            throw new AuthorizationFailedException();
        }

        // TODO: Log this action! -> timesheet_workflow_logs

        $record = $this->updateRecord($json, $record);

        return $this->getCreatedResponse($record);
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
        if (!self::arrayHas($json, 'data.attributes.date')) {
            return 'Missing `date` member of attributes block.';
        }
        if (!self::arrayHas($json, 'data.attributes.start-time')) {
            return 'Missing `start-time` member of attributes block.';
        }
        if (!self::arrayHas($json, 'data.attributes.end-time')) {
            return 'Missing `end-time` member of attributes block.';
        }
        if (!self::arrayHas($json, 'data.attributes.break-start')) {
            return 'Missing `break-start` member of attributes block.';
        }
        if (!self::arrayHas($json, 'data.attributes.break-duration')) {
            return 'Missing `break-duration` member of attributes block.';
        }

        // Check the absence type if exists.
        $absenceType = self::arrayGet($json, 'data.attributes.absence-type');
        if (!empty($absenceType) && !in_array($absenceType, Record::ABSENCE_TYPES)) {
            return 'Invalid value for attribute `absence-type`.';
        }
    }

    /**
     * Extract data and updates record.
     * @param array $json
     * @return Record
     */
    private function updateRecord(array $json, Record $record)
    {
        $date = self::arrayGet($json, 'data.attributes.date');
        $startTime = self::arrayGet($json, 'data.attributes.start-time');
        $endTime = self::arrayGet($json, 'data.attributes.end-time');
        $breakStart = self::arrayGet($json, 'data.attributes.break-start');
        $breakDuration = self::arrayGet($json, 'data.attributes.break-duration', 0);
        $absenceType = self::arrayGet($json, 'data.attributes.absence-type');
        $comment = self::arrayGet($json, 'data.attributes.comment');

        $startDatetime = "$date $startTime";
        $endDatetime = "$date $endTime";

        $record->date = MiscHelper::datetimeStringToTimestamp($date, 0,0,1);
        $record->start_time = MiscHelper::datetimeStringToTimestamp($startDatetime);
        $record->end_time = MiscHelper::datetimeStringToTimestamp($endDatetime);
        $record->break_start = MiscHelper::datetimeStringToTimestamp($breakStart);
        $record->break_duration = $breakDuration;
        $record->absence_type = $absenceType;
        $record->comment = $comment;
        $record->store();

        return $record;
    }
}
