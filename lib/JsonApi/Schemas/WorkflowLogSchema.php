<?php
/**
 * Workflow Logs JSON API Schema
 *
 * @package   StudipTimesheet\JsonApi\Schemas
 * @since     0.0.1
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipTimesheet\JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;
use StudipTimesheet\Models\WorkflowLog;

class WorkflowLogSchema extends \JsonApi\Schemas\SchemaProvider
{
    /**
     * Type of schema.
     * {@inheritdoc}
     */
    public const TYPE = 'timesheet-workflow-logs';

    /**
     * Resource Type.
     * {@inheritdoc}
     */
    protected string $resourceType = self::TYPE;

    const REL_SHEET = 'sheet';
    const REL_USER = 'user';

    /**
     * {@inheritdoc}
     */
    public function getId($resource): ?string
    {
        return $resource->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes($resource, ContextInterface $context): iterable
    {
        return [
            'action' => $resource['action'],
            'object-type' => $resource['object_type'],
            'comment' => $resource['comment'],
            'log-data' => $resource['log_data'],
            'date' => $resource['mkdate'] ? date('Y-m-d H:i:s', $resource['mkdate']) : null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        // The method "getRelationshipBuilder" is available from StudIP v6.3.
        if (method_exists($this, 'getRelationshipBuilder')) {
            $builder = $this->getRelationshipBuilder($resource, $context);
        } else { // Otherwise, we use the local builder.
            $builder = new RelationshipBuilder($this, $resource, $context);
        }

        $builder->addRelationship(self::REL_SHEET, 'sheet');
        $builder->addRelationship(self::REL_USER, 'user');

        return $builder->getRelationships();
    }

    /**
     * @inheritdoc
     */
    public function hasResourceMeta($resource): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceMeta($resource)
    {
        return [
            'actions' => WorkflowLog::ACTIONS,
        ];
    }
}
