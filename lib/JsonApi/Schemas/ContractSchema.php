<?php
/**
 * Contract JSON API Schema
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
use StudipTimesheet\Models\Contract;

class ContractSchema extends \JsonApi\Schemas\SchemaProvider
{
    /**
     * Type of schema.
     * {@inheritdoc}
     */
    public const TYPE = 'timesheet-contracts';

    /**
     * Resource Type.
     * {@inheritdoc}
     */
    protected string $resourceType = self::TYPE;

    const REL_EMPLOYEE = 'employee';
    const REL_INSTITUTE = 'institute';
    const REL_PREDECESSOR = 'predecessor';

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
            'type'  => $resource['type'],
            'label' => $resource['label'],
            'hours-per-month' => (int) $resource['hours_per_month'],
            'start-date' => $resource['start_date'] ? date('Y-m-d', $resource['start_date']) : null,
            'end-date' => $resource['end_date'] ? date('Y-m-d', $resource['end_date']) : null,
            'half-hours-first-month' => (bool) $resource['half_hours_first_month'],
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

        $builder->addRelationship(self::REL_EMPLOYEE, 'employee');
        $builder->addRelationship(self::REL_INSTITUTE, 'institute');
        if ($resource->predecessor) {
            $builder->addRelationshipData(self::REL_PREDECESSOR, 'predecessor');
        }

        return $builder->getRelationships();
        // $relationships = [];

        // $relationships = $this->addEmployeeRelationship(
        //     $relationships,
        //     $resource,
        //     $this->shouldInclude($context, self::REL_EMPLOYEE)
        // );

        // $relationships = $this->addInstituteRelationship(
        //     $relationships,
        //     $resource,
        //     $this->shouldInclude($context, self::REL_INSTITUTE)
        // );

        // $relationships = $this->addPredecessorRelationship(
        //     $relationships,
        //     $resource,
        //     $this->shouldInclude($context, self::REL_PREDECESSOR)
        // );

        // return $relationships;
    }

    private function addEmployeeRelationship(array $relationships, mixed $resource, bool $includeData): array
    {
        $employee = $resource->employee;
        $relation = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->createLinkToResource($employee),
            ],
        ];

        if ($includeData) {
            $relation[self::RELATIONSHIP_DATA] = $employee;
        }

        $relationships[self::REL_EMPLOYEE] = $relation;

        return $relationships;
    }

    private function addInstituteRelationship(array $relationships, mixed $resource, bool $includeData): array
    {
        $institute = $resource->institute;
        $relation = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->createLinkToResource($institute),
            ],
        ];

        if ($includeData) {
            $relation[self::RELATIONSHIP_DATA] = $institute;
        }

        $relationships[self::REL_INSTITUTE] = $relation;

        return $relationships;
    }

    private function addPredecessorRelationship(array $relationships, mixed $resource, bool $includeData): array
    {
        $predecessor = $resource->predecessor;
        $relation = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->createLinkToResource($predecessor),
            ],
        ];

        if ($includeData) {
            $relation[self::RELATIONSHIP_DATA] = $predecessor;
        }

        $relationships[self::REL_PREDECESSOR] = $relation;

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
            'types' => Contract::TYPES,
        ];
    }
}
