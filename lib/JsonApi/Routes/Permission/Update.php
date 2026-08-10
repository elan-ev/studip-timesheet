<?php
/**
 * Permission Update Route Handler
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
use JsonApi\Routes\ValidationTrait;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipTimesheet\JsonApi\Routes\Authority;
use StudipTimesheet\JsonApi\Schemas\PermissionSchema;
use StudipTimesheet\Models\Permission;

class Update extends JsonApiController
{
    use ValidationTrait;

    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        PermissionSchema::REL_USER,
        PermissionSchema::REL_INSTITUTE,
    ];

    /**
     * Update Permission.
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

        if (!Authority::canUpdatePermission($user)) {
            throw new AuthorizationFailedException();
        }

        $permission = Permission::find($args['id']);
        if (!$permission) {
            throw new RecordNotFoundException();
        }

        $permission = $this->updatePermission($json, $permission);

        return $this->getCreatedResponse($permission);
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
        if (!self::arrayHas($json, 'data.attributes.role')) {
            return 'Missing `type` member of attributes block.';
        }
        $role = self::arrayGet($json, 'data.attributes.role');
        if (!in_array($role, Permission::ROLES)) {
            return 'Invalid value for attribute `role`.';
        }
    }

    /**
     * Extract data and updates permission.
     * @param array $json
     * @return Permission
     */
    private function updatePermission(array $json, Permission $permission)
    {
        $role = self::arrayGet($json, 'data.attributes.role');

        $permission->role = $role;
        $permission->store();

        return $permission;
    }
}
