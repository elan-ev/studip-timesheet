<?php
/**
 * Sheet JSON API Schema
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
use StudipTimesheet\Models\Sheet;

class SheetSchema extends \JsonApi\Schemas\SchemaProvider
{
    /**
     * Type of schema.
     * {@inheritdoc}
     */
    public const TYPE = 'timesheet-sheets';

    /**
     * Resource Type.
     * {@inheritdoc}
     */
    protected string $resourceType = self::TYPE;

    const REL_CONTRACT = 'contract';

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
            'year' => (int) $resource['year'],
            'month' => (int) $resource['month'],
            'status' => $resource['status'],
            'is-suspended' => (bool) $resource['is_suspended'],
            'workflow-config' => $resource['workflow_config']->getArrayCopy(),
            'deleted-at' => $resource['deleted_at'] ? date('Y-m-d H:i:s', $resource['deleted_at']) : null,
            'deleted-by' => $resource['deleted_by'] ?? null,
            'frozen-hours-per-month' => (int) $resource['frozen_hours_per_month'],
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

        $builder->addRelationship(self::REL_CONTRACT, 'contract');

        return $builder->getRelationships();

        // $relationships = [];

        // $relationships = $this->addContractRelationship(
        //     $relationships,
        //     $resource,
        //     $this->shouldInclude($context, self::REL_CONTRACT)
        // );

        // return $relationships;
    }

    private function addContractRelationship(array $relationships, mixed $resource, bool $includeData): array
    {
        $contract = $resource->contract;
        $relation = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->createLinkToResource($contract),
            ],
        ];

        if ($includeData) {
            $relation[self::RELATIONSHIP_DATA] = $contract;
        }

        $relationships[self::REL_CONTRACT] = $relation;

        return $relationships;
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
            'statuses' => Sheet::STATUSES,
        ];
    }
}
