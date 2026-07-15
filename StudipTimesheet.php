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

class StudipTimesheet extends StudIPPlugin implements SystemPlugin, JsonApiPlugin
{
    use Routes;
    use Schemas;

    public function __construct()
    {
        parent::__construct();

        PageLayout::addStylesheet($this->getPluginUrl() . '/dist/timesheet.css');

        PageLayout::addScript($this->getPluginUrl() . '/dist/studip-timesheet.js', [
            'type' => 'module',
            'rel' => 'preload',
        ]);

        if ($GLOBALS['perm']->have_perm("root")) {
            PageLayout::addScript($this->getPluginUrl() . '/dist/studip-checkin-admin.js', [
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
}
