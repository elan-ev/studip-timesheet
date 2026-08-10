<?php
/**
 * Authority
 *
 * Main class to handle action validity checks.
 *
 * @package   StudipTimesheet\JsonApi\Routes
 * @since     0.0.1
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipTimesheet\JsonApi\Routes;

use StudipTimesheet\Models\Contract;
use StudipTimesheet\Models\Sheet;
use StudipTimesheet\Models\WorkflowLog;

use User;
use RolePersistence;

class Authority
{
    const ADMIN_ROLE = 'TimesheetPlugin_Admin';
    const INST_ADMIN_ROLE = 'TimesheetPlugin_Institute_Admin';

    private static function isRootOrAdmin(User $user, ?string $instId = null): bool
    {
        $isRoot = $GLOBALS['perm']->have_perm('root', $user->id);

        $isAdmin = RolePersistence::isAssignedRole($user->id, self::ADMIN_ROLE);

        $isInstAdmin = false;
        if (!empty($instId)) {
            $isInstAdmin = RolePersistence::isAssignedRole($user->id, self::INST_ADMIN_ROLE, $instId);
        }

        return $isRoot || $isAdmin || $isInstAdmin;
    }

    public static function canCreateContract(User $user): bool
    {
        return self::isRootOrAdmin($user);
    }

    public static function canUpdateContract(User $user): bool
    {
        return self::isRootOrAdmin($user);
    }

    public static function canDeleteContract(User $user): bool
    {
        return self::isRootOrAdmin($user);
    }

    public static function canIndexContract(User $user): bool
    {
        return self::isRootOrAdmin($user);
    }

    public static function canShowContract(User $user, Contract $resource): bool
    {
        return self::isRootOrAdmin($user) || $resource->employee_id == $user->id;
    }

    public static function canCreatePermission(User $user): bool
    {
        return self::isRootOrAdmin($user);
    }

    public static function canDeletePermission(User $user): bool
    {
        return self::isRootOrAdmin($user);
    }

    public static function canIndexPermission(User $user): bool
    {
        return self::isRootOrAdmin($user);
    }

    public static function canShowPermission(User $user): bool
    {
        return self::isRootOrAdmin($user);
    }

    public static function canUpdatePermission(User $user): bool
    {
        return self::isRootOrAdmin($user);
    }

    public static function canCreateSupervisor(User $user): bool
    {
        return self::isRootOrAdmin($user);
    }

    public static function canDeleteSupervisor(User $user): bool
    {
        return self::isRootOrAdmin($user);
    }

    public static function canIndexSupervisor(User $user): bool
    {
        return self::isRootOrAdmin($user);
    }

    public static function canShowSupervisor(User $user): bool
    {
        return self::isRootOrAdmin($user);
    }

    public static function canUpdateSupervisor(User $user): bool
    {
        return self::isRootOrAdmin($user);
    }

    public static function canIndexRecord(User $user): bool
    {
        return self::isRootOrAdmin($user);
    }

    public static function canCreateRecord(User $user, Sheet $sheet): bool
    {
        return self::isRootOrAdmin($user) || $sheet->contract->employee->id === $user->id;
    }

    public static function canDeleteRecord(User $user, Sheet $sheet): bool
    {
        return self::isRootOrAdmin($user) || $sheet->contract->employee->id === $user->id;
    }

    public static function canShowRecord(User $user, Sheet $sheet): bool
    {
        return self::isRootOrAdmin($user) || $sheet->contract->employee->id === $user->id;
    }

    public static function canUpdateRecord(User $user, Sheet $sheet): bool
    {
        return self::isRootOrAdmin($user) || $sheet->contract->employee->id === $user->id;
    }

    public static function canCreateSheet(User $user, Contract $contract): bool
    {
        return self::isRootOrAdmin($user) || $contract->employee->id === $user->id;
    }

    public static function canDeleteSheet(User $user, Contract $contract): bool
    {
        return self::isRootOrAdmin($user) || $contract->employee->id === $user->id;
    }

    public static function canShowSheet(User $user, Contract $contract): bool
    {
        return self::isRootOrAdmin($user) || $contract->employee->id === $user->id;
    }

    public static function canUpdateSheet(User $user, Contract $contract): bool
    {
        return self::isRootOrAdmin($user) || $contract->employee->id === $user->id;
    }

    public static function canIndexSheet(User $user): bool
    {
        return self::isRootOrAdmin($user);
    }

    public static function canIndexSheetRecord(User $user, Sheet $sheet): bool
    {
        return self::isRootOrAdmin($user) || $sheet->contract->employee->id === $user->id;
    }

    public static function canIndexContractSheet(User $user, Contract $contract): bool
    {
        return self::isRootOrAdmin($user) || $contract->employee->id === $user->id;
    }

    public static function canCreateWorkflowLog(User $user): bool
    {
        return self::isRootOrAdmin($user);
    }

    public static function canCreateSheetWorkflowLog(User $user, Sheet $sheet): bool
    {
        return self::isRootOrAdmin($user) || $sheet->contract->employee->id === $user->id;
    }

    public static function canDeleteWorkflowLog(User $user): bool
    {
        return self::isRootOrAdmin($user);
    }

    public static function canIndexWorkflowLogs(User $user): bool
    {
        return self::isRootOrAdmin($user);
    }

    public static function canIndexSheetWorkflowLogs(User $user, Sheet $sheet): bool
    {
        return self::isRootOrAdmin($user) || $sheet->contract->employee->id === $user->id;
    }

    public static function canShowWorkflowLog(User $user): bool
    {
        return self::isRootOrAdmin($user);
    }

    public static function canShowSheetWorkflowLog(User $user, Sheet $sheet): bool
    {
        return self::isRootOrAdmin($user) || $sheet->contract->employee->id === $user->id;
    }

    public static function canUpdateWorkflowLog(User $user, Sheet $sheet): bool
    {
        return self::isRootOrAdmin($user) || $sheet->contract->employee->id === $user->id;
    }
}
