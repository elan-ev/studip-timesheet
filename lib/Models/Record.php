<?php

namespace StudipTimesheet\Models;

use SimpleORMap;

/**
 * Record
 *
 * @package   StudipTimesheet\Models
 * @since     0.0.1
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 *
 * @property int $id
 * @property int $sheet_id
 * @property int $date
 * @property int $start_time
 * @property int $end_time
 * @property int $break_duration
 * @property string $absence_type
 * @property string $comment
 *
 * @property Sheet $sheet
 */

class Record extends SimpleORMap
{
    const ABSENCE_TYPE_MATERNITY = 'maternity';
    const ABSENCE_TYPE_HOLIDAY = 'holiday';
    const ABSENCE_TYPE_VACATION = 'vacation';
    const ABSENCE_TYPE_SICK = 'sick';
    const ABSENCE_TYPE_WORK = 'work';

    const ABSENCE_TYPES = [
        self::ABSENCE_TYPE_MATERNITY,
        self::ABSENCE_TYPE_HOLIDAY,
        self::ABSENCE_TYPE_VACATION,
        self::ABSENCE_TYPE_SICK,
        self::ABSENCE_TYPE_WORK,
    ];

    protected static function configure($config = [])
    {
        $config['db_table'] = 'timesheet_records';

        $config['belongs_to']['sheet'] = [
            'class_name'  => Sheet::class,
            'foreign_key' => 'sheet_id',
        ];

        parent::configure($config);
    }

    public static function getAll(): array
    {
        return self::findBySQL('1');
    }
}
