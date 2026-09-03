<?php
/**
 * Sheet Delete Route Handler
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

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipTimesheet\JsonApi\Routes\Authority;
use StudipTimesheet\Models\Sheet;

class Delete extends JsonApiController
{
    /**
     * Delete Sheet.
     * @param Request $request
     * @param Response $response
     * @param mixed $args
     * @return Response
     * @throws AuthorizationFailedException
     * @throws RecordNotFoundException
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $user = $this->getUser($request);

        $sheet = Sheet::find($args['id']);
        if (!$sheet) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canDeleteSheet($user, $sheet->contract)) {
            throw new AuthorizationFailedException();
        }

        $sheet->deleted_at = time();
        $sheet->deleted_by = $user->id;

        $sheet->status = Sheet::STATUS_ARCHIVED;

        // TODO: Log this action! -> timesheet_workflow_logs
    
        $sheet->store();

        return $this->getCodeResponse(204);
    }
}
