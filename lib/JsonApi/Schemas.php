<?php
/**
 * Schemas trait
 *
 * Registers JSON-API Schemas for StudipTimesheet models.
 *
 * @package   StudipTimesheet\JsonApi
 * @since     0.0.1
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipTimesheet\JsonApi;

trait Schemas
{
    public function registerSchemas(): array
    {
        return [
            \StudipTimesheet\Models\Contract::class => \StudipTimesheet\JsonApi\Schemas\ContractSchema::class,
            \StudipTimesheet\Models\Permission::class => \StudipTimesheet\JsonApi\Schemas\PermissionSchema::class,
            \StudipTimesheet\Models\Record::class => \StudipTimesheet\JsonApi\Schemas\RecordSchema::class,
            \StudipTimesheet\Models\Sheet::class => \StudipTimesheet\JsonApi\Schemas\SheetSchema::class,
            \StudipTimesheet\Models\Supervisor::class => \StudipTimesheet\JsonApi\Schemas\SupervisorSchema::class,
            \StudipTimesheet\Models\WorkflowLog::class => \StudipTimesheet\JsonApi\Schemas\WorkflowLogSchema::class,
        ];
    }
}
