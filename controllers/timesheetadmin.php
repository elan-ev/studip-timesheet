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

class TimesheetAdminController extends PluginController
{
    public function index_action()
    {
        // PageLayout::disableSidebar();

        $this->preferredLanguage = str_replace('_', '-', $_SESSION['_language']);

        if (Navigation::hasItem('/admin/config')) {
            Navigation::activateItem('/admin/config/timesheetadmin');
        }
    }
}