<?php
/**
 * Record JSON API Schema
 *
 * @package   StudipTimesheet\JsonApi\Schemas
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipTimesheet\JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;
use StudipTimesheet\Models\Record;

class RecordSchema extends \JsonApi\Schemas\SchemaProvider
{
    /**
     * Type of schema.
     * {@inheritdoc}
     */
    public const TYPE = 'timesheet-records';

    /**
     * Resource Type.
     * {@inheritdoc}
     */
    protected string $resourceType = self::TYPE;

    const REL_SHEET = 'sheet';

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
            'sheet-id' => $resource->sheet_id,
            'date' => $resource['date'] ? date('Y-m-d', $resource['date']) : null,
            'start-time' => $resource['start_time'] ? date('H:i:s', $resource['start_time']) : null,
            'end-time' => $resource['end_time'] ? date('H:i:s', $resource['end_time']) : null,
            'break-start' => $resource['break_start'] ? date('H:i:s', $resource['break_start']) : null,
            'break-duration' => (int) $resource['break_duration'],
            'absence-type' => $resource['absence_type'],
            'comment' => $resource['comment'],
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

        return $builder->getRelationships();
        // $relationships = [];

        // $relationships = $this->addSheetRelationship(
        //     $relationships,
        //     $resource,
        //     $this->shouldInclude($context, self::REL_SHEET)
        // );

        // return $relationships;
    }

    private function addSheetRelationship(array $relationships, mixed $resource, bool $includeData): array
    {
        $sheet = $resource->sheet;
        $relation = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->createLinkToResource($sheet),
            ],
        ];

        if ($includeData) {
            $relation[self::RELATIONSHIP_DATA] = $sheet;
        }

        $relationships[self::REL_SHEET] = $relation;

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
            'absence-types' => Record::ABSENCE_TYPES,
        ];
    }
}
