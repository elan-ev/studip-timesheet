<?php

namespace StudipTimesheet\Models;

use SimpleORMap;
use JSONArrayObject;

/**
 * Sheet
 *
 * @package   StudipTimesheet\Models
 * @since     0.0.1
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 *
 * @property int $id
 * @property int $contract_id
 * @property int $year
 * @property int $month
 * @property string $status
 * @property bool $is_suspended
 * @property JSONArrayObject $workflow_config
 * @property int $deleted_at
 * @property string $deleted_by
 * @property int $frozen_hours_per_month
 *
 * @property Contract $contract
 */

class Sheet extends SimpleORMap
{
    const STATUS_OPEN = 'open';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_APPROVED_CONFIRM = 'approved_confirm';
    const STATUS_APPROVED_FINAL = 'approved_final';
    const STATUS_ARCHIVED = 'archived';

    const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_SUBMITTED,
        self::STATUS_APPROVED_CONFIRM,
        self::STATUS_APPROVED_FINAL,
        self::STATUS_ARCHIVED,
    ];

    protected static function configure($config = [])
    {
        $config['db_table'] = 'timesheet_sheets';

        $config['serialized_fields']['workflow_config'] = JSONArrayObject::class;

        $config['belongs_to']['contract'] = [
            'class_name'  => Contract::class,
            'foreign_key' => 'contract_id',
        ];

        $config['has_many']['records'] = [
            'class_name'        => Record::class,
            'assoc_foreign_key' => 'sheet_id',
            'on_delete'         => 'delete',
        ];

        $config['has_many']['workflow_logs'] = [
            'class_name'        => WorkflowLog::class,
            'assoc_foreign_key' => 'sheet_id',
            'on_delete'         => 'delete',
        ];

        parent::configure($config);
    }

    public static function getAll(): array
    {
        return self::findBySQL('1');
    }
}
