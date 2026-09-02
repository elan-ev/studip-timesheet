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
use StudipTimesheet\Models\Permission;
use StudipTimesheet\Models\Supervisor;

use User;

class Authority
{
    private static function isGlobalAdmin(User $user): bool
    {
        $isRoot = $GLOBALS['perm']->have_perm('root', $user->id);
        $isAdmin = Permission::hasAssignedRole($user->id, Permission::ROLE_SUPERADMIN);

        return $isRoot || $isAdmin;
    }

    private static function isAdmin(User $user, string $instId): bool
    {
        $isGlobalAdmin = self::isGlobalAdmin($user);

        $isInstAdmin = false;
        if (!empty($instId)) {
            $isInstAdmin = Permission::hasAssignedRole($user->id, Permission::ROLE_ADMIN, $instId);
        }

        return  $isGlobalAdmin || $isInstAdmin;
    }

    private static function isSupervisor(User $user, ?string $instId = null, ?string $contractId = null )
    {

        $isContractSupervisor = $contractId ?? Supervisor::userIsSupervisor($contractId);
        $isInstSupervisor = $instId ?? Permission::hasAssignedRole($user->id, Permission::ROLE_SUPERVISOR, $instId);

        return $isContractSupervisor || $isInstSupervisor;
    }

    public static function canCreateContract(User $user, string $instId): bool
    {
        return self::isAdmin($user, $instId);
    }

    public static function canUpdateContract(User $user, string $instId): bool
    {
        return self::isAdmin($user, $instId);
    }

    public static function canDeleteContract(User $user, string $instId): bool
    {
        return self::isAdmin($user, $instId);
    }

    public static function canIndexContract(User $user): bool
    {
        return self::isGlobalAdmin($user);
    }

    public static function canIndexContractForInstitute(User $user, string $instId): bool
    {
        return self::isAdmin($user, $instId);
    }

    public static function canShowContract(User $user, Contract $resource): bool
    {
        return self::isAdmin($user, $resource->institute_id) || $resource->employee_id == $user->id;
    }

    public static function canCreatePermission(User $user): bool
    {
        return $GLOBALS['perm']->have_perm('root', $user->id);
    }

    public static function canDeletePermission(User $user): bool
    {
        return $GLOBALS['perm']->have_perm('root', $user->id);
    }

    public static function canIndexPermission(User $user): bool
    {
        return $GLOBALS['perm']->have_perm('root', $user->id);
    }

    public static function canShowPermission(User $user): bool
    {
        return $GLOBALS['perm']->have_perm('root', $user->id);
    }

    public static function canUpdatePermission(User $user): bool
    {
        return $GLOBALS['perm']->have_perm('root', $user->id);
    }

    public static function canCreateSupervisor(User $user, Contract $contract): bool
    {
        return self::isAdmin($user, $contract->institute_id);
    }

    public static function canDeleteSupervisor(User $user, Contract $contract): bool
    {
        return self::isAdmin($user, $contract->institute_id);
    }

    public static function canIndexSupervisor(User $user): bool
    {
        return self::isGlobalAdmin($user);
    }

    public static function canShowSupervisor(User $user, Contract $contract): bool
    {
        return self::isAdmin($user, $contract->institute_id);
    }

    public static function canUpdateSupervisor(User $user, string $instId): bool
    {
        return self::isAdmin($user, $instId);
    }

    public static function canIndexRecord(User $user): bool
    {
        return self::isGlobalAdmin($user);
    }

    public static function canCreateRecord(User $user, Sheet $sheet): bool
    {
        return self::isAdmin($user, $sheet->contract->institute_id) || $sheet->contract->employee->id === $user->id;
    }

    public static function canDeleteRecord(User $user, Sheet $sheet): bool
    {
        return self::isAdmin($user, $sheet->contract->institute_id) || $sheet->contract->employee->id === $user->id;
    }

    public static function canShowRecord(User $user, Sheet $sheet): bool
    {
        return self::isAdmin($user, $sheet->contract->institute_id)|| self::isSupervisor($user, $sheet->contract->institute_id, $sheet->contract_id) || $sheet->contract->employee->id === $user->id;
    }

    public static function canUpdateRecord(User $user, Sheet $sheet): bool
    {
        return self::isAdmin($user, $sheet->contract->institute_id) || $sheet->contract->employee->id === $user->id;
    }

    public static function canCreateSheet(User $user, Contract $contract): bool
    {
        return self::isAdmin($user, $contract->institute_id) || $contract->employee->id === $user->id;
    }

    public static function canDeleteSheet(User $user, Contract $contract): bool
    {
        return self::isAdmin($user, $contract->institute_id) || $contract->employee->id === $user->id;
    }

    public static function canShowSheet(User $user, Sheet $sheet): bool
    {
        return self::isAdmin($user, $sheet->contract->institute_id) || self::isSupervisor($user, $sheet->contract->institute_id, $sheet->contract_id) || $sheet->contract->employee_id === $user->id;
    }

    public static function canUpdateSheet(User $user, Sheet $sheet): bool
    {
        return self::isAdmin($user, $sheet->contract->institute_id)|| self::isSupervisor($user, $sheet->contract->institute_id, $sheet->contract_id) || $sheet->contract->employee_id === $user->id;
    }

    public static function canIndexSheet(User $user): bool
    {
        return self::isGlobalAdmin($user);
    }

    public static function canIndexSheetRecord(User $user, Sheet $sheet): bool
    {
        return self::isAdmin($user, $sheet->contract->institute_id) || self::isSupervisor($user, $sheet->contract->institute_id, $sheet->contract_id) || $sheet->contract->employee_id === $user->id;
    }

    public static function canIndexContractSheet(User $user, Contract $contract): bool
    {
        return self::isAdmin($user, $contract->institute_id) || self::isSupervisor($user, $contract->institute_id, $contract->id) || $contract->employee_id === $user->id;
    }

    public static function canCreateWorkflowLog(User $user): bool
    {
        return false;
        // return self::isGlobalAdmin($user);
    }

    public static function canCreateSheetWorkflowLog(User $user, Sheet $sheet): bool
    {
        return false;
        // return self::isAdmin($user, $sheet->contract->institute_id) || $sheet->contract->employee->id === $user->id;
    }

    public static function canDeleteWorkflowLog(User $user): bool
    {
        return false;
        // return self::isGlobalAdmin($user);
    }

    public static function canIndexWorkflowLogs(User $user): bool
    {
        return false;
        // return self::isGlobalAdmin($user);
    }

    public static function canIndexSheetWorkflowLogs(User $user, Sheet $sheet): bool
    {
        return false;
        // return self::isAdmin($user, $sheet->contract->institute_id) || $sheet->contract->employee->id === $user->id;
    }

    public static function canShowWorkflowLog(User $user): bool
    {
        return false;
        // return self::isGlobalAdmin($user);
    }

    public static function canShowSheetWorkflowLog(User $user, Sheet $sheet): bool
    {
        return false;
        // return self::isAdmin($user, $sheet->contract->institute_id) || $sheet->contract->employee->id === $user->id;
    }

    public static function canUpdateWorkflowLog(User $user, Sheet $sheet): bool
    {
        return false;
        // return self::isAdmin($user, $sheet->contract->institute_id) || $sheet->contract->employee->id === $user->id;
    }
}
