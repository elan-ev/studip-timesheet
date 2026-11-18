<?php
/**
 * User Permission Index Route Handler
 *
 * @package    StudipTimesheet\JsonApi\Routes
 * @since      0.1.0
 * @author     Ron Lucke <lucke@elan-ev.de>
 * @copyright  2026 elan e.V.
 * @license    GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link       https://elan-ev.de
 */

namespace StudipTimesheet\JsonApi\Routes\Permission;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipTimesheet\JsonApi\Routes\Authority;
use StudipTimesheet\JsonApi\Schemas\PermissionSchema;
use StudipTimesheet\Models\Permission;

class IndexByUser extends JsonApiController
{
    /**
     * @inheritDoc
     */
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        PermissionSchema::REL_INSTITUTE,
    ];

    /**
     * Index Permissions by User.
     * @param Request $request
     * @param Response $response
     * @param mixed $args
     * @return void
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $request_user = $this->getUser($request);
        $userId = $args['id'];

        if (!Authority::canIndexPermissionByUser($request_user, $userId)) {
            throw new AuthorizationFailedException();
        }
        
        [$offset, $limit] = $this->getOffsetAndLimit();

        $permissions = Permission::findBySql('user_id = ?', [$userId]);
        $total = count($permissions);
        $data = array_slice($permissions, $offset, $limit);

        return $this->getPaginatedContentResponse($data, $total);
    }
}