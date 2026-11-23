<?php

namespace StudipTimesheet\Models;

use SimpleORMap;
use User;
use Institute;

/**
 * Permission
 *
 * @package   StudipTimesheet\Models
 * @since     0.0.1
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 *
 * @property string $user_id
 * @property string $institute_id
 * @property string $role
 *
 * @property User $user
 * @property Institute $institute
 */

class Permission extends SimpleORMap
{
    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_ADMIN = 'admin';
    const ROLE_SUPERVISOR = 'supervisor';

    const ROLES = [
        self::ROLE_SUPERADMIN,
        self::ROLE_ADMIN,
        self::ROLE_SUPERVISOR,
    ];

    protected static function configure($config = [])
    {
        $config['db_table'] = 'timesheet_permissions';

        $config['belongs_to']['user'] = [
            'class_name' => User::class,
            'foreign_key' => 'user_id',
        ];

        $config['belongs_to']['institute'] = [
            'class_name' => Institute::class,
            'foreign_key' => 'institute_id',
        ];

        parent::configure($config);
    }

    public static function find($id)
    {
        [$user_id, $institute_id] = explode('_', $id);

        return self::findOneBySql('user_id = ? AND institute_id = ?', [$user_id, $institute_id]);
    }

    public static function getAll(): array
    {
        return self::findBySQL('1');
    }

    public static function hasAssignedRole($user_id, $role, $instId = null): bool
    {
        $sql = 'user_id = ? AND role = ?';
        $params = [$user_id, $role];

        if ($instId !== null) {
            $sql .= ' AND institute_id = ?';
            $params[] = $instId;
        }

        return self::countBySql($sql, $params) > 0;
    }

    public static function hasPermission($user): bool
    {
        return self::countBySql('user_id = ?', [$user->id]) > 0;
    }
}
