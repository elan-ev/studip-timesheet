<?php

namespace StudipTimesheet\Models;

use SimpleORMap;
use User;

/**
 * Supervisor
 *
 * @package   StudipTimesheet\Models
 * @since     0.0.1
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 *
 * @property int $contract_id
 * @property string $user_id
 *
 * @property User $user
 * @property Contract $contract
 */

class Supervisor extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'timesheet_contract_supervisors';

        $config['belongs_to']['user'] = [
            'class_name' => User::class,
            'foreign_key' => 'user_id',
            'on_delete' => 'delete',
        ];

        $config['belongs_to']['contract'] = [
            'class_name' => Contract::class,
            'foreign_key' => 'contract_id',
            'on_delete' => 'delete',
        ];

        parent::configure($config);
    }

    public static function getAll(): array
    {
        return self::findBySQL('1');
    }

    public static function userIsSupervisor($user_id, $contract_id = null): bool
    {
        $sql = 'user_id = ?';
        $params = [$user_id];

        if ($contract_id !== null) {
            $sql .= ' AND contract_id = ?';
            $params[] = $contract_id;
        }

        return self::countBySql($sql, $params) > 0;
    }

    public static function getForContract($contract_id): array
    {
        return self::findBySQL('contract_id = ?', [$contract_id]);
    }
}
