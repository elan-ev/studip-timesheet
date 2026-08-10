<?php

namespace StudipTimesheet\Models;

use SimpleORMap;
use User;

/**
 * WorkflowLog
 *
 * @package   StudipTimesheet\Models
 * @since     0.0.1
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 *
 * @property int $id
 * @property int $sheet_id
 * @property string $user_id
 * @property string $action
 * @property int $performed_at
 * @property string $comment
 *
 * @property Sheet $sheet
 * @property User $user
 */

class WorkflowLog extends SimpleORMap
{
    const ACTION_MANUAL_UNLOCK = 'manual_unlock';
    const ACTION_SYSTEM_OVERRIDE = 'system_override';
    const ACTION_UNSUSPEND = 'unsuspend';
    const ACTION_SOFT_DELETE = 'soft_delete';
    const ACTION_REJECT = 'reject';
    const ACTION_APPROVED_FINAL = 'approve_final';
    const ACTION_APPROVED_CONFIRM = 'approved_confirm';
    const ACTION_SUBMIT = 'submit';

    const ACTIONS = [
        self::ACTION_MANUAL_UNLOCK,
        self::ACTION_SYSTEM_OVERRIDE,
        self::ACTION_UNSUSPEND,
        self::ACTION_SOFT_DELETE,
        self::ACTION_REJECT,
        self::ACTION_APPROVED_FINAL,
        self::ACTION_APPROVED_CONFIRM,
        self::ACTION_SUBMIT,
    ];

    protected static function configure($config = [])
    {
        $config['db_table'] = 'timesheet_workflow_logs';

        $config['belongs_to']['sheet'] = [
            'class_name'  => Sheet::class,
            'foreign_key' => 'sheet_id',
        ];

        $config['belongs_to']['user'] = [
            'class_name'  => User::class,
            'foreign_key' => 'user_id',
        ];

        parent::configure($config);
    }

    public static function getAll(): array
    {
        return self::findBySQL('1');
    }
}
