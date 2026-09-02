<?php

namespace StudipTimesheet\Models;

use SimpleORMap;
use User;
use Institute;

/**
 * Contract
 *
 * @package   StudipTimesheet\Models
 * @since     0.0.1
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 *
 * @property int $id
 * @property string $employee_id
 * @property string $institute_id
 * @property string $type
 * @property int $predecessor_id
 * @property string $label
 * @property int $hours_per_month
 * @property int $start_date
 * @property int $end_date
 * @property bool $half_hours_first_month
 * @property bool $half_hours_last_month
 *
 * @property User $employee
 * @property Institute $institute
 * @property Contract $predecessor
 */

class Contract extends SimpleORMap
{
    const TYPE_NEW = 'new';
    const TYPE_EXTENSION = 'extension';
    const TYPE_REHIRE = 'rehire';

    const TYPES = [
        self::TYPE_NEW,
        self::TYPE_EXTENSION,
        self::TYPE_REHIRE,
    ];

    protected static function configure($config = [])
    {
        $config['db_table'] = 'timesheet_contracts';

        $config['belongs_to']['employee'] = [
            'class_name'  => User::class,
            'foreign_key' => 'employee_id',
        ];

        $config['belongs_to']['institute'] = [
            'class_name'  => Institute::class,
            'foreign_key' => 'institute_id',
        ];

        $config['has_one']['predecessor'] = [
            'class_name'  => Contract::class,
            'foreign_key' => 'predecessor_id',
        ];

        $config['has_many']['sheets'] = [
            'class_name'        => Sheet::class,
            'assoc_foreign_key' => 'contract_id',
            'on_delete'         => 'delete',
        ];

        $config['has_many']['supervisors'] = [
            'class_name'        => Supervisor::class,
            'assoc_foreign_key' => 'contract_id',
            'on_delete'         => 'delete',
        ];

        parent::configure($config);
    }

    public static function getAll(): array
    {
        return self::findBySQL('1');
    }
}
