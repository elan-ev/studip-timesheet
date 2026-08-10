<?php
/**
 * Supervisor JSON API Schema
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

class SupervisorSchema extends \JsonApi\Schemas\SchemaProvider
{
    /**
     * Type of schema.
     * {@inheritdoc}
     */
    public const TYPE = 'timesheet-contract-supervisors';

    /**
     * Resource Type.
     * {@inheritdoc}
     */
    protected string $resourceType = self::TYPE;

    const REL_CONTRACT = 'contract';
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
            'contract-id' => (int) $resource['contract_id'],
            'user-id' => (int) $resource['user_id'],
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
        $builder->addRelationship(self::REL_USER, 'user');

        return $builder->getRelationships();
        // $relationships = [];

        // $relationships = $this->addUserRelationship(
        //     $relationships,
        //     $resource,
        //     $this->shouldInclude($context, self::REL_USER)
        // );

        // $relationships = $this->addContractRelationship(
        //     $relationships,
        //     $resource,
        //     $this->shouldInclude($context, self::REL_CONTRACT)
        // );

        // return $relationships;
    }

    private function addUserRelationship(array $relationships, mixed $resource, bool $includeData): array
    {
        $user = $resource->user;
        $relation = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->createLinkToResource($user),
            ],
        ];

        if ($includeData) {
            $relation[self::RELATIONSHIP_DATA] = $user;
        }

        $relationships[self::REL_USER] = $relation;

        return $relationships;
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
}
