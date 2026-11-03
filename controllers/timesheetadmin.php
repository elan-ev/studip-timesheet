<?php

/**
 * IndexController
 *
 * Controller für die Indexansicht von StudipCheckin.
 *
 * @package   StudipCheckin
 * @since     0.2.0
 * @author    Ron Lucke <lucke@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

use StudipTimesheet\Models\Permission;
use StudipTimesheet\JsonApi\Routes\Authority;

class TimesheetAdminController extends PluginController
{
    public function index_action()
    {
        $user = User::findCurrent();

        if (!Authority::canIndexContract($user) && !Authority::canIndexContractForInstitute($user, '')) {
            throw new \AccessDeniedException(_('Sie haben keine Berechtigung, die Zeiterfassung zu verwalten.'));
        }

        PageLayout::disableSidebar();

        $this->preferredLanguage = str_replace('_', '-', $_SESSION['_language']);
        $this->isGlobalAdmin = Authority::isGlobalAdmin($user);
        $this->isSupervisor = Authority::isSupervisor($user);
        $this->hasPermissions = Permission::hasPermission($user);

        if (Navigation::hasItem('/contents')) {
            Navigation::activateItem('/contents/timesheetadmin');
        }
    }
}