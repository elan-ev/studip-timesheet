<?php

/**
 * StudipTimesheet Plugin
 *
 * @package   StudipTimesheet
 * @since     0.0.1
 * @author    Ron Lucke <lucke@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

require_once __DIR__ . '/bootstrap.php';

use JsonApi\Contracts\JsonApiPlugin;
use StudipTimesheet\JsonApi\Routes;
use StudipTimesheet\JsonApi\Schemas;
use StudipTimesheet\Models\Contract;
use StudipTimesheet\Models\Permission;
use StudipTimesheet\Models\Supervisor;

class StudipTimesheet extends StudIPPlugin implements SystemPlugin, JsonApiPlugin
{
    use Routes;
    use Schemas;

    public function __construct()
    {
        parent::__construct();

        $userId = User::findCurrent()->id ?? null;
        if (!$userId || $userId === 'nobody') {
            return;
        }

        if ($this->isWorker($userId) || $this->isSupervisorOrAdmin($userId) || $GLOBALS['perm']->have_perm("root")) {
            $this->buildContentsNavigation();
            PageLayout::addStylesheet($this->getPluginUrl() . '/dist/timesheet.css');

            PageLayout::addScript($this->getPluginUrl() . '/dist/studip-timesheet.js', [
                'type' => 'module',
                'rel' => 'preload',
            ]);
        }

        if ($GLOBALS['perm']->have_perm("root")) {
            $this->buildAdminNavigation();
            PageLayout::addScript($this->getPluginUrl() . '/dist/studip-timesheet-admin.js', [
                'type' => 'module',
                'rel' => 'preload',
            ]);
        }
    }

    public function perform($unconsumedPath)
    {
        parent::perform($unconsumedPath);
    }

    public function getPluginName(): string
    {
        return _('TimesheetPlugin');
    }

    public function getInfoTemplate($courseId)
    {
        return null;
    }

    private function buildContentsNavigation(): void
    {
        $navigation = new Navigation($this->_('Zeiterfassung'));
        $navigation->setDescription('TODO: Lorem ipsum dolor');
        $navigation->setImage(Icon::create('timetable', 'navigation'));
        $navigation->addSubNavigation('index', new Navigation(
            'Stundenzettel',
            PluginEngine::getURL($this, [], 'timesheet')
        ));

        if (Navigation::hasItem('/contents')) {
            Navigation::addItem('/contents/timesheet', $navigation);
        }
    }

    private function buildAdminNavigation(): void
    {
        $item = new Navigation(
                $this->_('Zeiterfassung'),
                PluginEngine::getLink($this, [], 'timesheetadmin#/admin')
            );
            if (Navigation::hasItem('/admin/config') && !Navigation::hasItem('/admin/config/timesheetadmin')) {
                Navigation::addItem('/admin/config/timesheetadmin', $item);
            }
    }

    public function isWorker(string $userId): bool
    {
        static $cache = [];
        return $cache[$userId] ??= Contract::countBySql('employee_id = ?', [$userId]) > 0;
    }

    public function isSupervisorOrAdmin(string $userId): bool
    {
        static $cache = [];

        return $cache[$userId] ??= (
            Permission::countBySql('user_id = ?', [$userId]) > 0
            || Supervisor::countBySql('user_id = ?', [$userId]) > 0
        );
    }
}
