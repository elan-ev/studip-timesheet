<?php

/**
 * IndexController
 *
 * Controller für die Indexansicht von StudipCheckin.
 *
 * @package   StudipCheckin
 * @since     0.1.0
 * @author    Ron Lucke <lucke@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

class TimesheetController extends PluginController
{
    public function index_action()
    {
        PageLayout::disableSidebar();

        $this->userId = User::findCurrent()->id;
        $this->preferredLanguage = str_replace('_', '-', $_SESSION['_language']);
        // $this->isSupervisor = $this->plugin->isSupervisorOrAdmin($this->userId);
        // $this->isWorker =  $this->plugin->isWorker($this->userId);

        $this->isSupervisor = false;
        $this->isWorker = true;

        if (Navigation::hasItem('/contents')) {
            Navigation::activateItem('/contents/timesheet');
        }
    }
}