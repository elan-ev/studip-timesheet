<?php
/**
 * Permission Show Route Handler
 *
 * @package   StudipTimesheet\JsonApi\Routes
 * @since     0.0.1
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipTimesheet\JsonApi\Routes\Permission;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipTimesheet\JsonApi\Routes\Authority;
use StudipTimesheet\JsonApi\Schemas\PermissionSchema;
use StudipTimesheet\Models\Permission;

class Show extends JsonApiController
{
    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        PermissionSchema::REL_USER,
        PermissionSchema::REL_INSTITUTE,
    ];

    /**
     * Show a Permission.
     * @param Request $request
     * @param Response $response
     * @param mixed $args
     * @return void
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $requestUser = $this->getUser($request);

        if (!Authority::canShowPermission($requestUser)) {
            throw new AuthorizationFailedException();
        }

        $permission = Permission::find($args['id']);
        if (!$permission) {
            throw new RecordNotFoundException();
        }
        
        if (!$permission->institute) {
            throw new RecordNotFoundException();
        }

        return $this->getContentResponse($permission);
    }
}
